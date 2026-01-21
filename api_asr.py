# api_asr.py
import os
import re
import uuid
import json
import traceback
import subprocess
import sys
import time
import inspect

import pymysql
from fastapi import FastAPI, UploadFile, File, Form, HTTPException, Request, Query
from fastapi.responses import JSONResponse

from asr_faster_whisper import transcribe_for_rubric
from fluency_metrics import score_fluency
from timing_score import score_timing
from order_rules import score_order_and_clarity_rules
from text_clean import clean_transcript_for_scoring

from tone_rules import score_tone_rules
from grammar_metrics import score_grammar_metrics

# ✅ entailment coverage
from coverage_entailment import score_coverage_entailment

app = FastAPI()

UPLOAD_DIR = "uploads"
os.makedirs(UPLOAD_DIR, exist_ok=True)

METHOD_ALIASES = {
    "constructive_feedback_rules": "order_and_clarity_rules",
    "serious_feedback_rules": "order_and_clarity_rules",
    "idea_pitch_rules": "order_and_clarity_rules",
    "networking_intro_rules": "order_and_clarity_rules",
    "stall_breaking_rules": "order_and_clarity_rules",
}

HARD_MAX_SEC = 180.0
OFFTOPIC_FINAL_CAP_RATIO = 0.30


def normalize_expected_points(obj):
    if obj is None:
        return []
    if isinstance(obj, list):
        return [p for p in obj if isinstance(p, dict)]
    if isinstance(obj, dict):
        if isinstance(obj.get("points"), list):
            return [p for p in (obj.get("points") or []) if isinstance(p, dict)]
        if isinstance(obj.get("expected_points"), list):
            return [p for p in (obj.get("expected_points") or []) if isinstance(p, dict)]
        if any(k in obj for k in ("id", "label", "desc", "description", "keywords", "anchors")):
            return [obj]
    return []


def _scale_score_result(res: dict, factor: float) -> dict:
    if not isinstance(res, dict):
        return res
    out = dict(res)
    if "score" in out and out["score"] is not None:
        try:
            raw = float(out["score"])
        except Exception:
            raw = 0.0
        out["score_raw_100"] = raw
        out["score"] = round(raw * float(factor), 2)
    return out


