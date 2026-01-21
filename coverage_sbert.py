# coverage_sbert.py (FAIRNESS V6)
from __future__ import annotations

import os
import re
from dataclasses import dataclass, field
from typing import Any, Dict, List, Optional, Tuple

import numpy as np

try:
    from sentence_transformers import SentenceTransformer
except Exception as e:
    raise RuntimeError("sentence-transformers is required") from e

# ----------------------------
# Embedding cache
# ----------------------------
_SBERT_MODEL: Optional[SentenceTransformer] = None
_EMB_CACHE: Dict[str, np.ndarray] = {}

def _get_model() -> SentenceTransformer:
    global _SBERT_MODEL
    if _SBERT_MODEL is None:
        name = os.getenv("COGNISENSE_SBERT_MODEL", "sentence-transformers/all-MiniLM-L6-v2")
        _SBERT_MODEL = SentenceTransformer(name)
    return _SBERT_MODEL

def get_sbert() -> SentenceTransformer:
    return _get_model()

def _embed_texts(texts: List[str]) -> np.ndarray:
    model = _get_model()
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
# Text utils
# ----------------------------
_WORD_RE = re.compile(r"[a-z0-9]+")
_PUNCT = re.compile(r"[^a-z0-9\s]+")

def _norm(s: str) -> str:
    s = (s or "").lower()
    s = _PUNCT.sub(" ", s)
    s = re.sub(r"\s+", " ", s).strip()
    return s

def _norm_text(s: str) -> str:
    return _norm(s)

def _tokens(s: str) -> List[str]:
    return _WORD_RE.findall(_norm(s))

def _contains_phrase(text_norm: str, phrase_norm: str) -> bool:
    if not phrase_norm:
        return False
    if phrase_norm in text_norm:
        pat = r"(?:^|\b)" + re.escape(phrase_norm) + r"(?:\b|$)"
        return re.search(pat, text_norm) is not None
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

# ----------------------------
# Keyword utils
# ----------------------------
_GENERIC_STOP = {
    "the","a","an","and","or","to","of","in","on","for","with","as",
    "i","we","you","they","it","this","that","is","am","are","was","were",
    "my","our","your","their","me","us","there","here","at","by","be","been",
}

def _keyword_hits(text: str, keywords: List[str]) -> Tuple[bool, bool]:
    """
    Returns (hit_any, hit_strict)
    - hit_any: any keyword phrase found
    - hit_strict: found keyword that is not stopword-only
    """
    if not keywords:
        return (False, False)
    t = _norm(text)
    hit_any = False
    hit_strict = False
    for kw in keywords:
        kw_n = _norm(str(kw))
        if not kw_n:
            continue
        if _contains_phrase(t, kw_n):
            hit_any = True
            toks = kw_n.split()
            if any(tok not in _GENERIC_STOP for tok in toks):
                hit_strict = True
    return (hit_any, hit_strict)

def _anti_hit(text: str, anti_keywords: List[str]) -> bool:
    if not anti_keywords:
        return False
    t = _norm(text)
    for kw in anti_keywords:
        kw_n = _norm(str(kw))
        if kw_n and _contains_phrase(t, kw_n):
            return True
    return False

def _content_token_count(text: str) -> int:
    toks = _tokens(text)
    return sum(1 for t in toks if t not in _GENERIC_STOP)

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
# Point parsing + smart defaults
# ----------------------------
DEFAULT_DESC_BY_ID = {
    # interview intro
    "present": "Explain who you are right now: current role, background, what you're focused on.",
    "past": "Mention past experience: projects, coursework, internship, responsibilities and what you did.",
    "strengths": "State 1–2 strengths and back them with a quick example.",
    "future": "Describe future goals: why this role, what you want to learn, how you plan to grow.",

    # motivation scenario patterns
    "why_role_reason_1": "Give a clear reason you want THIS ROLE (impact, responsibilities, type of work).",
    "why_role_reason_2": "Give another reason for THIS ROLE (learning, growth, fundamentals, real challenges).",
    "why_company_reason_1": "Give a reason you want THIS COMPANY (culture, customers, teamwork, mission).",
    "why_company_reason_2": "Give another company reason (learning culture, growth, feedback, development).",
    "fit_example": "Provide a short example proving fit (teamwork, communication, ownership, results).",
    "close_confident": "Close confidently (excited, ready to contribute, aligned with goals).",
}

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

def _parse_points(expected_points_obj: Any) -> List[Point]:
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
    # If desc exists, keep it.
    if p.desc:
        return p.desc.strip()

    # If label is long enough, don’t inject.
    if len((p.label or "").strip()) > 12:
        return ""

    pid = (p.id or "").strip().lower()
    return DEFAULT_DESC_BY_ID.get(pid, "")

