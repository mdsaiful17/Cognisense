# order_rules.py (FAIRNESS V5.1 - fixes + more robust order detection)

import re
from typing import Any, Dict, List, Optional

from coverage_sbert import score_coverage_sbert, _norm_text, _normalize_points

INTERVIEW_ORDER = ["present", "past", "strengths", "future"]
STAR_ORDER = ["situation", "task", "action", "result"]

SECTION_CUES = {
    "present": [
        r"\b(i am|i'm|im)\b",
        r"\b(currently|right now|at the moment|these days)\b",
        r"\b(recent|recently)\b",
        r"\b(student|graduate|developer|engineer)\b",
    ],
    "past": [
        r"\b(in the past|previously|earlier|before)\b",
        r"\b(during my studies|during college|during university)\b",
        r"\b(i worked on|i built|i developed|i contributed)\b",
        r"\b(project|internship|coursework|assignment)\b",
    ],
    "strengths": [
        r"\b(two strengths|my strengths|strengths i bring|one strength)\b",
        r"\b(problem solving|teamwork|communication|ownership)\b",
        r"\b(for example|for instance|such as)\b",
    ],
    "future": [
        r"\b(looking ahead|moving forward|in the future)\b",
        r"\b(this role|this position)\b",
        r"\b(i am excited|i'm excited|im excited|excited about)\b",
        r"\b(eager to learn|want to learn)\b",
    ],
    "situation": [r"\b(situation|context|at the time|when)\b"],
    "task":      [r"\b(task|goal|responsible for|needed to)\b"],
    "action":    [r"\b(action|i did|i took|i decided|i implemented)\b"],
    "result":    [r"\b(result|outcome|impact|we achieved|it led to)\b"],
}

def _first_pos(text_norm: str, patterns: List[str]) -> Optional[int]:
    for pat in patterns:
        m = re.search(pat, text_norm)
        if m:
            return m.start()
    return None

def _order_from_cues(text_norm: str, order_ids: List[str]) -> Optional[float]:
    pos = []
    for sid in order_ids:
        p = _first_pos(text_norm, SECTION_CUES.get(sid, []))
        if p is None:
            return None
        pos.append(p)
    if all(pos[i] <= pos[i + 1] for i in range(len(pos) - 1)):
        return 1.0
    good_pairs = sum(1 for i in range(len(pos) - 1) if pos[i] <= pos[i + 1])
    return good_pairs / max(1, (len(pos) - 1))

def _is_star(ids: List[str]) -> bool:
    return all(x in ids for x in STAR_ORDER)

def _is_interview_intro(ids: List[str]) -> bool:
    return all(x in ids for x in INTERVIEW_ORDER)

def _stable_index(dp: dict) -> Optional[int]:
    if not isinstance(dp, dict):
        return None
    for k in ("best_unit_index", "top1_unit_index"):
        if k in dp:
            try:
                v = dp.get(k, None)
                return None if v is None else int(v)
            except Exception:
                return None
    return None

def _credit_ok(
    dp: dict,
    min_credit: float,
    require_kw_hit_for_order: bool,
    require_kw_strict_for_order: bool,
) -> bool:
    """
    For order positioning we want to avoid using generic semantic matches.
    So optionally require kw_hit / kw_hit_strict in addition to credit.
    """
    try:
        c = float(dp.get("credit", 0.0) or 0.0)
    except Exception:
        return False

    if c < float(min_credit):
        return False

    if require_kw_strict_for_order:
        return bool(dp.get("kw_hit_strict", False))

    if require_kw_hit_for_order:
        return bool(dp.get("kw_hit", False) or dp.get("kw_hit_strict", False))

    return True

def _best_index_for_id(
    details_points: List[dict],
    pid: str,
    min_credit: float,
    require_kw_hit_for_order: bool,
    require_kw_strict_for_order: bool,
) -> Optional[int]:
    pid = (pid or "").strip().lower()
    for dp in details_points:
        if str(dp.get("id", "")).strip().lower() == pid:
            if not _credit_ok(dp, min_credit, require_kw_hit_for_order, require_kw_strict_for_order):
                return None
            return _stable_index(dp)
    return None

