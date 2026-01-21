# emotion_audio_speechbrain.py
from __future__ import annotations

from typing import Any, Dict, List, Optional, Tuple
import os
import uuid
import wave
import contextlib

import numpy as np
from speechbrain.inference.interfaces import foreign_class

MODEL_SOURCE = "speechbrain/emotion-recognition-wav2vec2-IEMOCAP"
PYMODULE_FILE = "custom_interface.py"
CLASSNAME = "CustomEncoderWav2vec2Classifier"

# Cache one classifier per device (cpu/cuda)
_CLASSIFIERS: Dict[str, Any] = {}


# ----------------------------
# Calibration helpers
# ----------------------------
def _safe_softmax(logits: np.ndarray) -> np.ndarray:
    logits = logits - np.max(logits)
    ex = np.exp(logits)
    s = np.sum(ex)
    return ex / s if s > 0 else np.ones_like(ex) / len(ex)


def _apply_temperature_to_probs(probs: List[float], T: float, eps: float = 1e-12) -> List[float]:
    """
    Convert probs -> logits via log, then soften with temperature.
    T > 1 softens (less peaky). T == 1 no change.
    """
    p = np.clip(np.array(probs, dtype=np.float64), eps, 1.0)
    logits = np.log(p) / float(max(T, 1e-6))
    return _safe_softmax(logits).astype(np.float64).tolist()


def _entropy(probs: List[float], eps: float = 1e-12) -> float:
    p = np.clip(np.array(probs, dtype=np.float64), eps, 1.0)
    return float(-(p * np.log(p)).sum())


# ----------------------------
# Core helpers
# ----------------------------
def _to_float(x) -> float:
    try:
        return float(x.item())  # torch scalar
    except Exception:
        return float(x)


def _get_classifier(device: str = "cpu"):
    device = (device or "cpu").lower()

    if device not in _CLASSIFIERS:
        # run_opts belongs to the model loader, not classify_file()
        clf = foreign_class(
            source=MODEL_SOURCE,
            pymodule_file=PYMODULE_FILE,
            classname=CLASSNAME,
            run_opts={"device": device},
        )
        _CLASSIFIERS[device] = clf

    return _CLASSIFIERS[device]


def _get_label_encoder(clf) -> Optional[Any]:
    # speechbrain pretrained models usually store it here
    if hasattr(clf, "hparams") and hasattr(clf.hparams, "label_encoder"):
        return clf.hparams.label_encoder
    if hasattr(clf, "label_encoder"):
        return clf.label_encoder
    return None


def _labels_from_encoder(le: Any, n: int) -> Optional[List[str]]:
    """
    Attempt to extract full ordered label list (index -> label).
    """
    # Most common: le.lab2ind is dict label -> index
    if hasattr(le, "lab2ind") and isinstance(le.lab2ind, dict) and le.lab2ind:
        items = sorted(le.lab2ind.items(), key=lambda kv: int(kv[1]))
        return [str(k) for k, _ in items]

    # Sometimes: le.ind2lab is dict index -> label
    if hasattr(le, "ind2lab") and isinstance(le.ind2lab, dict) and le.ind2lab:
        items = sorted(le.ind2lab.items(), key=lambda kv: int(kv[0]))
        return [str(v) for _, v in items]

    # Fallback if encoder can decode indices
    if hasattr(le, "decode_ndim"):
        try:
            import torch

            idx = torch.arange(n)
            labs = le.decode_ndim(idx)
            # labs might be list[str] or list[list[str]]
            if isinstance(labs, list) and labs and isinstance(labs[0], list):
                labs = [x[0] if x else "" for x in labs]
            return [str(x) for x in labs]
        except Exception:
            return None

    return None


def _wav_duration_sec(wav_path: str) -> float:
    """
    Duration using wave module (best with PCM WAV, e.g., ffmpeg output).
    """
    try:
        with contextlib.closing(wave.open(wav_path, "rb")) as wf:
            frames = wf.getnframes()
            rate = wf.getframerate()
            if rate <= 0:
                return 0.0
            return float(frames) / float(rate)
    except Exception:
        return 0.0


def _split_wav_to_chunks(
    wav_path: str,
    out_dir: str,
    chunk_sec: float = 3.0,
    hop_sec: float = 2.5,
    min_last_sec: float = 2.0,
) -> List[str]:
    """
    Splits wav into overlapping chunks (chunk_sec window, hop_sec stride).
    Uses Python wave module (works best with PCM WAV, like ffmpeg output).
    """
    os.makedirs(out_dir, exist_ok=True)
    out_paths: List[str] = []

    try:
        with contextlib.closing(wave.open(wav_path, "rb")) as wf:
            nch = wf.getnchannels()
            sw = wf.getsampwidth()
            fr = wf.getframerate()
            nframes = wf.getnframes()

            if fr <= 0 or nframes <= 0:
                return []

            chunk_frames = int(chunk_sec * fr)
            hop_frames = max(1, int(hop_sec * fr))

            start = 0
            while start < nframes:
                end = start + chunk_frames
                if end > nframes:
                    last_len_sec = (nframes - start) / float(fr)
                    if last_len_sec < min_last_sec:
                        break
                    end = nframes

                wf.setpos(start)
                frames = wf.readframes(end - start)

                out_path = os.path.join(out_dir, f"emo_{uuid.uuid4().hex}.wav")
                with contextlib.closing(wave.open(out_path, "wb")) as out:
                    out.setnchannels(nch)
                    out.setsampwidth(sw)
                    out.setframerate(fr)
                    out.writeframes(frames)

                out_paths.append(out_path)
                start += hop_frames

    except Exception:
        return []

    return out_paths


