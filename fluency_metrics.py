# fluency_metrics.py (FAIRNESS V2)
import re
from typing import List, Dict, Any

FILLERS = {
    "um", "uh", "erm", "ah", "like", "you know", "sort of", "kind of",
    "actually", "basically", "literally"
}

_WORD_RE = re.compile(r"[A-Za-z']+")
_TOKEN_RE = re.compile(r"\S+")
_VOWEL_RE = re.compile(r"[aeiou]")
_BAD_CHAR_RE = re.compile(r"[^A-Za-z0-9\s\.,!?'\-]")  # catches non-ascii/noise

def count_words(text: str) -> int:
    return len(_WORD_RE.findall(text or ""))

def count_fillers(text: str) -> int:
    t = (text or "").lower()
    total = 0
    multi = [f for f in FILLERS if " " in f]
    for f in multi:
        total += t.count(f)
    tokens = re.findall(r"[a-z']+", t)
    single = {f for f in FILLERS if " " not in f}
    total += sum(1 for tok in tokens if tok in single)
    return total

def compute_pauses(segments: List[Dict[str, Any]], elapsed_sec: float) -> Dict[str, Any]:
    if not segments:
        return {
            "pause_count": 0,
            "avg_pause_sec": 0.0,
            "max_pause_sec": 0.0,
            "long_pause_count": 0,
            "pause_ratio": 1.0,
            "speech_time_sec": 0.0,
            "silence_time_sec": float(elapsed_sec or 0.0),
            "pauses": [],
        }

    segs = sorted(segments, key=lambda s: float(s.get("start", 0.0)))
    pauses = []

    first_start = float(segs[0].get("start", 0.0))
    if first_start > 0:
        pauses.append(first_start)

    for i in range(len(segs) - 1):
        end_i = float(segs[i].get("end", 0.0))
        start_next = float(segs[i + 1].get("start", 0.0))
        gap = start_next - end_i
        if gap > 0:
            pauses.append(gap)

    last_end = float(segs[-1].get("end", 0.0))
    if elapsed_sec and elapsed_sec > last_end:
        pauses.append(float(elapsed_sec - last_end))

    pause_count = len(pauses)
    avg_pause = sum(pauses) / pause_count if pause_count else 0.0
    max_pause = max(pauses) if pause_count else 0.0

    long_pause_count = sum(1 for p in pauses if p >= 1.2)

    speech_time = 0.0
    for s in segs:
        st = float(s.get("start", 0.0))
        en = float(s.get("end", 0.0))
        if en > st:
            speech_time += (en - st)

    total = float(elapsed_sec or (segs[-1].get("end", 0.0) or 0.0))
    silence_time = max(0.0, total - speech_time)
    pause_ratio = (silence_time / total) if total > 0 else 1.0

    return {
        "pause_count": pause_count,
        "avg_pause_sec": round(avg_pause, 3),
        "max_pause_sec": round(max_pause, 3),
        "long_pause_count": long_pause_count,
        "pause_ratio": round(pause_ratio, 3),
        "speech_time_sec": round(speech_time, 3),
        "silence_time_sec": round(silence_time, 3),
        "pauses": [round(p, 3) for p in pauses[:20]],
    }

def _coerce_int(x, default: int) -> int:
    try: return int(x)
    except Exception: return int(default)

def _coerce_float(x, default: float) -> float:
    try: return float(x)
    except Exception: return float(default)

def _pick_kw(kwargs: dict, keys: List[str], default=None):
    for k in keys:
        if k in kwargs:
            return kwargs.pop(k)
    return default

def _transcript_quality(text: str) -> Dict[str, float]:
    """
    Heuristic quality signals:
    - bad_char_ratio: non-ascii/noise chars
    - weird_token_ratio: long tokens with no vowels or containing non-ascii
    - repetition_ratio: consecutive duplicated tokens
    """
    t = text or ""
    if not t.strip():
        return {"bad_char_ratio": 0.0, "weird_token_ratio": 0.0, "repetition_ratio": 0.0, "quality_score": 0.0}

    bad_chars = len(_BAD_CHAR_RE.findall(t))
    bad_char_ratio = bad_chars / max(1, len(t))

    toks = [x.lower() for x in _TOKEN_RE.findall(t) if x.strip()]
    if not toks:
        return {"bad_char_ratio": round(bad_char_ratio, 4), "weird_token_ratio": 0.0, "repetition_ratio": 0.0, "quality_score": 0.0}

    weird = 0
    for tok in toks:
        if any(ord(ch) > 127 for ch in tok):
            weird += 1
            continue
        if len(tok) >= 10 and (_VOWEL_RE.search(tok) is None):
            weird += 1
            continue
        if re.search(r"(.)\1\1\1", tok):
            weird += 1

    weird_token_ratio = weird / max(1, len(toks))

    dup = 0
    for i in range(1, len(toks)):
        if toks[i] == toks[i - 1]:
            dup += 1
    repetition_ratio = dup / max(1, (len(toks) - 1))

    # Score (tuned to actually punish gibberish)
    quality_score = 100.0
    quality_score -= bad_char_ratio * 1500.0
    quality_score -= weird_token_ratio * 600.0
    quality_score -= repetition_ratio * 500.0
    quality_score = max(0.0, min(100.0, quality_score))

    return {
        "bad_char_ratio": round(bad_char_ratio, 4),
        "weird_token_ratio": round(weird_token_ratio, 4),
        "repetition_ratio": round(repetition_ratio, 4),
        "quality_score": round(quality_score, 2),
    }