def _group_order_score(
    details_points: List[dict],
    order_groups: List[List[str]],
    min_credit: float,
    require_kw_hit_for_order: bool,
    require_kw_strict_for_order: bool,
    min_groups_for_order: int,
) -> float:
    """
    Compute ordering based on earliest unit index per group.
    If too few valid groups are present, we can't judge order => return 1.0.
    """
    group_pos: List[int] = []
    for grp in order_groups:
        if not isinstance(grp, list):
            continue
        indexes = []
        for pid in grp:
            idx = _best_index_for_id(
                details_points,
                str(pid),
                min_credit=min_credit,
                require_kw_hit_for_order=require_kw_hit_for_order,
                require_kw_strict_for_order=require_kw_strict_for_order,
            )
            if idx is not None:
                indexes.append(idx)
        if not indexes:
            continue
        group_pos.append(min(indexes))

    if len(group_pos) < int(min_groups_for_order):
        return 1.0

    good_pairs = sum(1 for i in range(len(group_pos) - 1) if group_pos[i] <= group_pos[i + 1])
    return good_pairs / max(1, (len(group_pos) - 1))

def _presence_from_details(details_points: List[dict]) -> float:
    if not isinstance(details_points, list) or not details_points:
        return 0.0
    tw = 0.0
    got = 0.0
    for dp in details_points:
        try:
            w = float(dp.get("weight", 0.0) or 0.0)
        except Exception:
            w = 0.0
        try:
            c = float(dp.get("credit", 0.0) or 0.0)
        except Exception:
            c = 0.0
        tw += w
        got += w * c
    return float(got / tw) if tw > 0 else 0.0