def _ensure_labels(clf, n: int) -> List[str]:
    le = _get_label_encoder(clf)
    labels = _labels_from_encoder(le, n) if le else None

    if not labels or len(labels) < n:
        labels = labels or []
        while len(labels) < n:
            labels.append(f"class_{len(labels)}")

    return labels[:n]


def _infer_probs_single(clf, wav_path: str) -> Tuple[List[float], float, int, str, float]:
    """
    Runs SpeechBrain classify_file and returns:
      probs_list, max_prob_conf, top_index, top_text_label, raw_score_float
    """
    out_prob, score, index, text_lab = clf.classify_file(wav_path)

    raw_score = _to_float(score)

    probs_vec = out_prob.squeeze()
    try:
        probs_vec = probs_vec.detach().cpu()
    except Exception:
        pass
    probs_list = probs_vec.tolist()

    try:
        top_i = int(index.item())
    except Exception:
        top_i = int(index)

    max_prob = float(max(probs_list)) if probs_list else 0.0

    top_text = ""
    if isinstance(text_lab, (list, tuple)) and text_lab:
        top_text = str(text_lab[0]).strip()
    elif isinstance(text_lab, str):
        top_text = text_lab.strip()

    return probs_list, max_prob, top_i, top_text, raw_score


def _calibrate_probs_if_needed(
    raw_probs: List[float],
    auto_calibrate: bool,
    overconf_thresh: float,
    auto_temperature: float,
) -> Tuple[List[float], float, Dict[str, Any]]:
    """
    Returns (used_probs, used_conf, calibration_details)
    where used_conf is max(used_probs).
    """
    raw_conf = float(max(raw_probs)) if raw_probs else 0.0

    T_used = 1.0
    used_probs = raw_probs

    if auto_calibrate and raw_conf >= float(overconf_thresh):
        T_used = float(auto_temperature)
        used_probs = _apply_temperature_to_probs(raw_probs, T_used)

    used_conf = float(max(used_probs)) if used_probs else 0.0

    calib = {
        "auto_calibrate": bool(auto_calibrate),
        "overconf_thresh": float(overconf_thresh),
        "temperature_used": float(T_used),
        "confidence_raw": float(raw_conf),
        "confidence_used": float(used_conf),
        "entropy_raw": float(_entropy(raw_probs)) if raw_probs else 0.0,
        "entropy_used": float(_entropy(used_probs)) if used_probs else 0.0,
    }
    return used_probs, used_conf, calib


