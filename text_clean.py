# text_clean.py
import re

def _norm(s: str) -> str:
    s = (s or "").strip()
    s = re.sub(r"\s+", " ", s)
    return s

def dedupe_consecutive_sentences(text: str) -> str:
    """
    Removes exact consecutive duplicate sentences (very common in Whisper outputs).
    """
    t = _norm(text)
    if not t:
        return t

    # naive sentence split (good enough for MVP)
    parts = re.split(r"(?<=[.!?])\s+", t)
    out = []
    prev = None
    for p in parts:
        p2 = _norm(p)
        if not p2:
            continue
        if prev is not None and p2.lower() == prev.lower():
            continue
        out.append(p2)
        prev = p2
    return " ".join(out).strip()

def clean_transcript_for_scoring(text: str) -> str:
    # you can extend later (remove stutters, normalize contractions, etc.)
    return dedupe_consecutive_sentences(text)
