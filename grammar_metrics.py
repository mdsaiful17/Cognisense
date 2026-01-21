from __future__ import annotations

import json
import os
import re
from dataclasses import dataclass
from typing import Any, Dict, List, Optional
from urllib.parse import urlencode
from urllib.request import Request, urlopen

WORD_RE = re.compile(r"[A-Za-z']+")
SENT_SPLIT = re.compile(r"(?<=[.!?])\s+")

# -----------------------------
# LanguageTool issue weighting
# -----------------------------
# In this "core grammar only" mode:
# - We count ONLY issueType == "grammar" by default.
# - We ignore misspelling/typographical/style/uncategorized unless explicitly enabled.
_DEFAULT_LT_WEIGHTS = {
    "grammar": 1.00,
    "misspelling": 0.80,
    "typographical": 0.50,
    "style": 0.20,
    "uncategorized": 0.50,
}

# Fillers list (used if GRAMMAR_REMOVE_FILLERS=1)
_FILLERS = ["um", "uh", "erm", "like", "you know", "i mean", "sort of", "kind of"]


def _clamp(x: float, lo: float, hi: float) -> float:
    return lo if x < lo else hi if x > hi else x


def _word_count(text: str) -> int:
    return len(WORD_RE.findall(text or ""))


def _env_bool(name: str, default: bool) -> bool:
    v = os.getenv(name, "1" if default else "0").strip().lower()
    return v not in {"0", "false", "no", "off", ""}


def _env_float(name: str, default: float) -> float:
    v = (os.getenv(name) or "").strip()
    if not v:
        return float(default)
    try:
        return float(v)
    except Exception:
        return float(default)


def _env_int(name: str, default: int) -> int:
    v = (os.getenv(name) or "").strip()
    if not v:
        return int(default)
    try:
        return int(v)
    except Exception:
        return int(default)


@dataclass(frozen=True)
class GrammarConfig:
    endpoint: str
    # Core policy toggles
    grammar_only: bool
    include_misspelling: bool
    include_uncategorized: bool
    include_typo: bool
    include_style: bool

    # Scoring / cleanup
    err_mult: float
    remove_fillers: bool
    min_words: int          # soft minimum; affects denominator for scoring stability
    hard_min_words: int     # hard minimum; below this we don't score

    # Rule filters
    rule_blocklist_regex: re.Pattern


def _get_config() -> GrammarConfig:
    """
    Read env vars at call-time so settings can be changed without restarting.
    Defaults are set for: CORE GRAMMAR ONLY (no hyphen/compound/punctuation nags).
    """
    endpoint = (os.getenv("GRAMMAR_API_URL") or "").strip()

    # Core behavior:
    # Default TRUE: only count issueType == "grammar"
    grammar_only = _env_bool("GRAMMAR_ONLY_CORE", True)

    # Default FALSE: don't count spelling/punctuation/style/uncategorized
    include_misspelling = _env_bool("GRAMMAR_INCLUDE_SPELLING", False)
    include_uncategorized = _env_bool("GRAMMAR_INCLUDE_UNCATEGORIZED", False)
    include_typo = _env_bool("GRAMMAR_INCLUDE_TYPO", False)
    include_style = _env_bool("GRAMMAR_INCLUDE_STYLE", False)

    # Higher = stricter grading. A good starting range: 8..14
    err_mult = _env_float("GRAMMAR_ERR_MULT", 10.0)

    # Remove common spoken fillers before checking grammar
    remove_fillers = _env_bool("GRAMMAR_REMOVE_FILLERS", True)

    # Short-text handling
    min_words = max(3, _env_int("GRAMMAR_MIN_WORDS", 8))
    hard_min_words = max(1, _env_int("GRAMMAR_HARD_MIN_WORDS", 3))

    # Rule blocklist:
    # Default: block hyphen/compound formatting rules + any message mentioning hyphen.
    # This fixes your exact complaint ("problem solving" -> "problem-solving", "user friendly" -> "user-friendly").
    default_block = r"(?i)(HYPHEN|EN_COMPOUNDS|COMPOUND|HYPHENATION|with a hyphen|spelled with a hyphen)"
    custom_block = (os.getenv("GRAMMAR_RULE_BLOCKLIST_REGEX") or "").strip()
    rule_blocklist_regex = re.compile(custom_block if custom_block else default_block)

    return GrammarConfig(
        endpoint=endpoint,
        grammar_only=grammar_only,
        include_misspelling=include_misspelling,
        include_uncategorized=include_uncategorized,
        include_typo=include_typo,
        include_style=include_style,
        err_mult=err_mult,
        remove_fillers=remove_fillers,
        min_words=min_words,
        hard_min_words=hard_min_words,
        rule_blocklist_regex=rule_blocklist_regex,
    )