def predict_emotion_audio(
    wav_path: str,
    device: str = "cpu",
    enable_chunking: bool = True,
    chunk_sec: float = 3.0,
    hop_sec: float = 2.5,
    # Calibration/debug:
    auto_calibrate: bool = True,
    overconf_thresh: float = 0.999,
    auto_temperature: float = 8.0,
    debug_chunks: bool = False,
    debug_max_chunks: int = 6,
) -> dict:
    """
    Returns:
      {
        "model": "...",
        "top_label": "...",
        "confidence": 0..1,            # max(prob) after calibration
        "probs": {label: prob, ...},   # calibrated distribution
        "raw_probs": {label: prob, ...},# raw distribution (pre-calibration)
        "labels": [...],
        "details": {
            "duration_sec": ...,
            "raw_score": ...,
            "chunking": {...},
            "calibration": {...},
            "chunk_debug": [...]
        }
      }
    """
    clf = _get_classifier(device)

    dur = _wav_duration_sec(wav_path)
    details: Dict[str, Any] = {
        "duration_sec": round(float(dur), 3),
    }

    use_chunks = bool(enable_chunking and dur > (float(chunk_sec) + 0.5))

    # ----------------------------
    # No chunking path
    # ----------------------------
    if not use_chunks:
        probs_list, _, top_i, top_text, raw_score = _infer_probs_single(clf, wav_path)
        n = len(probs_list)
        labels = _ensure_labels(clf, n)

        used_probs_list, used_conf, calib = _calibrate_probs_if_needed(
            raw_probs=probs_list,
            auto_calibrate=auto_calibrate,
            overconf_thresh=overconf_thresh,
            auto_temperature=auto_temperature,
        )

        raw_probs = {labels[i]: float(probs_list[i]) for i in range(n)}
        probs = {labels[i]: float(used_probs_list[i]) for i in range(n)}

        top_label = max(probs.items(), key=lambda kv: kv[1])[0] if probs else None
        if not top_label:
            top_label = labels[top_i] if (0 <= top_i < len(labels)) else (top_text or None)

        details["raw_score"] = float(raw_score)
        details["chunking"] = {"enabled": False, "chunks": 1}
        details["calibration"] = calib

        return {
            "model": MODEL_SOURCE,
            "top_label": top_label,
            "confidence": float(used_conf),
            "probs": probs,
            "raw_probs": raw_probs,
            "labels": labels,
            "details": details,
        }

    # ----------------------------
    # Chunking path
    # ----------------------------
    tmp_dir = os.path.join(os.path.dirname(wav_path) or ".", "_emo_chunks")
    chunk_paths = _split_wav_to_chunks(
        wav_path=wav_path,
        out_dir=tmp_dir,
        chunk_sec=float(chunk_sec),
        hop_sec=float(hop_sec),
        min_last_sec=2.0,
    )

    details["chunking"] = {
        "enabled": True,
        "chunk_sec": float(chunk_sec),
        "hop_sec": float(hop_sec),
        "chunks": len(chunk_paths),
    }

    # If chunking failed, fall back to whole file
    if not chunk_paths:
        probs_list, _, top_i, top_text, raw_score = _infer_probs_single(clf, wav_path)
        n = len(probs_list)
        labels = _ensure_labels(clf, n)

        used_probs_list, used_conf, calib = _calibrate_probs_if_needed(
            raw_probs=probs_list,
            auto_calibrate=auto_calibrate,
            overconf_thresh=overconf_thresh,
            auto_temperature=auto_temperature,
        )

        raw_probs = {labels[i]: float(probs_list[i]) for i in range(n)}
        probs = {labels[i]: float(used_probs_list[i]) for i in range(n)}

        top_label = max(probs.items(), key=lambda kv: kv[1])[0] if probs else None
        if not top_label:
            top_label = labels[top_i] if (0 <= top_i < len(labels)) else (top_text or None)

        details["raw_score"] = float(raw_score)
        details["chunking"]["note"] = "No chunks created; used whole file."
        details["calibration"] = calib

        return {
            "model": MODEL_SOURCE,
            "top_label": top_label,
            "confidence": float(used_conf),
            "probs": probs,
            "raw_probs": raw_probs,
            "labels": labels,
            "details": details,
        }

    all_probs: List[List[float]] = []
    chunk_max_confs: List[float] = []
    chunk_raw_scores: List[float] = []
    chunk_debug: List[Dict[str, Any]] = []

    for i, cp in enumerate(chunk_paths):
        probs_list, max_conf, top_i, top_text, raw_score = _infer_probs_single(clf, cp)
        all_probs.append(probs_list)
        chunk_max_confs.append(float(max_conf))
        chunk_raw_scores.append(float(raw_score))

        if debug_chunks and len(chunk_debug) < int(debug_max_chunks):
            labels_local = _ensure_labels(clf, len(probs_list))
            top_lab = labels_local[top_i] if (0 <= top_i < len(labels_local)) else (top_text or None)
            chunk_debug.append(
                {
                    "i": int(i),
                    "top": top_lab,
                    "max_prob": float(max(probs_list) if probs_list else 0.0),
                }
            )

    # Cleanup chunks (best-effort)
    for cp in chunk_paths:
        try:
            os.remove(cp)
        except Exception:
            pass

    # Average probs across chunks
    P = np.array(all_probs, dtype=np.float32)
    avg = P.mean(axis=0).tolist()
    n = len(avg)

    labels = _ensure_labels(clf, n)

    used_avg, used_conf, calib = _calibrate_probs_if_needed(
        raw_probs=avg,
        auto_calibrate=auto_calibrate,
        overconf_thresh=overconf_thresh,
        auto_temperature=auto_temperature,
    )

    raw_probs = {labels[i]: float(avg[i]) for i in range(n)}
    probs = {labels[i]: float(used_avg[i]) for i in range(n)}

    top_label = max(probs.items(), key=lambda kv: kv[1])[0] if probs else None

    details["raw_score"] = round(float(np.mean(chunk_raw_scores)) if chunk_raw_scores else 0.0, 6)
    details["chunking"]["avg_chunk_conf"] = round(float(np.mean(chunk_max_confs)) if chunk_max_confs else 0.0, 3)
    details["calibration"] = calib
    if debug_chunks:
        details["chunk_debug"] = chunk_debug

    return {
        "model": MODEL_SOURCE,
        "top_label": top_label,
        "confidence": float(used_conf),
        "probs": probs,
        "raw_probs": raw_probs,
        "labels": labels,
        "details": details,
    }
