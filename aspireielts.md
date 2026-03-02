# AspireIELTS — Module Inside Cognisense

## What AspireIELTS is
AspireIELTS is a full IELTS mock testing platform that simulates the real exam experience and provides feedback, scoring, and analytics.

It supports:
- Listening
- Reading
- Writing (AI-evaluated)
- Speaking (live speaking test via Jitsi with admin as examiner)

---

## Goals
- Realistic exam simulation: timers, real test structure, structured sets
- Intelligent feedback: writing evaluation + improvement tips
- Human interaction: live speaking session for authenticity
- Progress tracking: band scores + graphs over time
- Preparation resources: tips + lecture videos before practice
- Always-available help: GPT-powered chatbot

---

## Modules & evaluation
### Listening
- Timed audio-based practice
- MCQ/comprehension questions
- Auto-graded scoring

### Reading
- Timed reading passages + questions
- Auto-graded scoring

### Writing
- User submits Writing Task responses
- AI (GPT) returns:
  - estimated band score
  - detailed feedback aligned with IELTS criteria

### Speaking
- User initiates a speaking test
- System notifies admin/examiner
- Speaking conducted via **Jitsi video/audio**
- Admin evaluates the session (realistic exam feel)

---

## Learning flow (tips + lecture videos)
AspireIELTS includes:
- “tips screen” before each module/video section
- module-wise lecture videos for strategy building
- learners are guided: tips → videos → mock practice → feedback

---

## Dashboards & analytics
User dashboard:
- section-wise band scores
- overall band progression
- historical attempt tracking
- graphs over time

Admin panel:
- user list + search
- access to user responses and band scores
- writing feedback review
- speaking test monitoring + joining calls
- analytics + progress graphs

---

## Tech stack (as described in the SRS)
- Backend: PHP
- Database: MySQL
- Frontend: HTML/CSS/JS
- AI: OpenAI GPT API for writing + chatbot
- Speaking: Jitsi integration for live calls

---

## Assistant behavior (RAG notes)
If user asks:
- “How does writing scoring work?” → GPT evaluates writing and returns band + feedback.
- “How does speaking work?” → user requests test, admin joins via Jitsi and conducts it live.
- “Do you have all four modules?” → yes: Listening/Reading/Writing/Speaking.
- “Do you track progress?” → yes: dashboards + graphs show band progression.