def score_order_and_clarity_rules(
    transcript_text: str,
    points_or_obj: Any,
    segments=None,
    unit_mode: str = "auto",

    # coverage knobs
    sim_threshold: float = 0.46,
    kw_min_sim: float = 0.30,
    kw_boost_credit: float = 0.72,

    # forwarded knobs (keep consistent with coverage)
    require_kw_for_subthreshold: bool = True,
    subthreshold_allow_ratio: float = 0.80,
    full_credit_requires_keyword_hit: bool = True,
    apply_content_token_gate: bool = True,
    min_content_tokens_for_full_credit: int = 6,
    content_token_power: float = 2.0,
    use_content_gate_in_assignment: bool = True,
    distinct_units: bool = True,
    anti_penalty: float = 0.20,
    auto_reason_framing_anti: bool = True,

    # off-topic knobs
    topic_threshold: float = 0.44,
    maxsim_gate: float = 0.36,
    off_topic_penalty: float = 0.22,

    enable_star_only_order: bool = False,
    enable_order: Optional[bool] = None,
    order_groups: Optional[List[List[str]]] = None,
    within_group_any_order: bool = True,  # kept for compatibility (not used here)
    kw_top_k: int = 4,

    # ordering fairness knobs
    min_credit_for_order: float = 0.70,
    presence_floor_for_order_weight: float = 0.35,
    order_weight: float = 0.15,

    # ✅ NEW: make order less sensitive to generic matches
    require_kw_hit_for_order: bool = True,
    require_kw_strict_for_order: bool = False,
    min_groups_for_order: int = 2,
) -> Dict[str, Any]:

    points = _normalize_points(points_or_obj)
    if not points:
        return {"score": 0.0, "evidence": ["No expected points configured."], "details": {}}

    if order_groups is None and isinstance(points_or_obj, dict):
        og = points_or_obj.get("order_groups")
        if isinstance(og, list):
            order_groups = og

    cov = score_coverage_sbert(
        transcript_text,
        points,
        segments=segments,
        unit_mode=unit_mode,

        sim_threshold=sim_threshold,
        kw_min_sim=kw_min_sim,
        kw_boost_credit=kw_boost_credit,

        require_kw_for_subthreshold=require_kw_for_subthreshold,
        subthreshold_allow_ratio=subthreshold_allow_ratio,
        full_credit_requires_keyword_hit=full_credit_requires_keyword_hit,
        apply_content_token_gate=apply_content_token_gate,
        min_content_tokens_for_full_credit=min_content_tokens_for_full_credit,
        content_token_power=content_token_power,
        use_content_gate_in_assignment=use_content_gate_in_assignment,
        distinct_units=distinct_units,
        anti_penalty=anti_penalty,
        auto_reason_framing_anti=auto_reason_framing_anti,

        topic_threshold=topic_threshold,
        maxsim_gate=maxsim_gate,
        off_topic_penalty=off_topic_penalty,
        kw_top_k=kw_top_k,
    )

    details_points = (cov.get("details") or {}).get("points") or []
    presence = float((cov.get("details") or {}).get("score_ratio", 0.0) or 0.0)

    if presence <= 0.0:
        presence = _presence_from_details(details_points)

    if presence <= 0.0:
        # ✅ BUGFIX: cov["score"] is 0–100, presence should be 0–1
        try:
            presence = float(cov.get("score", 0.0) or 0.0) / 100.0
        except Exception:
            presence = 0.0

    ids = [str(p.get("id", "")).strip().lower() for p in points if isinstance(p, dict)]
    is_interview = _is_interview_intro(ids)
    is_star = _is_star(ids)

    text_norm = _norm_text(transcript_text)

    # ✅ More robust default: if it's a known pattern (interview or STAR), we can enable cue-based order
    if enable_order is None:
        if order_groups:
            enable_order = True
        elif is_interview:
            enable_order = True
        elif is_star:
            enable_order = (True if not enable_star_only_order else True)
        else:
            enable_order = False

    order_score = 1.0
    order_mode = "disabled"

    if enable_order:
        if order_groups:
            cleaned: List[List[str]] = []
            for g in order_groups:
                if isinstance(g, list) and g:
                    cleaned.append([str(x).strip() for x in g if str(x).strip()])

            order_score = _group_order_score(
                details_points,
                cleaned,
                min_credit=float(min_credit_for_order),
                require_kw_hit_for_order=bool(require_kw_hit_for_order),
                require_kw_strict_for_order=bool(require_kw_strict_for_order),
                min_groups_for_order=int(min_groups_for_order),
            ) if cleaned else 1.0
            order_mode = "group_order"

        elif is_interview:
            c = _order_from_cues(text_norm, INTERVIEW_ORDER)
            order_score = c if c is not None else 1.0
            order_mode = "cue_words_interview"

        elif is_star:
            c = _order_from_cues(text_norm, STAR_ORDER)
            order_score = c if c is not None else 1.0
            order_mode = "cue_words_star"

        else:
            order_score = 1.0
            order_mode = "skipped_generic"

    ow = float(order_weight) if (enable_order and presence >= float(presence_floor_for_order_weight)) else 0.0
    pw = 1.0 - ow
    final = pw * float(presence) + ow * float(order_score)

    score = round(100.0 * final, 2)

    evidence: List[str] = []
    if enable_order and ow > 0 and float(order_score) < 0.85:
        evidence.append("Sections appear out of order. Follow the recommended structure.")
    elif enable_order and ow > 0 and float(order_score) < 0.999:
        evidence.append("Structure could be clearer. Try following the recommended section order.")

    return {
        "score": score,
        "evidence": evidence[:4],
        "details": {
            "matches": details_points,
            "presence": round(float(presence), 3),
            "order": round(float(order_score), 3),
            "order_mode": order_mode,
            "weights_used": {"presence_weight": round(pw, 3), "order_weight": round(ow, 3)},
            "min_credit_for_order": float(min_credit_for_order),
            "presence_floor_for_order_weight": float(presence_floor_for_order_weight),
            "require_kw_hit_for_order": bool(require_kw_hit_for_order),
            "require_kw_strict_for_order": bool(require_kw_strict_for_order),
            "min_groups_for_order": int(min_groups_for_order),
            "unit_mode_used": (cov.get("details") or {}).get("unit_mode_used"),
            "unit_count": (cov.get("details") or {}).get("unit_count"),
            "off_topic": bool((cov.get("details") or {}).get("off_topic", False)),
            "topic_sim": (cov.get("details") or {}).get("topic_sim"),
        },
    }
