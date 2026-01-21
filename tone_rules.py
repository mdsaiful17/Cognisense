# tone_rules.py
from __future__ import annotations
from typing import Any, Dict, List, Optional
import re

DEFAULT_PROFANITY = [
    r"\bfuck\b", r"\bshit\b", r"\bbitch\b", r"\basshole\b",
    r"\bidiot\b", r"\bstupid\b", r"\bdumb\b",
]
DEFAULT_AGGRESSIVE = [
    r"\bshut up\b", r"\bi hate you\b", r"\byou people\b",
    r"\byour fault\b", r"\bnot my problem\b",
]

def _compile(patterns: List[str]) -> List[re.Pattern]:
    out = []
    for p in patterns or []:
        try:
            out.append(re.compile(p, flags=re.IGNORECASE))
        except Exception:
            out.append(re.compile(re.escape(str(p)), flags=re.IGNORECASE))
    return out

def score_tone_rules(
    transcript_text: str,
    rules: Optional[List[Dict[str, Any]]] = None,
    evidence_limit: int = 6,
) -> Dict[str, Any]:
    """
    Tone/professionalism scoring (0..100) using avoid_regex/require_regex rules.
    """
    t = transcript_text or ""
    rules = rules if isinstance(rules, list) else []

    if not rules:
        rules = [
            {
                "id": "no_profanity",
                "type": "avoid_regex",
                "desc": "Avoid profanity/insults.",
                "patterns": DEFAULT_PROFANITY,
                "points": 70,
            },
            {
                "id": "no_aggressive_phrases",
                "type": "avoid_regex",
                "desc": "Avoid aggressive or blaming language.",
                "patterns": DEFAULT_AGGRESSIVE,
                "points": 30,
            },
        ]

    total = 0.0
    violated = 0.0
    evidence: List[str] = []
    details: List[Dict[str, Any]] = []

    for r in rules:
        if not isinstance(r, dict):
            continue
        rid = str(r.get("id","")).strip() or "rule"
        rtype = str(r.get("type","avoid_regex")).strip()
        desc = str(r.get("desc","")).strip()
        pts = float(r.get("points", 100) or 100)
        pats = r.get("patterns") or []
        if not isinstance(pats, list) or not pats:
            pats = DEFAULT_PROFANITY

        comp = _compile([str(x) for x in pats if str(x).strip()])
        hit = False
        hit_pat = None
        for p in comp:
            if p.search(t):
                hit = True
                hit_pat = p.pattern
                break

        ok = True
        if rtype == "avoid_regex":
            ok = (not hit)
        elif rtype == "require_regex":
            ok = hit

        total += pts
        if not ok:
            violated += pts
            if len(evidence) < evidence_limit:
                evidence.append(f"Tone issue: {desc or rid}.")
        details.append({
            "id": rid,
            "type": rtype,
            "desc": desc,
            "points": pts,
            "ok": bool(ok),
            "hit": bool(hit),
            "hit_pattern": hit_pat,
        })

    score = 100.0 if total <= 0 else (100.0 * (1.0 - (violated / total)))
    score = max(0.0, min(100.0, score))

    return {
        "score": round(score, 2),
        "evidence": evidence[:evidence_limit],
        "details": {
            "violated_points": round(violated, 3),
            "total_points": round(total, 3),
            "rules": details,
        },
    }
