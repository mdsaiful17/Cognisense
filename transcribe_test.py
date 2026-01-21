from faster_whisper import WhisperModel

def main():
    model = WhisperModel("large-v3", device="cpu", compute_type="int8")

    segments, info = model.transcribe(
        "sample.wav",
        beam_size=1,
        vad_filter=True
    )

    segs = [{"start": s.start, "end": s.end, "text": s.text.strip()} for s in segments]
    transcript_text = " ".join(s["text"] for s in segs).strip()
    elapsed_sec = float(getattr(info, "duration", segs[-1]["end"] if segs else 0.0))

    print("Language:", info.language)
    print("Duration:", elapsed_sec)
    print("Transcript:\n", transcript_text)
    print("\nFirst 5 segments:", segs[:5])

if __name__ == "__main__":
    main()