def _estimate_transcript_quality(text: str) -> Dict[str, float]:
    """
    Rough proxy for ASR quality (0..100).
    We use it only to reduce penalty when text looks corrupted.
    """
    t = (text or "").strip()
    if not t:
        return {
            "bad_char_ratio": 1.0,
            "weird_token_ratio": 1.0,
            "repetition_ratio": 1.0,
            "quality_score": 0.0,
        }

    bad = sum(1 for ch in t if ord(ch) < 32 and ch not in "\n\t") + t.count("\ufffd")
    bad_char_ratio = bad / max(1, len(t))

    toks = re.findall(r"\S+", t)
    weird = 0
    for tok in toks:
        punct = sum(1 for ch in tok if not ch.isalnum() and ch not in "'")
        digits = sum(1 for ch in tok if ch.isdigit())
        if (punct >= 2) or (digits >= 2):
            weird += 1
    weird_token_ratio = weird / max(1, len(toks))

    words = [w.lower() for w in WORD_RE.findall(t)]
    bigrams = list(zip(words, words[1:]))
    rep = 0
    seen = set()
    for bg in bigrams:
        if bg in seen:
            rep += 1
        seen.add(bg)
    repetition_ratio = rep / max(1, len(bigrams))

    penalty = (bad_char_ratio * 200.0) + (weird_token_ratio * 120.0) + (repetition_ratio * 120.0)
    quality_score = _clamp(100.0 - penalty, 0.0, 100.0)

    return {
        "bad_char_ratio": round(bad_char_ratio, 4),
        "weird_token_ratio": round(weird_token_ratio, 4),
        "repetition_ratio": round(repetition_ratio, 4),
        "quality_score": round(quality_score, 2),
    }


def _remove_fillers_preserve_case(text: str) -> str:
    """
    Remove filler tokens/phrases without lowercasing the entire transcript.
    """
    t = text
    for f in sorted(_FILLERS, key=len, reverse=True):
        pat = re.compile(rf"(?<!\w){re.escape(f)}(?!\w)", flags=re.IGNORECASE)
        t = pat.sub(" ", t)
    return t


def _clean_for_grammar(text: str, cfg: GrammarConfig) -> str:
    t = (text or "").strip()
    if not t:
        return t
    t = re.sub(r"\s+", " ", t).strip()
    if cfg.remove_fillers:
        t = _remove_fillers_preserve_case(t)
        t = re.sub(r"\s+", " ", t).strip()
    return t


def _post_form(url: str, data: Dict[str, Any], timeout: float = 6.0) -> Dict[str, Any]:
    body = urlencode({k: str(v) for k, v in data.items()}).encode("utf-8")
    req = Request(
        url,
        data=body,
        headers={"Content-Type": "application/x-www-form-urlencoded; charset=utf-8"},
        method="POST",
    )
    with urlopen(req, timeout=timeout) as r:
        raw = r.read().decode("utf-8", errors="replace")
    return json.loads(raw)


def _lt_issue_allowed(issue_type: str, cfg: GrammarConfig) -> bool:
    it = (issue_type or "uncategorized").strip().lower()

    # Core requirement: grammar-only
    if cfg.grammar_only and it != "grammar":
        return False

    # If not grammar-only, apply toggles
    if it == "style":
        return cfg.include_style
    if it == "typographical":
        return cfg.include_typo
    if it == "misspelling":
        return cfg.include_misspelling
    if it == "uncategorized":
        return cfg.include_uncategorized

    # grammar
    return True


def _lt_issue_weight(issue_type: str, cfg: GrammarConfig) -> float:
    if not _lt_issue_allowed(issue_type, cfg):
        return 0.0
    it = (issue_type or "uncategorized").strip().lower()
    return float(_DEFAULT_LT_WEIGHTS.get(it, _DEFAULT_LT_WEIGHTS["uncategorized"]))


def _is_blocked_rule(rule_id: str, message: str, cfg: GrammarConfig) -> bool:
    """
    Block hyphen/compound formatting nags (and anything you add via GRAMMAR_RULE_BLOCKLIST_REGEX).
    We check both rule_id and message because some LT matches have missing rule ids.
    """
    rid = (rule_id or "").strip()
    msg = (message or "").strip()
    hay = f"{rid} {msg}".strip()
    return bool(hay and cfg.rule_blocklist_regex.search(hay))


def _denom_for_scoring(words: int, cfg: GrammarConfig) -> int:
    if words <= 0:
        return cfg.min_words
    return cfg.min_words if words < cfg.min_words else words


