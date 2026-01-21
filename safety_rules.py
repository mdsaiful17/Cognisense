# # safety_rules.py (FAIRNESS V2 + dedupe)
# from __future__ import annotations
# from typing import Any, Dict, List, Optional
# import re

# DEFAULT_SENSITIVE = [
#     r"\bpassword\b", r"\bpasscode\b", r"\botp\b",
#     r"\b(pin|cvv|cvc)\b",
#     r"\b(card number|credit card|debit card)\b",
#     r"\b(bank account|account number)\b",
#     r"\bpassport\b", r"\bssn\b", r"\bnid\b",
# ]

# # Detect *actual* PII values (not just words like "email")
# EMAIL_VALUE = r"\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b"
# PHONE_VALUE = r"\b(?:\+?\d{1,3}[-.\s]?)?(?:\(?\d{2,4}\)?[-.\s]?)?\d{3,4}[-.\s]?\d{4}\b"
# CARD_VALUE  = r"\b(?:\d[ -]*?){13,19}\b"

# def _compile(patterns: List[str]) -> List[re.Pattern]:
#     out = []
#     for p in patterns or []:
#         s = str(p).strip()
#         if not s:
#             continue
#         try:
#             out.append(re.compile(s, flags=re.IGNORECASE))
#         except Exception:
#             out.append(re.compile(re.escape(s), flags=re.IGNORECASE))
#     return out

# def _clean_rule_patterns(pats: Any) -> List[str]:
#     """
#     Remove unfair single-word patterns like \\bemail\\b (word mention != PII value).
#     """
#     if not isinstance(pats, list):
#         return []
#     out = []
#     for p in pats:
#         s = str(p).strip()
#         if not s:
#             continue
#         sl = s.lower()
#         if sl in (r"\bemail\b", r"\bphone\b", r"\baddress\b"):
#             continue
#         out.append(s)
#     return out

# def _merge_rules_by_id(rules: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
#     """
#     Merge duplicates by id:
#     - combine patterns
#     - keep max(points)
#     - keep first type/desc unless missing
#     """
#     merged: Dict[str, Dict[str, Any]] = {}
#     order: List[str] = []

#     for r in rules:
#         if not isinstance(r, dict):
#             continue
#         rid = str(r.get("id", "")).strip() or "rule"
#         rtype = str(r.get("type", "avoid_regex")).strip()
#         desc = str(r.get("desc", "")).strip()
#         pts = float(r.get("points", 100) or 100)
#         pats = _clean_rule_patterns(r.get("patterns") or [])

#         if rid not in merged:
#             merged[rid] = {
#                 "id": rid,
#                 "type": rtype,
#                 "desc": desc,
#                 "points": pts,
#                 "patterns": pats,
#             }
#             order.append(rid)
#         else:
#             m = merged[rid]
#             m["points"] = max(float(m.get("points", 0) or 0), pts)
#             if not m.get("desc") and desc:
#                 m["desc"] = desc
#             if not m.get("type") and rtype:
#                 m["type"] = rtype

#             existing = set(str(x) for x in (m.get("patterns") or []))
#             for p in pats:
#                 if p not in existing:
#                     (m.setdefault("patterns", [])).append(p)
#                     existing.add(p)

#     return [merged[rid] for rid in order if rid in merged]

# def score_safety_rules(
#     transcript_text: str,
#     rules: Optional[List[Dict[str, Any]]] = None,
#     evidence_limit: int = 6,
# ) -> Dict[str, Any]:
#     t = transcript_text or ""
#     rules = rules if isinstance(rules, list) else []

#     # Base always enforced (PII-value detectors are fairness-critical)
#     base_rules = [
#         {
#             "id": "no_sensitive_info",
#             "type": "avoid_regex",
#             "desc": "Do not request or share passwords/OTP/card/bank identifiers.",
#             "patterns": DEFAULT_SENSITIVE,
#             "points": 60,
#         },
#         {
#             "id": "no_email_value",
#             "type": "avoid_regex",
#             "desc": "Do not share full email addresses.",
#             "patterns": [EMAIL_VALUE],
#             "points": 15,
#         },
#         {
#             "id": "no_phone_value",
#             "type": "avoid_regex",
#             "desc": "Do not share full phone numbers.",
#             "patterns": [PHONE_VALUE],
#             "points": 15,
#         },
#         {
#             "id": "no_card_value",
#             "type": "avoid_regex",
#             "desc": "Do not share card numbers.",
#             "patterns": [CARD_VALUE],
#             "points": 10,
#         },
#     ]

#     # Combine DB rules + base rules, then dedupe by id
#     combined = []
#     for r in rules:
#         if not isinstance(r, dict):
#             continue
#         rr = dict(r)
#         rr["patterns"] = _clean_rule_patterns(rr.get("patterns") or [])
#         combined.append(rr)

#     combined.extend(base_rules)
#     rules_final = _merge_rules_by_id(combined)

#     total = 0.0
#     violated = 0.0
#     evidence: List[str] = []
#     details: List[Dict[str, Any]] = []

#     for r in rules_final:
#         rid = str(r.get("id","")).strip() or "rule"
#         rtype = str(r.get("type","avoid_regex")).strip()
#         desc = str(r.get("desc","")).strip()
#         pts = float(r.get("points", 100) or 100)
#         pats = r.get("patterns") or []
#         comp = _compile(pats if isinstance(pats, list) else [])

#         hit = False
#         hit_pat = None
#         for p in comp:
#             if p.search(t):
#                 hit = True
#                 hit_pat = p.pattern
#                 break

#         ok = True
#         if rtype == "avoid_regex":
#             ok = (not hit)
#         elif rtype == "require_regex":
#             ok = hit

#         total += pts
#         if not ok:
#             violated += pts
#             if len(evidence) < evidence_limit:
#                 evidence.append(f"Safety issue: {desc or rid}.")
#         details.append({
#             "id": rid, "type": rtype, "desc": desc, "points": pts,
#             "ok": bool(ok), "hit": bool(hit), "hit_pattern": hit_pat,
#         })

#     score = 100.0 if total <= 0 else (100.0 * (1.0 - (violated / total)))
#     score = max(0.0, min(100.0, score))

#     return {
#         "score": round(score, 2),
#         "evidence": evidence[:evidence_limit],
#         "details": {
#             "violated_points": round(violated, 3),
#             "total_points": round(total, 3),
#             "rules": details,
#         },
#     }
