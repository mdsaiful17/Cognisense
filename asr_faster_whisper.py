# asr_faster_whisper.py
import os
from faster_whisper import WhisperModel

_MODEL = None

def get_whisper():
    global _MODEL
    if _MODEL is None:
        name = os.getenv("COGNISENSE_WHISPER_MODEL", "medium")  # ✅ speed default
        device = os.getenv("COGNISENSE_WHISPER_DEVICE", "cpu")
        compute = os.getenv("COGNISENSE_WHISPER_COMPUTE", "int8")

        cpu_threads = int(os.getenv("COGNISENSE_WHISPER_THREADS", str(os.cpu_count() or 4)))
        # num_workers > 1 can help throughput; keep 1 for stability on Windows single-node MVP
        _MODEL = WhisperModel(
            name,
            device=device,
            compute_type=compute,
            cpu_threads=cpu_threads,
            num_workers=1,
        )
    return _MODEL

def transcribe_for_rubric(audio_path: str) -> dict:
    model = get_whisper()

    segments, info = model.transcribe(
        audio_path,
        beam_size=1,
        vad_filter=True,
        condition_on_previous_text=False,  # ✅ reduces repetition loops
    )

    segs = [{"start": s.start, "end": s.end, "text": (s.text or "").strip()} for s in segments]
    transcript_text = " ".join(s["text"] for s in segs).strip()
    elapsed_sec = float(getattr(info, "duration", segs[-1]["end"] if segs else 0.0))

    return {"transcript_text": transcript_text, "segments": segs, "elapsed_sec": elapsed_sec}
