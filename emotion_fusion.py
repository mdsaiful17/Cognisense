# emotion_fusion.py
from __future__ import annotations
from typing import Dict, Any

CANON = ["happy", "neutral", "sad", "angry"]

ALIASES = {
    # speechbrain short labels
    "neu": "neutral",
    "hap": "happy",
    "ang": "angry",
    # common variants
    "anger": "angry",
    "happiness": "happy",
    "sadness": "sad",
}


def _canonize(probs: Dict[str, float]) -> Dict[str, float]:
    out = {k: 0.0 for k in CANON}
    for k, v in (probs or {}).items():
        kk = str(k).lower().strip()
        if kk in ALIASES:
            kk = ALIASES[kk]
        if kk in out:
            out[kk] += float(v)
    z = sum(out.values()) + 1e-9
    return {k: out[k] / z for k in CANON}


def _top(p: Dict[str, float]) -> str:
    return max(p.items(), key=lambda kv: kv[1])[0] if p else "neutral"


def _audio_is_saturated(audio: Dict[str, Any]) -> bool:
    """
    Detect SpeechBrain saturation case:
    - raw confidence ~ 1.0
    - chunk_debug shows repeated max_prob ~ 1.0
    """
    a_details = (audio or {}).get("details", {}) or {}
    calib = (a_details.get("calibration") or {})
    conf_raw = float(calib.get("confidence_raw", 0.0) or 0.0)

    chunk_debug = a_details.get("chunk_debug") or []
    if not isinstance(chunk_debug, list) or len(chunk_debug) == 0:
        # still consider saturation if raw confidence is extreme
        return conf_raw >= 0.9999

    sat = 0
    for c in chunk_debug:
        try:
            mp = float((c or {}).get("max_prob", 0.0) or 0.0)
        except Exception:
            mp = 0.0
        if mp >= 0.9999:
            sat += 1

    # if most inspected chunks are saturated, treat as saturated
    return (conf_raw >= 0.9999) and (sat >= max(3, int(0.75 * len(chunk_debug))))


def fuse_emotions(audio: Dict[str, Any], video: Dict[str, Any]) -> Dict[str, Any]:
    """
    Conservative, disagreement-aware fusion.

    Key behavior:
    - If face evidence is strong and video is neutral-ish BUT audio is saturated "angry",
      ignore audio entirely (prevents false angry spikes).
    """
    a_probs = _canonize((audio or {}).get("probs", {}))
    v_probs = _canonize((video or {}).get("probs", {}))

    a_conf = float((audio or {}).get("confidence", 0.0) or 0.0)
    v_conf = float((video or {}).get("confidence", 0.0) or 0.0)
    face_rate = float((video or {}).get("face_detect_rate", 0.0) or 0.0)

    a_top = _top(a_probs)
    v_top = _top(v_probs)

    # ---- base weights ----
    w_audio, w_video = 0.6, 0.4

    # If face detect is weak → audio dominates
    if face_rate < 0.2:
        w_audio, w_video = 1.0, 0.0
    else:
        # confidence gates
        if a_conf < 0.35:
            w_audio, w_video = 0.4, 0.6
        if v_conf < 0.35:
            w_audio, w_video = 0.75, 0.25

        # If face evidence is strong, let video matter more
        if face_rate >= 0.6:
            w_audio, w_video = 0.5, 0.5

        # ---- HARD SAFETY OVERRIDE (your current case) ----
        audio_saturated = _audio_is_saturated(audio)
        if (
            audio_saturated
            and face_rate >= 0.8
            and v_top in ("neutral", "happy")
            and v_probs["angry"] <= 0.38

            and (v_probs["neutral"] >= v_probs["angry"] + 0.02)
        ):
            # Ignore audio to prevent "angry" hallucination dominating fused output
            w_audio, w_video = 0.0, 1.0

        # ---- softer disagreement handling (kept) ----
        if face_rate >= 0.6 and v_conf >= 0.6:
            if a_top == "angry" and v_top in ("neutral", "happy"):
                if a_probs["angry"] >= 0.6 and v_probs["angry"] <= 0.35:
                    w_audio, w_video = min(w_audio, 0.35), max(w_video, 0.65)

            if v_top == "angry" and a_top in ("neutral", "happy"):
                if v_probs["angry"] >= 0.45 and a_probs["angry"] <= 0.35:
                    w_audio, w_video = min(w_audio, 0.45), max(w_video, 0.55)

    # normalize weights
    wz = (w_audio + w_video) + 1e-9
    w_audio, w_video = w_audio / wz, w_video / wz

    fused = {k: w_audio * a_probs[k] + w_video * v_probs[k] for k in CANON}
    z = sum(fused.values()) + 1e-9
    fused = {k: fused[k] / z for k in CANON}

    top = _top(fused)

    return {
        "top": top,
        "weights": {"audio": round(w_audio, 3), "video": round(w_video, 3)},
        "probs": fused,
        "inputs": {
            "audio_conf": round(a_conf, 3),
            "video_conf": round(v_conf, 3),
            "face_rate": round(face_rate, 3),
            "audio_top": a_top,
            "video_top": v_top,
            "audio_saturated": bool(_audio_is_saturated(audio)),
        },
        "notes": (
    ["Audio emotion saturated; ignoring audio and using video only."]
    if bool(_audio_is_saturated(audio)) and round(w_audio, 3) == 0.0
    else []
),

    }


def score_emotion_professionalism(fused_probs: Dict[str, float]) -> Dict[str, Any]:
    """
    Generic 'professional affect' score (0..100):
    - penalize angry/sad heavily
    - neutral/happy is good for most workplace contexts
    """
    p = _canonize(fused_probs or {})
    penalty = 1.0 * p["angry"] + 0.6 * p["sad"]
    score = 100.0 * (1.0 - min(1.0, penalty))

    top = _top(p)
    evidence = []
    if p["angry"] > 0.35:
        evidence.append("Detected elevated anger/irritation; for professional scenarios, keep tone calm.")
    if p["sad"] > 0.35:
        evidence.append("Detected low energy/negative affect; aim for steady, confident delivery.")

    return {"score": round(score, 2), "top": top, "evidence": evidence[:4], "probs": p}