def _point_text(p: Point) -> str:
    # Enrich if point is too generic (label-only like "Present").
    desc = p.desc.strip() if p.desc else _smart_desc(p)

    chunks = [p.label]
    if desc:
        chunks.append(desc)
    if p.anchors:
        chunks.append("Anchors: " + "; ".join(p.anchors))
    if p.keywords:
        chunks.append("Keywords: " + "; ".join(p.keywords[:10]))
    return " ".join(chunks).strip()

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

# ----------------------------
# Main scoring (FAIRNESS V6)
# ----------------------------
def score_coverage_sbert(
    transcript_text: str,
    expected_points_obj: Any,
    segments: Optional[List[Dict[str, Any]]] = None,
    unit_mode: str = "auto",

    sim_threshold: float = 0.46,
    kw_min_sim: float = 0.30,

    # backward-compat
    kw_boost_credit: float = 0.72,

    kw_bonus_any: float = 0.03,
    kw_bonus_strict: float = 0.06,
    kw_miss_penalty: float = 0.18,

    kw_boost_credit_strict: Optional[float] = None,
    kw_boost_credit_any: float = 0.55,

    # if True, do not give ANY subthreshold credit unless keyword hits (unless near threshold fallback triggers)
    require_kw_for_subthreshold: bool = True,

    # ✅ NEW: allow partial credit without kw if sim is close enough to threshold
    subthreshold_allow_ratio: float = 0.80,

    # fairness gates
    full_credit_requires_keyword_hit: bool = True,
    apply_content_token_gate: bool = True,
    min_content_tokens_for_full_credit: int = 6,
    content_token_power: float = 2.0,

    # ✅ NEW: apply content-token factor INSIDE assignment scoring
    use_content_gate_in_assignment: bool = True,

    # ✅ NEW: allow disabling distinct assignment (defaults to True)
    distinct_units: bool = True,

    # ✅ NEW: stronger anti penalty + optional auto anti for “reason framing”
    anti_penalty: float = 0.20,
    auto_reason_framing_anti: bool = True,

    # off-topic
    topic_threshold: float = 0.33,
    maxsim_gate: float = 0.40,
    off_topic_min_coverage_gate: float = 0.25,
    off_topic_penalty: float = 0.22,

    top_k_evidence: int = 6,
    kw_top_k: int = 4,
    required_miss_penalty: float = 0.0,

    **kwargs,
) -> Dict[str, Any]:

    transcript_text = transcript_text or ""

    # ---- backward compat mapping ----
    if kw_boost_credit_strict is None:
        kw_boost_credit_strict = float(kw_boost_credit)

    if "kw_boost_credit_strict" in kwargs:
        try: kw_boost_credit_strict = float(kwargs["kw_boost_credit_strict"])
        except Exception: pass
    if "kw_boost_credit_any" in kwargs:
        try: kw_boost_credit_any = float(kwargs["kw_boost_credit_any"])
        except Exception: pass

    points = _parse_points(expected_points_obj)
    units, unit_mode_used = _build_units(transcript_text, segments=segments, unit_mode=unit_mode)

    if not points:
        return {
            "score": 0.0,
            "evidence": ["No expected points configured."],
            "details": {"off_topic": False, "points": [], "unit_count": len(units), "unit_mode_used": unit_mode_used},
            "score_raw_100": 0.0,
        }
    if not units:
        return {
            "score": 0.0,
            "evidence": ["No transcript units to score."],
            "details": {"off_topic": False, "points": [], "unit_count": 0, "unit_mode_used": unit_mode_used},
            "score_raw_100": 0.0,
        }

    # Auto anti for “There are two reasons…” style framing sentences on reason-points
    if auto_reason_framing_anti:
        framing_phrases = ["two main reasons", "two reasons", "for two reasons", "main reasons"]
        for p in points:
            pid = (p.id or "").lower()
            if "reason" in pid:
                existing = set(_norm(x) for x in (p.anti_keywords or []))
                for phr in framing_phrases:
                    if _norm(phr) not in existing:
                        p.anti_keywords.append(phr)

    point_texts = [_point_text(p) for p in points]
    unit_texts = [u.strip() for u in units]

    E_points = _embed_texts(point_texts)
    E_units = _embed_texts(unit_texts)

    sim = (E_points @ E_units.T).astype(np.float32)
    P, U = sim.shape

    # Precompute content tokens per unit (used both in assignment and final gate)
    unit_content_tokens = [int(_content_token_count(ut)) for ut in unit_texts]

    bonus = np.zeros((P, U), dtype=np.float32)
    anti_pen = np.zeros((P, U), dtype=np.float32)
    miss_pen = np.zeros((P, U), dtype=np.float32)
    content_factor_assign = np.ones((P, U), dtype=np.float32)

    kw_hit_any_mat = [[False] * U for _ in range(P)]
    kw_hit_strict_mat = [[False] * U for _ in range(P)]
    anti_hit_mat = [[False] * U for _ in range(P)]

    for i, p in enumerate(points):
        p_sim_th = float(p.grading.get("sim_threshold", sim_threshold) or sim_threshold)

        # per-point overrides
        p_min_ct = int(p.grading.get("min_content_tokens_for_full_credit", min_content_tokens_for_full_credit) or min_content_tokens_for_full_credit)
        p_ct_pow = float(p.grading.get("content_token_power", content_token_power) or content_token_power)

        p_use_ct_assign = bool(p.grading.get("use_content_gate_in_assignment", use_content_gate_in_assignment))
        p_apply_ct_gate = bool(p.grading.get("apply_content_token_gate", apply_content_token_gate))

        for j, ut in enumerate(unit_texts):
            anti_hit = _anti_hit(ut, p.anti_keywords)
            hit_any, hit_strict = _keyword_hits(ut, p.keywords)

            if anti_hit:
                hit_any = False
                hit_strict = False

            kw_hit_any_mat[i][j] = hit_any
            kw_hit_strict_mat[i][j] = hit_strict
            anti_hit_mat[i][j] = anti_hit

            if hit_any:
                bonus[i][j] += float(kw_bonus_any)
            if hit_strict:
                bonus[i][j] += float(kw_bonus_strict)

            if anti_hit:
                anti_pen[i][j] = float(anti_penalty)

            if (float(sim[i, j]) < p_sim_th) and (not hit_any):
                miss_pen[i][j] = float(kw_miss_penalty)

            # ✅ Content gate as assignment preference (prevents short framing units)
            if p_use_ct_assign and p_min_ct > 0:
                ct = unit_content_tokens[j]
                if ct < p_min_ct:
                    ratio = max(0.0, min(1.0, float(ct) / float(p_min_ct)))
                    content_factor_assign[i][j] = float(ratio ** max(0.1, p_ct_pow))

            # If content gate disabled for scoring, still allow assignment preference without forcing credit down
            if not p_apply_ct_gate:
                # keep assignment factor if p_use_ct_assign, otherwise 1.0
                pass

    weights = np.array([max(0.0, p.weight) for p in points], dtype=np.float32)
    wsum = float(weights.sum())
    wnorm = weights / (wsum + 1e-9)

    base = (sim + bonus - anti_pen - miss_pen)
    if use_content_gate_in_assignment:
        base = base * content_factor_assign

    assign_score = base * wnorm[:, None]

    if distinct_units:
        assignment = _hungarian_maximize(assign_score)
    else:
        assignment = [int(np.argmax(assign_score[i])) for i in range(P)]

    # Topic sim (scenario-level)
    E_topic = np.mean(E_points, axis=0, keepdims=True)
    E_tr = _embed_texts([transcript_text.strip()])[0:1]
    topic_sim = float((E_tr @ E_topic.T)[0, 0])
    max_sim_overall = float(np.max(sim)) if sim.size else 0.0

    point_results: List[Dict[str, Any]] = []
    evid: List[str] = []

    total_w = wsum if wsum > 0 else float(len(points))
    score_num = 0.0

    required_total = 0
    required_missing = 0

    for i, p in enumerate(points):
        j = assignment[i]
        assigned = (j is not None) and (0 <= int(j) < U)

        if not assigned:
            sel_sim = 0.0
            unit_text = ""
            kw_any = False
            kw_strict = False
            anti_hit = False
            unit_idx = None
            content_tokens = 0
            content_gate_factor = 0.0
        else:
            j = int(j)
            sel_sim = float(sim[i, j])
            unit_text = unit_texts[j]
            kw_any = kw_hit_any_mat[i][j]
            kw_strict = kw_hit_strict_mat[i][j]
            anti_hit = anti_hit_mat[i][j]
            unit_idx = j
            content_tokens = unit_content_tokens[j]
            content_gate_factor = 1.0

        col = sim[i]
        top1_j = int(np.argmax(col))
        top1_sim = float(col[top1_j])

        k = max(1, int(kw_top_k))
        topk = np.argsort(col)[-k:][::-1].astype(int).tolist()

        p_sim_th = float(p.grading.get("sim_threshold", sim_threshold) or sim_threshold)
        p_kw_min = float(p.grading.get("kw_min_sim", kw_min_sim) or kw_min_sim)

        # per-point content gate override
        p_min_ct = int(p.grading.get("min_content_tokens_for_full_credit", min_content_tokens_for_full_credit) or min_content_tokens_for_full_credit)
        p_ct_pow = float(p.grading.get("content_token_power", content_token_power) or content_token_power)

        # per-point policy overrides
        p_req_kw = bool(p.grading.get("require_kw_for_subthreshold", require_kw_for_subthreshold))
        p_full_req = bool(p.grading.get("full_credit_requires_keyword_hit", full_credit_requires_keyword_hit))
        p_apply_ct_gate = bool(p.grading.get("apply_content_token_gate", apply_content_token_gate))

        credit = 0.0
        if assigned:
            if sel_sim >= p_sim_th:
                credit = 1.0
            else:
                if kw_strict and sel_sim >= p_kw_min:
                    credit = float(kw_boost_credit_strict)
                elif kw_any and sel_sim >= p_kw_min:
                    credit = float(kw_boost_credit_any)
                else:
                    # ✅ near-threshold semantic fallback to prevent false negatives
                    if p_req_kw and p.keywords:
                        if sel_sim >= float(subthreshold_allow_ratio) * p_sim_th:
                            credit = max(0.0, min(1.0, sel_sim / max(p_sim_th, 1e-6)))
                        else:
                            credit = 0.0
                    else:
                        credit = max(0.0, min(1.0, sel_sim / max(p_sim_th, 1e-6)))

            # ✅ FULL CREDIT requires keyword hit when keywords exist (per point)
            if p_full_req and credit >= 0.999 and p.keywords and (not kw_any):
                credit = min(float(credit), float(kw_boost_credit_strict))

            # ✅ Content-token gate (per point)
            if p_apply_ct_gate and credit > 0 and p_min_ct > 0:
                if content_tokens < p_min_ct:
                    ratio = max(0.0, min(1.0, float(content_tokens) / float(p_min_ct)))
                    content_gate_factor = float(ratio ** max(0.1, p_ct_pow))
                    credit *= content_gate_factor

        # required bookkeeping
        if p.required:
            required_total += 1
            if credit < 0.70:
                required_missing += 1

        score_num += (p.weight * credit)

        # Evidence messaging
        if assigned:
            snippet = unit_text.strip()
            if len(snippet) > 130:
                snippet = snippet[:127] + "..."
            if credit >= 0.95:
                evid.append(f"{p.label}: \"{snippet}\" (sim={sel_sim:.2f})")
            elif credit >= 0.70:
                evid.append(f"Partial '{p.label}' (sim={sel_sim:.2f}). Closest: \"{snippet}\"")
            else:
                evid.append(f"Missing '{p.label}' (sim={sel_sim:.2f}). Closest: \"{snippet}\"")
        else:
            evid.append(f"Missing '{p.label}' (sim=0.00). Closest: \"\"")

        point_results.append({
            "id": p.id,
            "label": p.label,
            "weight": p.weight,
            "sim": round(sel_sim, 3),
            "credit": round(float(credit), 3),
            "kw_hit": bool(kw_any),
            "kw_hit_strict": bool(kw_strict),
            "anti_hit": bool(anti_hit),
            "best_unit_index": unit_idx,
            "best_unit": unit_text,
            "topk_unit_indexes": topk,
            "top1_unit_index": top1_j,
            "top1_sim": round(top1_sim, 3),
            "content_tokens": int(content_tokens),
            "content_gate_factor": round(float(content_gate_factor), 3),
            "distinct_debug": {
                "used_distinct": bool(distinct_units),
                "selected_index": unit_idx,
                "selected_sim": sel_sim,
                "top1_index": top1_j,
                "top1_sim": top1_sim,
                "reason": "hungarian_one_to_one" if distinct_units else "independent_argmax",
            },
        })

    score_ratio_before_penalty = float(score_num / max(total_w, 1e-9))

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
        "sim_threshold": float(sim_threshold),
        "kw_min_sim": float(kw_min_sim),
        "subthreshold_allow_ratio": float(subthreshold_allow_ratio),
        "unit_mode_used": unit_mode_used,
        "unit_count": int(len(units)),
        "distinct_units": bool(distinct_units),
        "use_content_gate_in_assignment": bool(use_content_gate_in_assignment),
        "anti_penalty": float(anti_penalty),
        "auto_reason_framing_anti": bool(auto_reason_framing_anti),
        "evidence_suppressed": False,
        "fairness_gates": {
            "full_credit_requires_keyword_hit": bool(full_credit_requires_keyword_hit),
            "apply_content_token_gate": bool(apply_content_token_gate),
            "min_content_tokens_for_full_credit": int(min_content_tokens_for_full_credit),
            "content_token_power": float(content_token_power),
            "required_total": int(required_total),
            "required_missing": int(required_missing),
            "required_miss_penalty": float(required_miss_penalty),
        },
    }

    return {
        "score": round(score_raw_100, 2),
        "evidence": evidence_out,
        "details": details,
        "score_raw_100": round(score_raw_100, 2),
    }

def coverage_from_expected_points(transcript_text: str, expected_points_obj: Any, **kwargs) -> Dict[str, Any]:
    return score_coverage_sbert(transcript_text, expected_points_obj, **kwargs)
