# Cognisense — Platform Overview

## What Cognisense is
Cognisense is a web-based learning + skill-building platform focused on **real-life professional skills** and **IELTS mock preparation**. It combines:
- **Skill Hub** (scenario-based real-life practice)
- **Insight Streams** (video learning per skill)
- **Certificates** (proof of progress/achievement)
- **CV Builder** (convert profile + skills into CV-ready format)
- **AspireIELTS** (full IELTS mock testing system)

Cognisense is designed to be **one platform** where a user can:
1) learn (videos),
2) practice (scenarios + tasks),
3) get feedback + track progress,
4) generate proof (certificate + CV),
5) prepare for IELTS (AspireIELTS).

---

## Core modules (current scope)
- **Skill Hub**
  - 13 real-life skill cards
  - each skill card contains **6 scenarios**
  - user practices by responding (text / voice / optional video in future)
- **Insight Streams**
  - each skill provides **10 videos**
  - video learning supports the scenario practice
- **Certificate Generate**
  - downloadable certificate after meeting completion rules (per skill or overall)
- **CV Builder**
  - user profile + skills → exportable CV
- **AspireIELTS**
  - Listening, Reading, Writing, Speaking (mock test system)
  - AI evaluation for writing + live speaking via Jitsi
  - dashboards, tips, lecture videos, chatbot

---

## Typical user journey
1. User logs in.
2. On Dashboard, user can:
   - open Skill Hub → pick a skill → pick a scenario → attempt → feedback saved
   - open Insight Streams → watch 10 videos for the selected skill
   - open Certificate → generate/download after meeting rules
   - open CV Builder → build CV from profile + skills
   - open AspireIELTS → take a full IELTS mock or module practice

---

## Data concepts (canonical terms)
- **Skill Card**: a real-life skill category (e.g., Interview, Standup, Negotiation).
- **Scenario**: a practice situation inside a skill card (6 per skill).
- **Attempt**: a user’s submission for a scenario (text/audio/metrics/scores).
- **Rubric**: scoring criteria (expected points + dimension weights).
- **Insight Streams**: curated video set (10 videos per skill).

---

## Assistant behavior (for chatbot / RAG)
When the user asks:
- “What is Cognisense?” → explain in 2–4 sentences + list modules.
- “Where do I practice skills?” → route to Skill Hub + explain scenarios.
- “Where are videos?” → route to Insight Streams (10 videos per skill).
- “How do certificates work?” → explain completion rules + download.
- “How does AspireIELTS work?” → summarize modules + AI writing + live speaking.
- “How does scoring work?” → explain rubric-first + (Whisper/SBERT/LLM/audio) pipeline.

**Never promise “100% perfect scoring”.** Say: rubric-based + calibrated + explainable.

---

## Known constraints / honesty rules
- Soft-skill evaluation can be noisy (accent, mic quality, multiple valid answers).
- AI signals should be used as *evidence* inside a rubric, not “magic judgement”.
- Emotion detection (if added) should be low-weight and used mainly for feedback.

---

## Quick glossary (short)
- **SBERT**: semantic similarity embeddings to check “did they cover the expected points?”
- **Whisper**: speech-to-text for voice attempts.
- **Fusion**: weighted combining of scores into a final score.
