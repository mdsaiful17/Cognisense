# coverage_entailment.py
from __future__ import annotations

import os
import re
from dataclasses import dataclass, field
from typing import Any, Dict, List, Optional, Tuple

import numpy as np

try:
    from sentence_transformers import SentenceTransformer
    from sentence_transformers.cross_encoder import CrossEncoder
except Exception as e:
    raise RuntimeError("sentence-transformers is required") from e


# ----------------------------
# SBERT embedding cache
# ----------------------------
_SBERT_MODEL: Optional[SentenceTransformer] = None
_EMB_CACHE: Dict[str, np.ndarray] = {}


def get_sbert() -> SentenceTransformer:
    global _SBERT_MODEL
    if _SBERT_MODEL is None:
        name = os.getenv("COGNISENSE_SBERT_MODEL", "sentence-transformers/all-MiniLM-L6-v2")
        _SBERT_MODEL = SentenceTransformer(name)
    return _SBERT_MODEL


def _embed_texts(texts: List[str]) -> np.ndarray:
    model = get_sbert()
    out: List[Optional[np.ndarray]] = [None] * len(texts)

    missing_idx = []
    missing_texts = []
    for i, t in enumerate(texts):
        key = (t or "").strip()
        if key in _EMB_CACHE:
            out[i] = _EMB_CACHE[key]
        else:
            missing_idx.append(i)
            missing_texts.append(key)

    if missing_texts:
        embs = model.encode(missing_texts, normalize_embeddings=True)
        for j, key in enumerate(missing_texts):
            _EMB_CACHE[key] = embs[j]
        for k, i in enumerate(missing_idx):
            out[i] = embs[k]

    for i, x in enumerate(out):
        if x is None:
            out[i] = model.encode([""], normalize_embeddings=True)[0]

    return np.vstack(out)


# ----------------------------
# NLI CrossEncoder cache
# ----------------------------
_NLI_MODEL: Optional[CrossEncoder] = None


def _get_nli() -> CrossEncoder:
    global _NLI_MODEL
    if _NLI_MODEL is None:
        name = os.getenv("COGNISENSE_NLI_MODEL", "cross-encoder/nli-deberta-v3-small")
        _NLI_MODEL = CrossEncoder(name)
    return _NLI_MODEL


def _softmax(x: np.ndarray, axis: int = -1) -> np.ndarray:
    x = x - np.max(x, axis=axis, keepdims=True)
    e = np.exp(x)
    return e / (np.sum(e, axis=axis, keepdims=True) + 1e-12)


def _entailment_index(ce: CrossEncoder) -> int:
    idx_override = os.getenv("COGNISENSE_NLI_ENTAIL_INDEX", "").strip()
    if idx_override:
        try:
            return int(idx_override)
        except Exception:
            pass

    try:
        cfg = getattr(getattr(ce, "model", None), "config", None)
        id2label = getattr(cfg, "id2label", None)
        if isinstance(id2label, dict) and id2label:
            for k, v in id2label.items():
                if isinstance(v, str) and "entail" in v.lower():
                    return int(k)
    except Exception:
        pass

    return -1


def _nli_entail_probs(pairs: List[Tuple[str, str]]) -> Tuple[np.ndarray, Dict[str, Any]]:
    ce = _get_nli()
    raw = ce.predict(pairs)
    arr = np.array(raw)

    debug: Dict[str, Any] = {
        "nli_model": os.getenv("COGNISENSE_NLI_MODEL", "cross-encoder/nli-deberta-v3-small"),
        "raw_shape": list(arr.shape),
        "entail_index_config": None,
        "entail_index_used": None,
        "id2label": None,
    }

    try:
        cfg = getattr(getattr(ce, "model", None), "config", None)
        id2label = getattr(cfg, "id2label", None)
        if isinstance(id2label, dict):
            debug["id2label"] = {str(k): str(v) for k, v in id2label.items()}
        elif isinstance(id2label, list):
            debug["id2label"] = list(id2label)
    except Exception:
        pass

    if arr.ndim == 2 and arr.shape[1] >= 2:
        probs = _softmax(arr, axis=1)

        ent_idx = _entailment_index(ce)
        debug["entail_index_config"] = ent_idx if ent_idx >= 0 else None

        if ent_idx < 0 or ent_idx >= probs.shape[1]:
            cand1 = 1 if probs.shape[1] > 1 else 0
            cand2 = probs.shape[1] - 1
            mean1 = float(np.mean(probs[:, cand1]))
            mean2 = float(np.mean(probs[:, cand2]))
            ent_idx = cand1 if mean1 >= mean2 else cand2

        debug["entail_index_used"] = int(ent_idx)
        return probs[:, ent_idx].astype(np.float32), debug

    arr = arr.reshape(-1).astype(np.float32)
    probs = (1.0 / (1.0 + np.exp(-arr))).astype(np.float32)
    debug["entail_index_used"] = 0
    return probs, debug


