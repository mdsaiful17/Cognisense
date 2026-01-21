# emotion_video_mediapipe.py
from __future__ import annotations

from typing import Dict, Any, Tuple
import math
import numpy as np
import cv2
import mediapipe as mp

mp_face_mesh = mp.solutions.face_mesh


def _dist(a, b) -> float:
    return float(math.hypot(a[0] - b[0], a[1] - b[1]))


def _sigmoid(x: float) -> float:
    return 1.0 / (1.0 + math.exp(-x))


def _clamp01(x: float) -> float:
    return max(0.0, min(1.0, x))


def _landmark_xy(face_landmarks, idx: int) -> Tuple[float, float]:
    lm = face_landmarks.landmark[idx]
    return float(lm.x), float(lm.y)


def predict_emotion_video(video_path: str, sample_fps: int = 5) -> Dict[str, Any]:
    """
    Returns a lightweight emotion estimate from face landmarks only.
    Privacy: processes frames in-memory; returns only aggregated features & probs.

    Adjustments:
    1) OpenCV video open fallback using CAP_FFMPEG
    2) MediaPipe FaceMesh is created per-call (context-managed)
    3) IMPORTANT FIX: 'confidence' now reflects *emotion estimate certainty*:
       confidence = max_prob * face_evidence
       (face_evidence derived from face_detect_rate)
    """
    # --- Open video with fallback backend ---
    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        cap = cv2.VideoCapture(video_path, cv2.CAP_FFMPEG)

    if not cap.isOpened():
        return {"ok": False, "error": "Cannot open video", "probs": {}, "details": {}}

    fps = cap.get(cv2.CAP_PROP_FPS)
    if not fps or fps <= 0:
        fps = 25.0

    sample_fps = max(1, int(sample_fps))
    step = max(1, int(round(float(fps) / float(sample_fps))))

    total_frames = 0
    used_frames = 0
    face_frames = 0

    feats = []
    nose_track = []

    try:
        with mp_face_mesh.FaceMesh(
            static_image_mode=False,
            max_num_faces=1,
            refine_landmarks=True,
            min_detection_confidence=0.5,
            min_tracking_confidence=0.5,
        ) as face_mesh:
            while True:
                ok, frame = cap.read()
                if not ok:
                    break

                total_frames += 1
                if (total_frames % step) != 0:
                    continue

                used_frames += 1

                h, w = frame.shape[:2]
                if w > 960:
                    new_w = 960
                    new_h = int(h * (new_w / float(w)))
                    frame = cv2.resize(frame, (new_w, new_h), interpolation=cv2.INTER_AREA)

                rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
                res = face_mesh.process(rgb)

                if not res.multi_face_landmarks:
                    continue

                face_frames += 1
                face = res.multi_face_landmarks[0]

                # face width using outer eye corners
                p33 = _landmark_xy(face, 33)
                p263 = _landmark_xy(face, 263)
                face_w = _dist(p33, p263)
                if face_w <= 1e-6:
                    continue

                # mouth
                p13 = _landmark_xy(face, 13)
                p14 = _landmark_xy(face, 14)
                p61 = _landmark_xy(face, 61)
                p291 = _landmark_xy(face, 291)

                # eyes
                p159 = _landmark_xy(face, 159)
                p145 = _landmark_xy(face, 145)
                p386 = _landmark_xy(face, 386)
                p374 = _landmark_xy(face, 374)

                # nose tip-ish (track movement)
                p1 = _landmark_xy(face, 1)
                nose_track.append(p1)

                mouth_open = _dist(p13, p14) / face_w
                mouth_wide = _dist(p61, p291) / face_w

                # y grows downward; corners higher than upper lip => more "smile-up"
                smile_up = ((p13[1] - p61[1]) + (p13[1] - p291[1])) / 2.0
                smile_up = (smile_up / face_w)

                left_eye_open = _dist(p159, p145) / face_w
                right_eye_open = _dist(p386, p374) / face_w
                eye_open = (left_eye_open + right_eye_open) / 2.0

                feats.append([mouth_open, mouth_wide, smile_up, eye_open])

    finally:
        cap.release()

    if used_frames == 0:
        return {"ok": False, "error": "No frames processed", "probs": {}, "details": {}}

    face_rate = face_frames / float(used_frames)

    if face_frames < 3:
        return {
            "ok": True,
            "top": "neutral",
            "confidence": 0.05,
            "face_detect_rate": round(face_rate, 3),
            "probs": {"neutral": 1.0},
            "details": {"reason": "face_not_detected_enough"},
        }

    X = np.array(feats, dtype=np.float32)
    m = X.mean(axis=0)
    s = X.std(axis=0)

    mouth_open, mouth_wide, smile_up, eye_open = m.tolist()

    # Head motion estimate
    motion = 0.0
    if len(nose_track) >= 3:
        diffs = [_dist(nose_track[i], nose_track[i - 1]) for i in range(1, len(nose_track))]
        motion = float(np.mean(diffs))

    # Convert features -> valence/arousal (heuristic MVP)
    valence = _sigmoid(8.0 * smile_up + 3.0 * (mouth_wide - 0.35) - 2.0 * (mouth_open - 0.06))
    arousal = _clamp01(
        0.55 * _sigmoid(12.0 * (eye_open - 0.02))
        + 0.25 * _sigmoid(10.0 * (mouth_open - 0.05))
        + 0.20 * _clamp01(motion * 30.0)
    )

    # Map valence/arousal -> simple 4-class distribution
    happy = _clamp01(valence * (0.4 + 0.6 * arousal))
    angry = _clamp01((1.0 - valence) * (0.75 * arousal))
    sad = _clamp01((1.0 - valence) * (1.0 - arousal))
    neutral = _clamp01(1.0 - max(happy, angry, sad))

    probs = {"happy": float(happy), "angry": float(angry), "sad": float(sad), "neutral": float(neutral)}
    z = sum(probs.values()) + 1e-9
    probs = {k: v / z for k, v in probs.items()}

    top = max(probs.items(), key=lambda kv: kv[1])[0]
    model_conf = float(max(probs.values())) if probs else 0.0

    # Face evidence (availability), separate from emotion certainty
    face_evidence = _clamp01(0.2 + 0.8 * float(face_rate))

    # FINAL confidence = emotion certainty * face evidence
    confidence = _clamp01(model_conf * face_evidence)

    return {
        "ok": True,
        "top": top,
        "confidence": round(float(confidence), 3),
        "face_detect_rate": round(float(face_rate), 3),
        "probs": probs,
        "details": {
            "video_meta": {
                "fps_reported": float(fps),
                "sample_fps": int(sample_fps),
                "step": int(step),
                "total_frames": int(total_frames),
                "used_frames": int(used_frames),
                "face_frames": int(face_frames),
            },
            "confidence_components": {
                "model_conf": round(float(model_conf), 3),
                "face_evidence": round(float(face_evidence), 3),
            },
            "feature_mean": {
                "mouth_open": float(mouth_open),
                "mouth_wide": float(mouth_wide),
                "smile_up": float(smile_up),
                "eye_open": float(eye_open),
                "head_motion": float(motion),
            },
            "feature_std": {
                "mouth_open": float(s[0]),
                "mouth_wide": float(s[1]),
                "smile_up": float(s[2]),
                "eye_open": float(s[3]),
            },
            "valence": float(valence),
            "arousal": float(arousal),
        },
    }
