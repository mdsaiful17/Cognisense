# CV Builder — Cognisense

## Purpose
CV Builder converts a user’s profile + achievements into a clean CV-ready output. It helps users turn Skill Hub progress into real-world career value.

---

## Inputs (what CV Builder uses)
- Basic profile:
  - full name, email, phone, location
  - summary/objective
- Education
- Projects
- Experience (optional)
- Skills:
  - selected skill tags (communication, leadership, etc.)
  - Cognisense skill cards completed (optional)
- Certificates:
  - list of issued certificates + scores (optional)
- Links:
  - GitHub, LinkedIn, Portfolio

---

## Outputs
- One-page CV template (PDF export recommended)
- Multiple templates later (minimal, modern, ATS-friendly)

---

## Recommended CV sections
1) Header (name + contact)
2) Summary
3) Skills (grouped)
4) Experience (if any)
5) Projects
6) Education
7) Certifications (Cognisense certificates + others)

---

## “Skill Hub → CV” mapping (recommended)
Convert performance into CV-friendly bullet points like:
- “Completed Cognisense Interview Practice (6 scenarios) with average score 7.5/10”
- “Trained in conflict resolution and negotiation via scenario-based simulations”
- “Improved delivery metrics (reduced filler usage, improved pacing) over time”

Keep it honest:
- Don’t claim “IELTS certified” unless it’s a real cert
- Don’t claim “industry certification” unless verified

---

## Minimal data model (recommended)
- cv_profiles:
  - user_id
  - headline
  - summary
  - experience_json
  - education_json
  - projects_json
  - skills_json
  - links_json
  - template_id
  - updated_at

---

## Assistant behavior (RAG notes)
When asked:
- “How do I make my CV?” → CV Builder → fill profile → choose template → export.
- “Can I add Cognisense skills?” → yes; use certificates + completed skills as measurable proof.
- “What should I write in summary?” → 2–3 line professional summary + target role + strengths.
