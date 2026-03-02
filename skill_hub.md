# Skill Hub — Real-Life Skills Practice

## Purpose
Skill Hub is where users practice **real-world professional scenarios** (soft skills + workplace communication). Users choose a skill card and attempt scenario prompts; the system stores attempts, scores, and improvement feedback.

---

## Structure
- Total skill cards: **13**
- Scenarios per skill card: **6**
- Each scenario has:
  - a prompt + instructions
  - “expected points” (what a good answer should include)
  - a rubric (dimensions + weights)
  - optional safety rules (e.g., do not request passwords)

---

## Skill Cards (from current DB)
These are the skill cards visible in your current table:

1) **RL_CARD_INTERVIEW_01 — Job Interview Basics**
   - Category: Communication | Difficulty: Beginner

2) **RL_CARD_CUSTOMER_01 — Handling Customer Complaints**
   - Category: Customer Service | Difficulty: Intermediate

3) **RL_CARD_STANDUP_01 — Daily Team Standup**
   - Category: Teamwork | Difficulty: Beginner

4) **RL_CARD_CONFLICT_01 — Conflict Resolution with Colleague**
   - Category: Leadership | Difficulty: Intermediate

5) **RL_CARD_SALES_01 — Elevator Sales Pitch**
   - Category: Persuasion | Difficulty: Advanced

6) **RL_CARD_PRESENT_01 — Project Presentation to Manager**
   - Category: Communication | Difficulty: Intermediate

7) **RL_CARD_EMAIL_01 — Professional Email Writing**
   - Category: Communication | Difficulty: Beginner
   - NOTE: Typically text-only evaluation

8) **RL_CARD_MEETING_01 — Leading a Team Meeting**
   - Category: Leadership | Difficulty: Intermediate

9) **RL_CARD_FEEDBACK_01 — Giving Constructive Feedback**
   - Category: Leadership | Difficulty: Intermediate

10) **RL_CARD_NEGOTIATE_01 — Basic Client Negotiation**
   - Category: Persuasion | Difficulty: Intermediate

11) **RL_CARD_SUPPORT_01 — Technical Support Call**
   - Category: Customer Service | Difficulty: Intermediate

12) **RL_CARD_NETWORK_01 — Networking at a Professional Event**
   - Category: Networking | Difficulty: Beginner

13) **RL_CARD_PUBLIC_01 — Public Speaking Basics**
   - Category: Communication | Difficulty: Intermediate

---

## User Flow
1. User opens Skill Hub
2. Selects a skill card
3. Selects one of 6 scenarios
4. Submits response (text or voice; optional video later)
5. System saves attempt + returns:
   - final score (0–10 or 0–100)
   - dimension breakdown
   - “covered vs missed” expected points
   - 2–3 improvement tips + 1 micro-exercise

---

## Scoring design (rubric-first)
Skill Hub scoring should be explainable and consistent:

### What to store per scenario (recommended)
Store a rubric JSON per scenario:
- expected_points: [ ... ]
- scoring_dimensions: (e.g., content, structure, tone, actionability, delivery, safety)
- weights: { content: 0.35, structure: 0.25, ... }
- structure_type (if required): STAR / SBI / Standup-format / etc.
- safety_rules (optional): forbidden info requests, privacy constraints

### Pipeline signals (models as evidence)
- Speech-to-text (if voice): Whisper → transcript + timestamps
- Content coverage: SBERT matches transcript sentences against expected_points
- Structure/tone: LLM rubric judge (recommended) OR rules
- Delivery: audio metrics (WPM, pauses, fillers, pitch variance)
- Fusion: weighted score per skill card + confidence meter

### Output stored per attempt (recommended)
attempt.scores_json:
- covered_points, missed_points
- dimension_scores
- audio_metrics (if voice)
- final_score
- confidence_level

---

## Assistant behavior (RAG notes)
When a user asks:
- “What skills are available?” → list 13 skill cards + short descriptions.
- “How many scenarios per skill?” → answer: 6.
- “How does Skill Hub scoring work?” → rubric-first; SBERT coverage; optional Whisper/audio metrics; explainable feedback.
- “Which skill is best for interviews?” → point to RL_CARD_INTERVIEW_01 + mention STAR structure for some scenarios.

Never claim “perfect judgement”; always say “rubric-based + explainable + improves with calibration”.
