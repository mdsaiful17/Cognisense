# Insight Streams — Learning Videos per Skill

## Purpose
Insight Streams is the learning module that provides **curated video learning** to help users improve before/after practice in Skill Hub.

---

## Structure
- For each skill card, the platform provides: **10 videos**
- Videos are designed to:
  - teach core concepts
  - show examples of good responses
  - explain structures (STAR, SBI, Standup format)
  - give practical tips and common mistakes

---

## Recommended organization (per skill)
A clean 10-video layout that works for every skill:

1) Skill overview + what “good” looks like  
2) Structure framework (e.g., STAR/SBI/format rules)  
3) Vocabulary + tone patterns  
4) Common mistakes + how to avoid them  
5) Example response (good)  
6) Example response (average) + improvements  
7) Scenario-specific tips (scenario 1–2)  
8) Scenario-specific tips (scenario 3–4)  
9) Scenario-specific tips (scenario 5–6)  
10) Final checklist + practice plan  

---

## Integration with Skill Hub
Best practice flow:
1) User selects skill
2) User watches a short set of videos (optional)
3) User attempts scenario
4) Feedback suggests specific videos:
   - “You missed structure → watch Video 2”
   - “Tone improvement → watch Video 3”
   - “Scenario-specific guidance → watch Video 7/8/9”

---

## Metadata to store per video (recommended)
- skill_code (e.g., RL_CARD_INTERVIEW_01)
- video_index (1..10)
- title
- duration
- tags (e.g., STAR, empathy, de-escalation)
- URL/path
- recommended_for (weak dimensions: structure/tone/delivery/etc.)

---

## Assistant behavior (RAG notes)
When asked:
- “How many videos per skill?” → answer: 10.
- “Where do I find interview videos?” → Insight Streams → Interview skill card.
- “I scored low in structure—what to watch?” → recommend the framework/structure video + examples.

Keep answers short and actionable (1–2 steps + where to click).