def ensure_wav_16k_mono(in_path: str, max_sec: float = HARD_MAX_SEC) -> str:
    base = os.path.splitext(os.path.basename(in_path))[0]
    out_wav = os.path.join(UPLOAD_DIR, f"{base}_16k.wav")
    cmd = [
        "ffmpeg", "-y",
        "-i", in_path,
        "-t", str(float(max_sec)),
        "-vn",
        "-ar", "16000",
        "-ac", "1",
        out_wav
    ]
    try:
        subprocess.run(cmd, check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        return out_wav
    except Exception:
        return in_path


def _resolve_method(method: str) -> str:
    if not method:
        return method

    method = str(method).strip()

    if method in METHOD_ALIASES:
        return METHOD_ALIASES[method]

    if method in (
        "coverage_from_expected_points",
        "coverage_entailment_v1",   # ✅ supported
        "audio_metrics",
        "elapsed_sec_vs_recommended",
        "tone_rules",
        "grammar_metrics",
    ):
        return method

    if method.endswith("_rules"):
        return "order_and_clarity_rules"

    return method


def _is_off_topic_result(res: dict) -> bool:
    if not isinstance(res, dict):
        return False
    details = res.get("details") if isinstance(res.get("details"), dict) else {}
    return bool(details.get("off_topic", False))


def _filter_kwargs(fn, kw: dict) -> dict:
    if not isinstance(kw, dict) or not kw:
        return {}
    sig = inspect.signature(fn)
    params = sig.parameters.values()
    if any(p.kind == inspect.Parameter.VAR_KEYWORD for p in params):
        return kw
    allowed = set(sig.parameters.keys())
    return {k: v for k, v in kw.items() if k in allowed}


def _pop_reserved_kwargs(params: dict, reserved: list) -> dict:
    if not isinstance(params, dict):
        return {}
    out = dict(params)
    for k in reserved:
        out.pop(k, None)
    return out


# ----------------------------
# Rubric segmenter support
# ----------------------------
_SENT_SPLIT = re.compile(r"(?<=[.!?])\s+")

def build_punct_sentence_segments(text: str, min_chars: int = 24, max_chars: int = 240):
    text = (text or "").strip()
    if not text:
        return []

    sents = [s.strip() for s in _SENT_SPLIT.split(text) if s.strip()]

    merged = []
    buf = ""
    for s in sents:
        if not buf:
            buf = s
        elif len(buf) < int(min_chars):
            buf = buf + " " + s
        else:
            merged.append(buf.strip())
            buf = s
    if buf.strip():
        merged.append(buf.strip())

    final = []
    for s in merged:
        if len(s) <= int(max_chars):
            final.append(s)
            continue
        parts = re.split(r"(?<=,)\s+|\s{2,}", s)
        chunk = ""
        for p in parts:
            p = p.strip()
            if not p:
                continue
            if not chunk:
                chunk = p
            elif len(chunk) + 1 + len(p) <= int(max_chars):
                chunk = chunk + " " + p
            else:
                final.append(chunk.strip())
                chunk = p
        if chunk.strip():
            final.append(chunk.strip())

    return [{"text": s} for s in final if s]


@app.get("/debug/runtime")
def debug_runtime():
    info = {"sys_executable": sys.executable}
    try:
        r = subprocess.run(["ffmpeg", "-version"], capture_output=True, text=True, timeout=3)
        info["ffmpeg_ok"] = (r.returncode == 0)
    except Exception as e:
        info["ffmpeg_ok"] = False
        info["ffmpeg_error"] = str(e)
    return info


@app.post("/debug/warmup")
def debug_warmup(load_sbert: int = Query(1), load_nli: int = Query(1)):
    """
    Use this before demo so first evaluate() isn't 150-200s due to model download/init.
    """
    t0 = time.perf_counter()
    out = {"ok": True}

    if load_sbert:
        try:
            from coverage_sbert import get_sbert
            _ = get_sbert()
            out["sbert_loaded"] = True
        except Exception as e:
            out["sbert_loaded"] = False
            out["sbert_error"] = str(e)

    if load_nli:
        # tiny entailment call to force CrossEncoder init + label mapping
        try:
            _ = score_coverage_entailment(
                "hello",
                [{"id": "x", "label": "Greeting", "weight": 1.0, "desc": "Say hello."}],
                segments=[{"text": "hello"}],
                unit_mode="segments",
                retrieval_top_k=1,
                min_sim_for_candidate=0.0,
                max_pairs_per_batch=4,
                entail_threshold=0.5,
            )
            out["nli_loaded"] = True
        except Exception as e:
            out["nli_loaded"] = False
            out["nli_error"] = str(e)

    out["warmup_sec"] = round(time.perf_counter() - t0, 3)
    return out


@app.exception_handler(Exception)
async def debug_exception_handler(request: Request, exc: Exception):
    return JSONResponse(
        status_code=500,
        content={"error": str(exc), "trace": traceback.format_exc()},
    )


def fetch_scenario_bundle(scenario_id: int):
    conn = pymysql.connect(
        host="127.0.0.1",
        user="root",
        password="",
        database="cognisense",
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
    )
    try:
        with conn.cursor() as cur:
            cur.execute(
                "SELECT rubric_json, expected_points_json, recommended_duration_json "
                "FROM scenarios WHERE id=%s",
                (scenario_id,),
            )
            row = cur.fetchone()
            if not row:
                raise HTTPException(status_code=404, detail="Scenario not found")

            def parse_json(x):
                if x is None:
                    return None
                if isinstance(x, (bytes, bytearray)):
                    x = x.decode("utf-8", errors="replace")
                if isinstance(x, str):
                    return json.loads(x)
                return x

            return {
                "rubric_json": parse_json(row["rubric_json"]),
                "expected_points_json": parse_json(row["expected_points_json"]),
                "recommended_duration_json": parse_json(row["recommended_duration_json"]),
            }
    finally:
        conn.close()


@app.post("/asr")
async def asr(file: UploadFile = File(...)):
    ext = os.path.splitext(file.filename or "")[1].lower() or ".bin"
    path = os.path.join(UPLOAD_DIR, f"{uuid.uuid4().hex}{ext}")

    data = await file.read()
    with open(path, "wb") as f:
        f.write(data)

    wav_path = ensure_wav_16k_mono(path, max_sec=HARD_MAX_SEC)
    out = transcribe_for_rubric(wav_path)

    raw_text = out.get("transcript_text", "")
    clean_text = clean_transcript_for_scoring(raw_text)

    return {
        "audio_path": path,
        "asr_wav_path": wav_path,
        "transcript_text": raw_text,
        "transcript_text_clean": clean_text,
        "segments": out.get("segments", []),
        "elapsed_sec": out.get("elapsed_sec", 0.0),
    }


@app.post("/evaluate")
async def evaluate(
    scenario_id: int = Form(...),
    file: UploadFile = File(...),
    score_scale: float = Query(10.0),
):
    # keep SBERT coverage as fallback
    from coverage_sbert import score_coverage_sbert

    t0 = time.perf_counter()
    t_save = t0
    t_asr = t0
    t_cov = t0
    t_ord = t0
    t_flu = t0
    t_gram = t0
    t_tim = t0

    ext = os.path.splitext(file.filename or "")[1].lower() or ".bin"
    path = os.path.join(UPLOAD_DIR, f"{uuid.uuid4().hex}{ext}")

    data = await file.read()
    with open(path, "wb") as f:
        f.write(data)
    t_save = time.perf_counter()

    wav_path = ensure_wav_16k_mono(path, max_sec=HARD_MAX_SEC)
    asr_out = transcribe_for_rubric(wav_path)
    t_asr = time.perf_counter()

    bundle = fetch_scenario_bundle(scenario_id)
    rubric = bundle["rubric_json"] or {}
    expected_points_raw = bundle["expected_points_json"]
    expected_points = normalize_expected_points(expected_points_raw)
    rec_dur = bundle["recommended_duration_json"] or {}

    scale = float(score_scale or 10.0)
    if scale <= 0:
        scale = 10.0
    factor = scale / 100.0

    raw_text = asr_out.get("transcript_text", "")
    clean_text = clean_transcript_for_scoring(raw_text)

    dims_raw = rubric.get("dimensions", []) if isinstance(rubric, dict) else []
    if not isinstance(dims_raw, list):
        dims_raw = []

    # Safety removed
    dims = []
    skipped_safety = 0
    for d in dims_raw:
        if not isinstance(d, dict):
            continue
        m = (d.get("method") or "").strip()
        if m == "safety_rules" or (d.get("id") == "safety_rules"):
            skipped_safety += 1
            continue
        dims.append(d)

    dim_cfg = {}
    for d in dims:
        m = d.get("method")
        if not m:
            continue
        mu = _resolve_method(m)
        if mu and mu not in dim_cfg:
            dim_cfg[mu] = d

    # params
    cov_params_raw = (dim_cfg.get("coverage_from_expected_points") or {}).get("params") or {}
    ent_params_raw = (dim_cfg.get("coverage_entailment_v1") or {}).get("params") or {}
    ord_params_raw = (dim_cfg.get("order_and_clarity_rules") or {}).get("params") or {}
    aud_targets_raw = (dim_cfg.get("audio_metrics") or {}).get("targets") or {}
    tone_rules_cfg = (dim_cfg.get("tone_rules") or {}).get("rules", None)
    gram_params_raw = (dim_cfg.get("grammar_metrics") or {}).get("params") or {}

    # segmenter selection (prefer entailment, then sbert, then order)
    seg_cfg = None
    if isinstance(ent_params_raw, dict) and isinstance(ent_params_raw.get("segmenter"), dict):
        seg_cfg = ent_params_raw.get("segmenter")
    elif isinstance(cov_params_raw, dict) and isinstance(cov_params_raw.get("segmenter"), dict):
        seg_cfg = cov_params_raw.get("segmenter")
    elif isinstance(ord_params_raw, dict) and isinstance(ord_params_raw.get("segmenter"), dict):
        seg_cfg = ord_params_raw.get("segmenter")

    scoring_segments = asr_out.get("segments", []) or []
    segmenter_used = {"type": "whisper_segments"}

    if isinstance(seg_cfg, dict) and seg_cfg.get("type") == "punctuation_sentence":
        scoring_segments = build_punct_sentence_segments(
            clean_text,
            min_chars=int(seg_cfg.get("min_chars", 24)),
            max_chars=int(seg_cfg.get("max_chars", 240)),
        )
        segmenter_used = {
            "type": "punctuation_sentence",
            "min_chars": int(seg_cfg.get("min_chars", 24)),
            "max_chars": int(seg_cfg.get("max_chars", 240)),
            "unit_count": len(scoring_segments),
        }

    # Filter kwargs
    ent_params = _filter_kwargs(score_coverage_entailment, dict(ent_params_raw) if isinstance(ent_params_raw, dict) else {})
    cov_params = _filter_kwargs(score_coverage_sbert, dict(cov_params_raw) if isinstance(cov_params_raw, dict) else {})
    ord_params = _filter_kwargs(score_order_and_clarity_rules, dict(ord_params_raw) if isinstance(ord_params_raw, dict) else {})
    aud_targets = _filter_kwargs(score_fluency, dict(aud_targets_raw) if isinstance(aud_targets_raw, dict) else {})
    gram_params = _filter_kwargs(score_grammar_metrics, dict(gram_params_raw) if isinstance(gram_params_raw, dict) else {})

    # Unit modes
    ent_unit_mode = ent_params.pop("unit_mode", "auto")
    cov_unit_mode = cov_params.pop("unit_mode", "auto")
    ord_unit_mode = ord_params.pop("unit_mode", "auto")

    # Remove reserved / duplicate params
    reserved = ["segments", "transcript_text", "points_or_obj", "expected_points_obj", "points"]
    ent_params = _pop_reserved_kwargs(ent_params, reserved)
    cov_params = _pop_reserved_kwargs(cov_params, reserved)
    ord_params = _pop_reserved_kwargs(ord_params, reserved)

    # Drop segmenter from params
    if isinstance(ent_params, dict): ent_params.pop("segmenter", None)
    if isinstance(cov_params, dict): cov_params.pop("segmenter", None)
    if isinstance(ord_params, dict): ord_params.pop("segmenter", None)

    # ---- scoring ----

    # ✅ coverage: prefer entailment if rubric requests it
    if "coverage_entailment_v1" in dim_cfg:
        try:
            coverage = score_coverage_entailment(
                clean_text,
                expected_points,
                segments=scoring_segments,
                unit_mode=ent_unit_mode,
                **ent_params,
            )
        except Exception as e:
            # fallback to SBERT similarity coverage if NLI fails
            coverage = score_coverage_sbert(
                clean_text,
                expected_points,
                segments=scoring_segments,
                unit_mode=cov_unit_mode,
                full_credit_requires_keyword_hit=False,
                **cov_params,
            )
            # attach reason for debugging
            if isinstance(coverage, dict):
                coverage.setdefault("details", {})
                if isinstance(coverage["details"], dict):
                    coverage["details"]["entailment_fallback"] = True
                    coverage["details"]["entailment_error"] = str(e)
    else:
        coverage = score_coverage_sbert(
            clean_text,
            expected_points,
            segments=scoring_segments,
            unit_mode=cov_unit_mode,
            full_credit_requires_keyword_hit=False,
            **cov_params,
        )
    t_cov = time.perf_counter()

    order_rules = score_order_and_clarity_rules(
        clean_text,
        expected_points,
        segments=scoring_segments,
        unit_mode=ord_unit_mode,
        **ord_params,
    )
    t_ord = time.perf_counter()

    fluency = score_fluency(
        transcript_text=clean_text,
        segments=asr_out.get("segments", []),  # keep whisper segments for pauses
        elapsed_sec=asr_out.get("elapsed_sec", 0.0),
        **aud_targets,
    )
    t_flu = time.perf_counter()

    grammar = score_grammar_metrics(
        clean_text,
        **gram_params,
    ) if "grammar_metrics" in dim_cfg else {"score": 100.0, "evidence": [], "details": {"note": "grammar_metrics dimension not in rubric"}}
    t_gram = time.perf_counter()

    timing = score_timing(asr_out.get("elapsed_sec", 0.0), rec_dur)
    t_tim = time.perf_counter()

    if "tone_rules" in dim_cfg:
        tone = score_tone_rules(clean_text, rules=tone_rules_cfg)
    else:
        tone = {"score": 100.0, "evidence": [], "details": {"note": "tone_rules dimension not in rubric"}}

    # ---- scale to /score_scale ----
    coverage = _scale_score_result(coverage, factor)
    order_rules = _scale_score_result(order_rules, factor)
    fluency = _scale_score_result(fluency, factor)
    grammar = _scale_score_result(grammar, factor)
    timing = _scale_score_result(timing, factor)
    tone = _scale_score_result(tone, factor)

    # ✅ IMPORTANT: store coverage under BOTH keys so either rubric method works
    method_scores = {
        "coverage_from_expected_points": coverage,
        "coverage_entailment_v1": coverage,
        "order_and_clarity_rules": order_rules,
        "audio_metrics": fluency,
        "grammar_metrics": grammar,
        "elapsed_sec_vs_recommended": timing,
        "tone_rules": tone,
    }

    implemented = set(method_scores.keys())
    rubric_methods = []
    weight_sum = 0.0
    missing_methods = []

    for d in dims:
        m = d.get("method")
        w = float(d.get("weight", 0.0) or 0.0)
        if not m or w <= 0:
            continue
        rubric_methods.append(m)
        weight_sum += w
        m_used = _resolve_method(m)
        if m_used not in implemented:
            missing_methods.append(m)

    rubric_debug = {
        "weight_sum": round(weight_sum, 3),
        "dimensions_count": len(dims),
        "methods_in_rubric": rubric_methods,
        "missing_methods": sorted(set(missing_methods)),
        "score_scale": scale,
        "preprocess": {"hard_max_sec": HARD_MAX_SEC, "asr_wav_path": wav_path},
        "segmenter_used": segmenter_used,
        "safety_removed": True,
        "skipped_safety_dimensions": int(skipped_safety),
    }

    total_w = 0.0
    weighted = 0.0
    breakdown = []

    for d in dims:
        method = d.get("method")
        if not method:
            continue
        w = float(d.get("weight", 0.0) or 0.0)
        if w <= 0:
            continue

        method_used = _resolve_method(method)
        result = method_scores.get(method_used) or {"score": 0.0, "evidence": [f"Method not implemented: {method}"]}

        total_w += w
        weighted += w * float(result.get("score", 0.0))

        dim_id = d.get("id") or method
        label = d.get("label") or method

        breakdown.append({
            "dimension_id": dim_id,
            "label": label,
            "method": method,
            "method_used": method_used,
            "weight": w,
            "score": result.get("score", 0.0),
            "score_raw_100": result.get("score_raw_100", None),
            "evidence": result.get("evidence", []),
        })

    final_score_uncapped = round((weighted / total_w) if total_w > 0 else 0.0, 2)

    off_topic = _is_off_topic_result(coverage) or _is_off_topic_result(order_rules)
    off_topic_cap = round(float(scale) * float(OFFTOPIC_FINAL_CAP_RATIO), 2)

    final_score = final_score_uncapped
    final_score_capped = False
    if off_topic and final_score > off_topic_cap:
        final_score = off_topic_cap
        final_score_capped = True

    rubric_debug["off_topic_policy"] = {
        "off_topic": bool(off_topic),
        "final_cap_ratio": float(OFFTOPIC_FINAL_CAP_RATIO),
        "final_cap_value": float(off_topic_cap),
        "final_score_uncapped": float(final_score_uncapped),
        "final_score_capped": bool(final_score_capped),
    }

    return {
        "scenario_id": scenario_id,
        "audio_path": path,
        "asr_wav_path": wav_path,
        "transcript_text": raw_text,
        "transcript_text_clean": clean_text,
        "elapsed_sec": asr_out.get("elapsed_sec", 0.0),
        "score_scale": scale,
        "rubric_debug": rubric_debug,
        "scores": {
            "coverage": coverage,
            "order_and_clarity_rules": order_rules,
            "fluency": fluency,
            "grammar": grammar,
            "timing": timing,
            "tone": tone,
        },
        "breakdown": breakdown,
        "final_score": final_score,
        "final_score_uncapped": final_score_uncapped,
        "timing_debug": {
            "save_sec": round(t_save - t0, 3),
            "asr_sec": round(t_asr - t_save, 3),
            "coverage_sec": round(t_cov - t_asr, 3),
            "order_sec": round(t_ord - t_cov, 3),
            "fluency_sec": round(t_flu - t_ord, 3),
            "grammar_sec": round(t_gram - t_flu, 3),
            "timing_sec": round(t_tim - t_gram, 3),
            "total_sec": round(t_tim - t0, 3),
        },
    }
