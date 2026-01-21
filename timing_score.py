# timing_score.py
def score_timing(elapsed_sec: float, recommended_duration_json: dict, hard_cap_sec: float = 180.0):
    """
    MVP timing:
    - If <= max_sec (or hard_cap_sec), score = 100
    - If > max, linearly decay to 0 at 2*max
    """
    t = float(elapsed_sec or 0.0)

    mx = None
    if isinstance(recommended_duration_json, dict):
        mx = recommended_duration_json.get("max_sec", recommended_duration_json.get("max", None))
    mx = float(mx) if mx not in (None, "", 0) else float(hard_cap_sec)

    if t <= mx:
        return {"score": 100.0, "evidence": []}

    # decay to 0 at 2*mx
    score = max(0.0, 100.0 * (2.0 * mx - t) / mx)
    return {"score": round(score, 2), "evidence": [f"Too long ({t:.0f}s). Aim for ≤ {mx:.0f}s."]}