def _score_with_languagetool(
    text: str,
    language: str,
    cfg: GrammarConfig,
    evidence_limit: int,
    timeout_sec: float,
) -> Optional[Dict[str, Any]]:
    """
    Real grammar scoring using LanguageTool.
    cfg.endpoint must look like: http://host:port/v2/check
    """
    try:
        data = _post_form(cfg.endpoint, {"text": text, "language": language}, timeout=timeout_sec)
        matches = data.get("matches", []) or []

        weighted_err = 0.0
        evidence: List[str] = []
        counted_matches = 0

        ignored_by_type = 0
        ignored_by_blocklist = 0

        for m in matches:
            rule = m.get("rule") or {}
            issue = (rule.get("issueType") or "uncategorized").strip().lower()
            msg = (m.get("message") or "").strip()
            rule_id = (rule.get("id") or "").strip()

            # 1) ignore non-grammar (by default) and any disabled types
            w = _lt_issue_weight(issue, cfg)
            if w <= 0.0:
                ignored_by_type += 1
                continue

            # 2) ignore hyphen/compound formatting rules
            if _is_blocked_rule(rule_id, msg, cfg):
                ignored_by_blocklist += 1
                continue

            weighted_err += w
            counted_matches += 1

            if len(evidence) < evidence_limit:
                ctx = (m.get("context") or {}) or {}
                ctx_txt = (ctx.get("text") or "").replace("\n", " ").strip()
                off = int(ctx.get("offset") or 0)
                ln = int(ctx.get("length") or 0)
                snippet = ctx_txt[max(0, off - 25) : off + ln + 25].strip() if ctx_txt else ""

                reps = [r.get("value") for r in (m.get("replacements") or [])[:2] if r.get("value")]
                rep_txt = f" | suggestions: {', '.join(reps)}" if reps else ""

                rid = f" [{rule_id}]" if rule_id else ""
                evidence.append(f"{issue}{rid}: {msg} | “…{snippet}…”{rep_txt}")

        words = _word_count(text)
        if words < cfg.hard_min_words:
            return {
                "score": 0.0,
                "score_raw_100": 0.0,
                "evidence": ["Too short for grammar scoring."],
                "details": {
                    "engine": "languagetool",
                    "words": words,
                    "hard_min_words": cfg.hard_min_words,
                },
            }

        denom = _denom_for_scoring(words, cfg)
        err_per_100 = (weighted_err / float(denom)) * 100.0
        score_raw_100 = _clamp(100.0 - (err_per_100 * float(cfg.err_mult)), 0.0, 100.0)

        details: Dict[str, Any] = {
            "engine": "languagetool",
            "endpoint_used": bool(cfg.endpoint),
            "language": language,
            "words": words,
            "denom_used": denom,
            "matches_total": len(matches),
            "matches_counted": counted_matches,
            "ignored_by_type": ignored_by_type,
            "ignored_by_blocklist": ignored_by_blocklist,
            "weighted_errors": round(weighted_err, 2),
            "weighted_err_per_100_words": round(err_per_100, 2),
            "err_mult": float(cfg.err_mult),
            "policy": {
                "grammar_only": cfg.grammar_only,
                "include_misspelling": cfg.include_misspelling,
                "include_uncategorized": cfg.include_uncategorized,
                "include_typo": cfg.include_typo,
                "include_style": cfg.include_style,
            },
        }
        if words < cfg.min_words:
            details["note"] = f"Short text: used denom={denom} (GRAMMAR_MIN_WORDS) to avoid over-penalizing."

        return {
            "score": round(score_raw_100, 2),
            "score_raw_100": round(score_raw_100, 2),
            "evidence": evidence,
            "details": details,
        }
    except Exception as e:
        return {"_lt_error": str(e)}