def score_fluency(
    transcript_text: str,
    segments: List[Dict[str, Any]],
    elapsed_sec: float,

    target_wpm_min: int = 110,
    target_wpm_max: int = 170,
    max_fillers_per_min_good: float = 3.0,
    max_pause_ratio_good: float = 0.28,
    max_long_pauses_per_min_good: float = 1.2,

    # ✅ accept legacy keys safely
    **kwargs,
) -> Dict[str, Any]:

    wpm_min_alias = _pick_kw(kwargs, ["wpm_ok_min", "wpm_min", "min_wpm"], None)
    wpm_max_alias = _pick_kw(kwargs, ["wpm_ok_max", "wpm_max", "max_wpm"], None)

    fillers_alias = _pick_kw(
        kwargs,
        ["fillers_per_min_ok_max", "fillers_ok_per_min", "filler_ok_per_min", "max_fillers_per_min"],
        None,
    )

    pause_ratio_alias = _pick_kw(kwargs, ["pause_ratio_ok_max", "max_pause_ratio"], None)

    long_pause_alias = _pick_kw(
        kwargs,
        ["long_pauses_ok_max", "long_pauses_ok_per_min", "max_long_pauses_per_min"],
        None,
    )

    if wpm_min_alias is not None:
        target_wpm_min = _coerce_int(wpm_min_alias, target_wpm_min)
    if wpm_max_alias is not None:
        target_wpm_max = _coerce_int(wpm_max_alias, target_wpm_max)
    if fillers_alias is not None:
        max_fillers_per_min_good = _coerce_float(fillers_alias, max_fillers_per_min_good)
    if pause_ratio_alias is not None:
        max_pause_ratio_good = _coerce_float(pause_ratio_alias, max_pause_ratio_good)
    if long_pause_alias is not None:
        max_long_pauses_per_min_good = _coerce_float(long_pause_alias, max_long_pauses_per_min_good)

    elapsed_sec = float(elapsed_sec or 0.0)

    words = count_words(transcript_text)
    minutes = max(1e-9, elapsed_sec / 60.0)
    wpm = words / minutes

    fillers = count_fillers(transcript_text)
    fillers_per_min = fillers / minutes

    pause_stats = compute_pauses(segments, elapsed_sec)
    pr = float(pause_stats["pause_ratio"])
    long_pauses_per_min = float(pause_stats["long_pause_count"]) / minutes

    q = _transcript_quality(transcript_text)
    quality_score = float(q["quality_score"])

    # ---- scoring components (more strict) ----
    # WPM score with stronger curve
    if wpm < target_wpm_min:
        ratio = max(0.0, wpm / max(1e-6, float(target_wpm_min)))
        wpm_score = 100.0 * (ratio ** 2.0)   # stronger penalty when slow
    elif wpm > target_wpm_max:
        ratio = max(0.0, float(target_wpm_max) / max(1e-6, wpm))
        wpm_score = 100.0 * (ratio ** 1.7)
    else:
        wpm_score = 100.0

    if fillers_per_min <= max_fillers_per_min_good:
        filler_score = 100.0
    else:
        filler_score = max(0.0, 100.0 - (fillers_per_min - max_fillers_per_min_good) * 12.0)

    if pr <= max_pause_ratio_good:
        pause_score = 100.0
    else:
        pause_score = max(0.0, 100.0 - (pr - max_pause_ratio_good) * 220.0)

    if long_pauses_per_min <= max_long_pauses_per_min_good:
        long_pause_score = 100.0
    else:
        long_pause_score = max(0.0, 100.0 - (long_pauses_per_min - max_long_pauses_per_min_good) * 45.0)

    # Combine (adds transcript quality)
    score = (
        0.35 * wpm_score
        + 0.20 * filler_score
        + 0.20 * pause_score
        + 0.10 * long_pause_score
        + 0.15 * quality_score
    )

    evidence = []
    if wpm < target_wpm_min:
        evidence.append(f"Speaking rate is slow ({wpm:.0f} WPM).")
    elif wpm > target_wpm_max:
        evidence.append(f"Speaking rate is fast ({wpm:.0f} WPM).")

    if fillers_per_min > max_fillers_per_min_good:
        evidence.append(f"Many fillers ({fillers_per_min:.1f}/min).")

    if pr > max_pause_ratio_good:
        evidence.append(f"High silence ratio ({pr:.2f}).")

    if long_pauses_per_min > max_long_pauses_per_min_good:
        evidence.append(f"Too many long pauses ({long_pauses_per_min:.1f}/min).")

    if quality_score < 80:
        evidence.append("Speech clarity seems low (transcript quality degraded).")

    return {
        "score": round(float(score), 2),
        "evidence": evidence[:6],
        "metrics": {
            "words": words,
            "elapsed_sec": round(elapsed_sec, 3),
            "wpm": round(wpm, 2),
            "fillers": fillers,
            "fillers_per_min": round(fillers_per_min, 2),
            "long_pauses_per_min": round(long_pauses_per_min, 2),
            **pause_stats,
            "transcript_quality": q,
        },
        "subscores": {
            "wpm_score": round(wpm_score, 2),
            "filler_score": round(filler_score, 2),
            "pause_score": round(pause_score, 2),
            "long_pause_score": round(long_pause_score, 2),
            "quality_score": round(quality_score, 2),
        },
        "targets_used": {
            "target_wpm_min": int(target_wpm_min),
            "target_wpm_max": int(target_wpm_max),
            "max_fillers_per_min_good": float(max_fillers_per_min_good),
            "max_pause_ratio_good": float(max_pause_ratio_good),
            "max_long_pauses_per_min_good": float(max_long_pauses_per_min_good),
        },
    }