# ----------------------------
# Text utils
# ----------------------------
_WORD_RE = re.compile(r"[a-z0-9]+")
_PUNCT = re.compile(r"[^a-z0-9\s]+")


def _norm(s: str) -> str:
    s = (s or "").lower()
    s = _PUNCT.sub(" ", s)
    s = re.sub(r"\s+", " ", s).strip()
    return s


def _dedupe_keep_order(items: List[str]) -> List[str]:
    """Deduplicate strings (case/space/punct-insensitive) while preserving order."""
    seen = set()
    out: List[str] = []
    for x in items:
        k = _norm(str(x))
        if not k or k in seen:
            continue
        seen.add(k)
        out.append(str(x))
    return out


_GENERIC_STOP = {
    "the","a","an","and","or","to","of","in","on","for","with","as",
    "i","we","you","they","it","this","that","is","am","are","was","were",
    "my","our","your","their","me","us","there","here","at","by","be","been",
}


def _tokens(s: str) -> List[str]:
    return _WORD_RE.findall(_norm(s))


def _content_token_count(text: str) -> int:
    toks = _tokens(text)
    return sum(1 for t in toks if t not in _GENERIC_STOP)


def _contains_substring(text_norm: str, phrase_norm: str) -> bool:
    # ✅ Robust: substring match on normalized text
    if not phrase_norm:
        return False
    return phrase_norm in text_norm


def _keyword_hits(text: str, keywords: List[str]) -> Tuple[bool, bool]:
    if not keywords:
        return (False, False)
    t = _norm(text)
    hit_any = False
    hit_strict = False
    for kw in keywords:
        kw_n = _norm(str(kw))
        if not kw_n:
            continue
        if _contains_substring(t, kw_n):
            hit_any = True
            toks = kw_n.split()
            if any(tok not in _GENERIC_STOP for tok in toks):
                hit_strict = True
    return (hit_any, hit_strict)


def _anti_hit(text: str, anti_keywords: List[str]) -> bool:
    # ✅ Robust anti filtering
    if not anti_keywords:
        return False
    t = _norm(text)
    for kw in anti_keywords:
        kw_n = _norm(str(kw))
        if kw_n and _contains_substring(t, kw_n):
            return True
    return False


# ----------------------------
# Expected points normalization
# ----------------------------
def _normalize_points(points_or_obj: Any) -> List[Dict[str, Any]]:
    if points_or_obj is None:
        return []
    if isinstance(points_or_obj, list):
        return [p for p in points_or_obj if isinstance(p, dict)]
    if isinstance(points_or_obj, dict):
        pts = points_or_obj.get("points") or points_or_obj.get("expected_points") or []
        if isinstance(pts, list):
            return [p for p in pts if isinstance(p, dict)]
        return []
    return []


DEFAULT_DESC_BY_ID = {
    "present": "Explain who you are right now: current role, background, what you're focused on.",
    "past": "Mention past experience: projects, coursework, internship, responsibilities and what you did.",
    "strengths": "State 1–2 strengths and back them with a quick example.",
    "future": "Describe future goals: why this role, what you want to learn, how you plan to grow.",
}