def _score_with_heuristics(text: str, cfg: GrammarConfig, evidence_limit: int) -> Dict[str, Any]:
    """
    Fallback only. Minimal "core grammar-ish" heuristics.
    (No hyphen/typo/style checks.)
    """
    t = (text or "").strip()
    words = _word_count(t)
    if words < cfg.hard_min_words:
        return {
            "score": 0.0,
            "score_raw_100": 0.0,
            "evidence": ["Too short for grammar scoring."],
            "details": {"engine": "heuristic", "words": words, "hard_min_words": cfg.hard_min_words},
        }

    evidence: List[str] = []
    weighted_err = 0.0

    # Lowercase "i" as standalone pronoun is a real grammar convention
    i_hits = len(re.findall(r"(?<![A-Za-z])i(?![A-Za-z])", t))
    if i_hits:
        weighted_err += 0.6 * i_hits
        evidence.append(f"grammar: standalone 'i' should be capitalized ({i_hits} time(s)).")

    # Run-on proxy (very long sentences)
    sents = [s.strip() for s in SENT_SPLIT.split(t) if s.strip()] or [t]
    long_sents = sum(1 for s in sents if len(WORD_RE.findall(s)) >= 38)
    if long_sents:
        weighted_err += 1.2 * long_sents
        evidence.append(f"grammar: possible run-on sentence(s): {long_sents} very long sentence(s).")

    evidence = evidence[:evidence_limit]

    denom = _denom_for_scoring(words, cfg)
    err_per_100 = (weighted_err / float(denom)) * 100.0
    score_raw_100 = _clamp(100.0 - (err_per_100 * float(cfg.err_mult)), 0.0, 100.0)

    return {
        "score": round(score_raw_100, 2),
        "score_raw_100": round(score_raw_100, 2),
        "evidence": evidence,
        "details": {
            "engine": "heuristic",
            "words": words,
            "denom_used": denom,
            "weighted_errors": round(weighted_err, 2),
            "weighted_err_per_100_words": round(err_per_100, 2),
            "sentences": len(sents),
            "long_sentences": long_sents,
            "err_mult": float(cfg.err_mult),
        },
    }


def score_grammar_metrics(
    transcript_text: str,
    language: str = "en-US",
    evidence_limit: int = 6,
    use_remote_if_available: bool = True,
    timeout_sec: float = 6.0,
) -> Dict[str, Any]:
    """
    Returns: {"score": 0..100, "evidence": [...], "details": {...}}

    Uses LanguageTool if GRAMMAR_API_URL is set, else heuristic fallback.
    This version is tuned for CORE GRAMMAR ONLY by default (no hyphen/compound nags).
    """
    cfg = _get_config()

    raw_text = (transcript_text or "").strip()
    q = _estimate_transcript_quality(raw_text)

    cleaned = _clean_for_grammar(raw_text, cfg)

    lt_res: Optional[Dict[str, Any]] = None
    lt_error: Optional[str] = None

    if use_remote_if_available and cfg.endpoint:
        tmp = _score_with_languagetool(
            text=cleaned,
            language=language,
            cfg=cfg,
            evidence_limit=int(evidence_limit),
            timeout_sec=float(timeout_sec),
        )
        if isinstance(tmp, dict) and "_lt_error" in tmp:
            lt_error = tmp.get("_lt_error") or "Unknown LanguageTool error"
            lt_res = None
        else:
            lt_res = tmp if isinstance(tmp, dict) else None

    res = lt_res if isinstance(lt_res, dict) else _score_with_heuristics(cleaned, cfg, evidence_limit=int(evidence_limit))

    # Fairness gate: if transcript quality is low, reduce penalty impact
    quality = float(q.get("quality_score", 100.0))
    soften = 0.60 + 0.40 * (quality / 100.0)  # 0.60..1.00

    raw_score = float(res.get("score_raw_100", res.get("score", 0.0)) or 0.0)
    adjusted = _clamp(raw_score * soften + (100.0 * (1.0 - soften)), 0.0, 100.0)

    res["score_raw_100"] = round(adjusted, 2)
    res["score"] = round(adjusted, 2)

    res.setdefault("details", {})
    if isinstance(res["details"], dict):
        res["details"]["transcript_quality"] = q
        res["details"]["soften_factor"] = round(soften, 3)
        res["details"]["used_remote"] = bool(lt_res)
        res["details"]["cleaned_for_grammar"] = bool(cleaned != raw_text)
        res["details"]["endpoint_configured"] = bool(cfg.endpoint)

        res["details"]["config"] = {
            "grammar_only": bool(cfg.grammar_only),
            "include_misspelling": bool(cfg.include_misspelling),
            "include_uncategorized": bool(cfg.include_uncategorized),
            "include_typo": bool(cfg.include_typo),
            "include_style": bool(cfg.include_style),
            "err_mult": float(cfg.err_mult),
            "remove_fillers": bool(cfg.remove_fillers),
            "min_words": int(cfg.min_words),
            "hard_min_words": int(cfg.hard_min_words),
            "rule_blocklist_regex": cfg.rule_blocklist_regex.pattern,
        }

        if not cfg.endpoint:
            res["details"]["note"] = "GRAMMAR_API_URL not set; using heuristic fallback."
        elif lt_error and not lt_res:
            res["details"]["note"] = "LanguageTool configured but failed; using heuristic fallback."
            res["details"]["languagetool_error"] = lt_error

    return res


def grammar_metrics(transcript_text: str, **kwargs) -> Dict[str, Any]:
    return score_grammar_metrics(transcript_text, **kwargs)
