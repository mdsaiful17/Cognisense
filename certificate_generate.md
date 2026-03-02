# Certificate Generation — Cognisense

## Purpose
Certificates provide **proof of progress** and motivate users by turning practice into a visible achievement.

A Cognisense certificate should be:
- downloadable (PDF)
- verifiable (certificate ID / UUID)
- meaningful (shows score breakdown and completion evidence)

---

## Certificate types (recommended)
1) **Skill Certificate**
   - Awarded after completing requirements for a specific skill card
   - Example: “Job Interview Basics Certificate”

2) **Platform Certificate**
   - Awarded after completing a broader set (e.g., all skills or a milestone)

---

## Completion rules (recommended defaults)
You can keep it simple for MVP:

### Skill certificate rule (simple)
- User completes all 6 scenarios for that skill
- Minimum score threshold for eligibility:
  - e.g., final_score >= 6.0/10 average (or >= 60/100)

### Skill certificate rule (better)
- Must complete all 6 scenarios
- Must meet:
  - average_score >= threshold
  - “safety/tone” dimension not failing (if applicable)
  - confidence meter not “low” on most attempts

---

## What the certificate should include
- User name
- Skill name (or certificate title)
- Issue date
- Certificate ID (UUID)
- Score summary:
  - overall score
  - dimension breakdown (content, structure, tone, delivery, actionability)
- Optional: top strengths + improvement note
- Verification link or code (for later)

---

## Minimal data model (recommended)
- certificates:
  - id
  - user_id
  - certificate_type (skill/platform)
  - skill_code (nullable)
  - final_score
  - issued_at
  - certificate_uuid
  - verification_status
  - metadata_json (dimension scores, notes)

---

## Assistant behavior (RAG notes)
When asked:
- “How do I get a certificate?” → explain the rule (finish scenarios + score threshold) + where to download.
- “Is the certificate verifiable?” → yes, via certificate UUID/ID (or planned verification page).
- “What does it include?” → name, skill, score breakdown, date, ID.