DEFAULT_HYP_BY_ID = {
    "present": "The speaker states who they are currently, including their current role/background and what they are focused on.",
    "past": "The speaker mentions past experience such as projects, coursework, responsibilities, or what they did previously.",
    "strengths": "The speaker states at least one strength and supports it with an example or evidence.",
    "future": "The speaker describes future goals and what they want to learn or how they plan to grow.",
}

# ✅ Stronger defaults to avoid “Present stolen by closing”
DEFAULT_ANTI_BY_ID = {
    "present": ["this role", "excited", "opportunity", "thank you", "thanks"],
    "past": ["looking ahead", "in the future", "my goal", "i want to", "i will"],
    "future": ["currently", "right now", "during my studies", "in the past", "i worked on"],
    "strengths": ["thank you", "this role aligns"],
}

# ✅ Default keywords (helps assignment choose the right sentence)
DEFAULT_KW_BY_ID = {
    "present": ["currently", "right now", "my main focus", "i am", "student", "working as"],
    "past": ["during my studies", "i worked", "project", "experience", "intern", "previously"],
    "future": ["looking ahead", "in the future", "my goal", "i want", "i will", "eager"],
    "strengths": ["strength", "i am good", "teamwork", "problem solving", "i can"],
}

_IMPERATIVE_START = re.compile(r"^(explain|mention|state|describe|provide|give|close)\b", re.I)


def _to_declarative(desc: str) -> str:
    d = (desc or "").strip()
    if not d:
        return ""
    m = _IMPERATIVE_START.match(d)
    if m:
        v = m.group(1).lower()
        rest = d[len(m.group(0)):].lstrip(" :,-")
        if not rest:
            return f"The speaker {v}s the required point."
        return f"The speaker {v}s {rest}"
    return f"The speaker covers: {d}"


@dataclass
class Point:
    id: str
    label: str
    weight: float
    desc: str = ""
    keywords: List[str] = field(default_factory=list)
    anti_keywords: List[str] = field(default_factory=list)
    anchors: List[str] = field(default_factory=list)
    required: bool = False
    grading: Dict[str, Any] = field(default_factory=dict)


def _parse_points(expected_points_obj: Any, use_defaults: bool = True) -> List[Point]:
    pts: List[Point] = []
    rows = _normalize_points(expected_points_obj)

    for p in rows:
        pid = str(p.get("id") or p.get("label") or "").strip()
        label = str(p.get("label") or pid).strip()
        weight = float(p.get("weight", 1.0) or 0.0)
        desc = str(p.get("desc") or p.get("description") or "").strip()

        kws = p.get("keywords_clean") or p.get("keywords") or []
        anti = p.get("anti_keywords") or []
        anchors = p.get("anchors") or []

        if not isinstance(kws, list): kws = []
        if not isinstance(anti, list): anti = []
        if not isinstance(anchors, list): anchors = []

        kws = [str(x).strip() for x in kws if str(x).strip()]
        anti = [str(x).strip() for x in anti if str(x).strip()]
        anchors = [str(x).strip() for x in anchors if str(x).strip()]

        required = bool(p.get("required", False))
        grading = p.get("grading") if isinstance(p.get("grading"), dict) else {}

        if not pid:
            continue

        pid_l = pid.lower().strip()

        # ✅ CHANGE #1: Always UNION defaults (append) then dedupe.
        # This fixes cases where scenario JSON has wrong anti_keywords and blocks good evidence selection.
        if use_defaults:
            if pid_l in DEFAULT_ANTI_BY_ID:
                anti = anti + list(DEFAULT_ANTI_BY_ID[pid_l])
            if pid_l in DEFAULT_KW_BY_ID:
                kws = kws + list(DEFAULT_KW_BY_ID[pid_l])

        anti = _dedupe_keep_order(anti)
        kws = _dedupe_keep_order(kws)

        pts.append(Point(
            id=pid,
            label=label,
            weight=max(0.0, weight),
            desc=desc,
            keywords=kws,
            anti_keywords=anti,
            anchors=anchors,
            required=required,
            grading=grading,
        ))
    return pts


def _smart_desc(p: Point) -> str:
    if p.desc:
        return p.desc.strip()
    if len((p.label or "").strip()) > 12:
        return ""
    return DEFAULT_DESC_BY_ID.get((p.id or "").lower(), "")


def _hypothesis_text(p: Point) -> str:
    pid = (p.id or "").lower().strip()
    if pid in DEFAULT_HYP_BY_ID:
        return DEFAULT_HYP_BY_ID[pid]

    desc = p.desc.strip() if p.desc else _smart_desc(p)
    decl = _to_declarative(desc) if desc else ""

    chunks = []
    if decl:
        chunks.append(decl)
    else:
        chunks.append(f"The speaker covers the point: {p.label}")

    if p.anchors:
        chunks.append("Examples include: " + "; ".join(p.anchors[:3]))

    return " ".join(chunks).strip()


# ----------------------------
# Unit builder
# ----------------------------
_SENT_SPLIT = re.compile(r"(?<=[.!?])\s+")


def _build_units(
    transcript_text: str,
    segments: Optional[List[Dict[str, Any]]] = None,
    unit_mode: str = "auto",
) -> Tuple[List[str], str]:
    transcript_text = transcript_text or ""

    if unit_mode == "auto":
        unit_mode = "segments" if segments else "sentences"

    if unit_mode == "full":
        u = transcript_text.strip()
        return ([u] if u else []), "full"

    if unit_mode == "segments" and segments:
        units: List[str] = []
        for s in segments:
            t = (s.get("text") or "").strip()
            if t:
                units.append(t)
        return units, "segments"

    parts = [p.strip() for p in _SENT_SPLIT.split(transcript_text.strip()) if p.strip()]
    return parts, "sentences"


# ----------------------------
# Hungarian assignment
# ----------------------------
def _hungarian_min_cost(cost: np.ndarray) -> List[int]:
    n, m = cost.shape
    u = np.zeros(n + 1)
    v = np.zeros(m + 1)
    p = np.zeros(m + 1, dtype=int)
    way = np.zeros(m + 1, dtype=int)

    for i in range(1, n + 1):
        p[0] = i
        j0 = 0
        minv = np.full(m + 1, np.inf)
        used = np.zeros(m + 1, dtype=bool)

        while True:
            used[j0] = True
            i0 = p[j0]
            delta = np.inf
            j1 = 0

            for j in range(1, m + 1):
                if not used[j]:
                    cur = cost[i0 - 1, j - 1] - u[i0] - v[j]
                    if cur < minv[j]:
                        minv[j] = cur
                        way[j] = j0
                    if minv[j] < delta:
                        delta = minv[j]
                        j1 = j

            for j in range(0, m + 1):
                if used[j]:
                    u[p[j]] += delta
                    v[j] -= delta
                else:
                    minv[j] -= delta

            j0 = j1
            if p[j0] == 0:
                break

        while True:
            j1 = way[j0]
            p[j0] = p[j1]
            j0 = j1
            if j0 == 0:
                break

    assignment = [-1] * n
    for j in range(1, m + 1):
        if p[j] != 0:
            assignment[p[j] - 1] = j - 1
    return assignment


def _hungarian_maximize(score: np.ndarray) -> List[int]:
    n, m = score.shape
    if n == 0:
        return []

    transposed = False
    S = score
    if n > m:
        transposed = True
        S = score.T
        n, m = S.shape

    pad_cols = max(0, n - m)
    if pad_cols > 0:
        S = np.hstack([S, np.zeros((n, pad_cols), dtype=S.dtype)])
        m = S.shape[1]

    maxv = float(np.max(S)) if S.size else 0.0
    cost = (maxv - S).astype(np.float64)
    assign = _hungarian_min_cost(cost)

    if transposed:
        inv = [-1] * score.shape[0]
        for r, c in enumerate(assign):
            if 0 <= c < score.shape[0]:
                inv[c] = r
        return inv

    return assign


def _pos_bias(pid: str, pos01: float) -> float:
    pid = (pid or "").lower().strip()
    if pid == "present":
        return 1.0 - pos01  # early
    if pid == "future":
        return pos01         # late
    if pid == "past":
        return max(0.0, 1.0 - abs(pos01 - 0.35) * 2.2)
    if pid == "strengths":
        return max(0.0, 1.0 - abs(pos01 - 0.55) * 2.2)
    return 0.0


def score_coverage_entailment(
    transcript_text: str,
    expected_points_obj: Any,
    segments: Optional[List[Dict[str, Any]]] = None,
    unit_mode: str = "auto",

    retrieval_top_k: int = 6,
    min_sim_for_candidate: float = 0.25,
    min_sim_for_entailment: Optional[float] = None,

    sim_tiebreak_weight: float = 0.35,
    position_bias_weight: float = 0.18,
    keyword_bonus_weight: float = 0.10,

    entail_threshold: float = 0.60,
    partial_power: float = 1.00,
    max_pairs_per_batch: int = 64,

    combo_premise_on: bool = True,
    combo_top_n: int = 3,
    combo_pos_bias_weight: float = 0.35,

    sim_fallback_on: bool = True,
    sim_partial_threshold: float = 0.38,
    sim_full_threshold: float = 0.52,
    sim_max_credit: float = 0.95,
    sim_power: float = 1.0,

    distinct_units: bool = True,
    apply_content_token_gate: bool = True,
    min_content_tokens: int = 6,
    content_token_power: float = 2.0,

    topic_threshold: float = 0.15,
    maxsim_gate: float = 0.36,
    off_topic_min_coverage_gate: float = 0.25,
    off_topic_penalty: float = 0.0,

    top_k_evidence: int = 6,
    required_miss_penalty: float = 0.0,

    **kwargs,
) -> Dict[str, Any]:

    transcript_text = transcript_text or ""

    points = _parse_points(expected_points_obj, use_defaults=True)
    units, unit_mode_used = _build_units(transcript_text, segments=segments, unit_mode=unit_mode)

    if not points:
        return {"score": 0.0, "evidence": ["No expected points configured."],
                "details": {"off_topic": False, "points": [], "unit_count": len(units), "unit_mode_used": unit_mode_used},
                "score_raw_100": 0.0}
    if not units:
        return {"score": 0.0, "evidence": ["No transcript units to score."],
                "details": {"off_topic": False, "points": [], "unit_count": 0, "unit_mode_used": unit_mode_used},
                "score_raw_100": 0.0}

    unit_texts = [u.strip() for u in units]
    unit_content_tokens = [int(_content_token_count(ut)) for ut in unit_texts]
    hyps = [_hypothesis_text(p) for p in points]

    E_points = _embed_texts(hyps)
    E_units = _embed_texts(unit_texts)
    sim = (E_points @ E_units.T).astype(np.float32)
    P, U = sim.shape

    E_topic = np.mean(E_points, axis=0, keepdims=True)
    E_tr = _embed_texts([transcript_text.strip()])[0:1]
    topic_sim = float((E_tr @ E_topic.T)[0, 0])
    max_sim_overall = float(np.max(sim)) if sim.size else 0.0

    # Candidates
    k = max(1, int(retrieval_top_k))
    candidates: List[List[int]] = []
    for i in range(P):
        col = sim[i]
        idxs = np.argsort(col)[-k:][::-1].astype(int).tolist()
        idxs_f = [j for j in idxs if float(col[j]) >= float(min_sim_for_candidate)]

        # ✅ CHANGE #2: Filter anti-hit candidates BEFORE assignment.
        idxs_f = [j for j in idxs_f if not _anti_hit(unit_texts[j], points[i].anti_keywords)]

        if not idxs_f:
            # fallback: pick best non-anti from entire ranking
            sorted_all = np.argsort(col)[::-1].astype(int).tolist()
            picked = None
            for jj in sorted_all:
                if not _anti_hit(unit_texts[jj], points[i].anti_keywords):
                    picked = int(jj)
                    break
            if picked is None:
                picked = int(np.argmax(col))
            idxs_f = [picked]

        candidates.append(idxs_f)

    # Entail matrix
    entail = np.zeros((P, U), dtype=np.float32)
    pairs: List[Tuple[str, str]] = []
    map_idx: List[Tuple[int, int]] = []

    for i, p in enumerate(points):
        hyp = hyps[i]
        for j in candidates[i]:
            premise = unit_texts[j]
            if _anti_hit(premise, p.anti_keywords):
                entail[i, j] = 0.0
                continue
            pairs.append((premise, hyp))
            map_idx.append((i, j))

    nli_debug: Dict[str, Any] = {}
    if pairs:
        probs_all: List[np.ndarray] = []
        start = 0
        while start < len(pairs):
            batch = pairs[start: start + int(max_pairs_per_batch)]
            probs, dbg = _nli_entail_probs(batch)
            if not nli_debug and isinstance(dbg, dict):
                nli_debug = dbg
            probs_all.append(probs)
            start += int(max_pairs_per_batch)

        probs_cat = np.concatenate(probs_all, axis=0).astype(np.float32)
        for idx, (i, j) in enumerate(map_idx):
            entail[i, j] = float(probs_cat[idx])

    # Content token gate
    if apply_content_token_gate and int(min_content_tokens) > 0:
        for i in range(P):
            for j in candidates[i]:
                ct = unit_content_tokens[j]
                if ct < int(min_content_tokens):
                    ratio = max(0.0, min(1.0, float(ct) / float(min_content_tokens)))
                    factor = float(ratio ** max(0.1, float(content_token_power)))
                    entail[i, j] *= factor

    # Keyword bonus matrix
    kw_bonus = np.zeros((P, U), dtype=np.float32)
    for i, p in enumerate(points):
        for j in candidates[i]:
            any_hit, strict_hit = _keyword_hits(unit_texts[j], p.keywords)
            if strict_hit:
                kw_bonus[i, j] = float(keyword_bonus_weight)
            elif any_hit:
                kw_bonus[i, j] = float(keyword_bonus_weight) * 0.5

    # Position bias matrix
    bias = np.zeros((P, U), dtype=np.float32)
    denom = float(max(1, U - 1))
    for i, p in enumerate(points):
        for j in candidates[i]:
            pos01 = float(j) / denom
            bias[i, j] = float(_pos_bias(p.id, pos01)) * float(position_bias_weight)

    # Weighted assignment
    weights = np.array([max(0.0, p.weight) for p in points], dtype=np.float32)
    wsum = float(weights.sum()) if float(weights.sum()) > 0 else float(len(points))
    wnorm = weights / (wsum + 1e-9)

    assign_score = (entail + sim * float(sim_tiebreak_weight) + bias + kw_bonus) * wnorm[:, None]

    if distinct_units:
        assignment = _hungarian_maximize(assign_score)
    else:
        assignment = [int(np.argmax(assign_score[i])) for i in range(P)]

    # Combo premise (try merging two best candidates)
    combo_entail: List[float] = [0.0] * P
    combo_units: List[List[int]] = [[] for _ in range(P)]
    combo_texts: List[str] = [""] * P

    if combo_premise_on and U >= 2:
        combo_pairs: List[Tuple[str, str]] = []
        combo_map: List[Tuple[int, List[int], str]] = []

        for i, p in enumerate(points):
            th_i = float(p.grading.get("entail_threshold", entail_threshold) or entail_threshold)

            max_ent_i = max((float(entail[i, j]) for j in candidates[i]), default=0.0)
            if max_ent_i >= th_i:
                continue
            if len(candidates[i]) < 2:
                continue

            scored = []
            for j in candidates[i][: max(2, int(combo_top_n))]:
                pos01 = float(j) / float(max(1, U - 1))
                scored.append((float(sim[i, j]) + float(_pos_bias(p.id, pos01)) * float(combo_pos_bias_weight), int(j)))
            scored.sort(reverse=True)

            if len(scored) < 2:
                continue
            j1, j2 = scored[0][1], scored[1][1]

            premise_combo = (unit_texts[j1] + " " + unit_texts[j2]).strip()
            if not premise_combo:
                continue
            if _anti_hit(premise_combo, p.anti_keywords):
                continue

            combo_pairs.append((premise_combo, hyps[i]))
            combo_map.append((i, [j1, j2], premise_combo))

        if combo_pairs:
            probs, _dbg = _nli_entail_probs(combo_pairs)
            for k_idx, (i, js, prem) in enumerate(combo_map):
                combo_entail[i] = float(probs[k_idx])
                combo_units[i] = list(js)
                combo_texts[i] = prem

    # Final scoring
    point_results: List[Dict[str, Any]] = []
    evid: List[str] = []
    score_num = 0.0
    required_total = 0
    required_missing = 0

    for i, p in enumerate(points):
        j = assignment[i]
        assigned = (j is not None) and (0 <= int(j) < U)

        if not assigned:
            sel_ent = 0.0
            sel_sim = 0.0
            unit_text = ""
            unit_idx = None
            content_tokens = 0
        else:
            j = int(j)
            sel_ent = float(entail[i, j])
            sel_sim = float(sim[i, j])
            unit_text = unit_texts[j]
            unit_idx = j
            content_tokens = unit_content_tokens[j]

        combo_used = False
        sel_ent_used = sel_ent
        best_text_for_evidence = unit_text
        best_idx_for_evidence: Any = unit_idx  # can become list for combo
        sel_sim_used = sel_sim

        if combo_entail[i] > sel_ent_used and combo_units[i]:
            sel_ent_used = float(combo_entail[i])
            combo_used = True
            best_text_for_evidence = combo_texts[i]
            best_idx_for_evidence = combo_units[i]
            # Use max sim of the combined units (more honest than keeping the assigned unit sim)
            try:
                sel_sim_used = float(max(sim[i, jj] for jj in combo_units[i]))
            except Exception:
                sel_sim_used = sel_sim

        # ✅ IMPORTANT: anti/kw should reflect the chosen evidence (single or combo)
        anti_hit_used = _anti_hit(best_text_for_evidence or "", p.anti_keywords)
        kw_any_used, kw_strict_used = _keyword_hits(best_text_for_evidence or "", p.keywords)

        th = float(p.grading.get("entail_threshold", entail_threshold) or entail_threshold)
        powv = float(p.grading.get("partial_power", partial_power) or partial_power)

        ent_credit = 0.0
        if not anti_hit_used:
            if sel_ent_used >= th:
                ent_credit = 1.0
            else:
                base = max(0.0, min(1.0, sel_ent_used / max(th, 1e-6)))
                ent_credit = float(base ** max(0.1, powv))

        sim_credit = 0.0
        if sim_fallback_on and assigned and (not anti_hit_used):
            sp = float(p.grading.get("sim_partial_threshold", sim_partial_threshold) or sim_partial_threshold)
            sf = float(p.grading.get("sim_full_threshold", sim_full_threshold) or sim_full_threshold)
            sm = float(p.grading.get("sim_max_credit", sim_max_credit) or sim_max_credit)
            s_pow = float(p.grading.get("sim_power", sim_power) or sim_power)

            if sel_sim_used >= sf:
                sim_credit = sm
            elif sel_sim_used >= sp and sf > sp:
                ratio = (sel_sim_used - sp) / (sf - sp)
                ratio = max(0.0, min(1.0, ratio))
                sim_credit = float((ratio ** max(0.1, s_pow)) * sm)

        credit = max(ent_credit, sim_credit)

        if p.required:
            required_total += 1
            if credit < 0.70:
                required_missing += 1

        score_num += (p.weight * credit)

        snippet = (best_text_for_evidence or "").strip()
        if len(snippet) > 150:
            snippet = snippet[:147] + "..."

        if credit >= 0.95:
            evid.append(f"{p.label}: \"{snippet}\" (entail={sel_ent_used:.2f}, sim={sel_sim_used:.2f})")
        elif credit >= 0.70:
            evid.append(f"Partial '{p.label}' (entail={sel_ent_used:.2f}, sim={sel_sim_used:.2f}). Closest: \"{snippet}\"")
        else:
            evid.append(f"Missing '{p.label}' (entail={sel_ent_used:.2f}, sim={sel_sim_used:.2f}). Closest: \"{snippet}\"")

        col = sim[i]
        topk = np.argsort(col)[-max(1, min(6, U)):][::-1].astype(int).tolist()
        top1_j = int(np.argmax(col))
        top1_sim = float(col[top1_j])

        point_results.append({
            "id": p.id,
            "label": p.label,
            "weight": p.weight,
            "hypothesis": hyps[i],
            "entail": round(sel_ent_used, 3),
            "sim": round(sel_sim_used, 3),
            "credit": round(float(credit), 3),
            "ent_credit": round(float(ent_credit), 3),
            "sim_credit": round(float(sim_credit), 3),
            "kw_hit": bool(kw_any_used),
            "kw_hit_strict": bool(kw_strict_used),
            "anti_hit": bool(anti_hit_used),
            "anti_keywords": p.anti_keywords,
            "keywords": p.keywords,
            "best_unit_index": best_idx_for_evidence,
            "best_unit": best_text_for_evidence,
            "combo_used": bool(combo_used),
            "combo_unit_indexes": combo_units[i],
            "combo_entail": round(float(combo_entail[i]), 3),
            "topk_unit_indexes": topk,
            "top1_unit_index": top1_j,
            "top1_sim": round(top1_sim, 3),
            "content_tokens": int(content_tokens),
        })

    score_ratio_before_penalty = float(score_num / max(wsum, 1e-9))

    off_topic = (
        (topic_sim < float(topic_threshold))
        and (
            (max_sim_overall < float(maxsim_gate))
            or (score_ratio_before_penalty < float(off_topic_min_coverage_gate))
        )
    )

    score_ratio = score_ratio_before_penalty

    if required_miss_penalty and required_total > 0 and required_missing > 0:
        score_ratio = max(0.0, score_ratio - float(required_miss_penalty) * float(required_missing))

    if off_topic:
        score_ratio = max(0.0, score_ratio - float(off_topic_penalty))

    score_raw_100 = float(score_ratio * 100.0)
    evidence_out = evid[: max(1, int(top_k_evidence))]

    details = {
        "points": point_results,
        "score_ratio": round(float(score_ratio), 4),
        "score_ratio_before_penalty": round(float(score_ratio_before_penalty), 4),
        "off_topic": bool(off_topic),
        "topic_sim": round(float(topic_sim), 3),
        "topic_threshold": float(topic_threshold),
        "max_sim": round(float(max_sim_overall), 3),
        "maxsim_gate": float(maxsim_gate),
        "off_topic_min_coverage_gate": float(off_topic_min_coverage_gate),
        "off_topic_penalty": float(off_topic_penalty),
        "entail_threshold": float(entail_threshold),
        "partial_power": float(partial_power),
        "retrieval_top_k": int(retrieval_top_k),
        "min_sim_for_candidate": float(min_sim_for_candidate),
        "min_sim_for_entailment_param": None if min_sim_for_entailment is None else float(min_sim_for_entailment),
        "unit_mode_used": unit_mode_used,
        "unit_count": int(len(units)),
        "distinct_units": bool(distinct_units),
        "position_bias_weight": float(position_bias_weight),
        "keyword_bonus_weight": float(keyword_bonus_weight),
        "combo_premise": {"on": bool(combo_premise_on), "top_n": int(combo_top_n)},
        "nli_debug": nli_debug,
    }

    return {
        "score": round(score_raw_100, 2),
        "evidence": evidence_out,
        "details": details,
        "score_raw_100": round(score_raw_100, 2),
    }


def coverage_entailment_v1(transcript_text: str, expected_points_obj: Any, **kwargs) -> Dict[str, Any]:
    return score_coverage_entailment(transcript_text, expected_points_obj, **kwargs)
