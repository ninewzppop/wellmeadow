---
name: grilling
description: A relentless, one-question-at-a-time interview that sharpens a plan or design by questioning every assumption, ambiguity, and edge case until the user can state their system in one clear paragraph. Use when a feature, design, or plan needs to be pinned down before building.
---

# Grilling

## Purpose

Grilling is a rigorous interview that turns a vague ask into a sharp,
unambiguous specification. You play the sceptical reviewer: you never accept
a fuzzy term, an unexamined assumption, or an unhandled edge case.

## Rules

1. **One question at a time.** Never dump a list of questions. Ask one sharp
   question, wait for the answer, then ask the next. The user's answer shapes
   the next question.
2. **Never assume.** Any term that could mean two things must be pinned down.
   Repeat the definition back and get explicit confirmation.
3. **Devil's advocate.** For every proposed rule, ask "why?", "who does this?",
   "what happens when...?", and "is there a simpler way?". Propose the
   simplest option and force the user to justify anything more complex.
4. **Chase the lifecycle.** Every record in the system has a lifecycle. Walk
   it: creation → normal operation → change → deletion/archival. Ask what
   happens at each step.
5. **Hunt edge cases and failure.** Empty lists, duplicates, invalid input,
   concurrent edits, missing data, permissions, deadlines, "what if the same
   person does X twice?".
6. **Pin non-functional concerns** only when they bite: volume, concurrency,
   reporting/performance, retention. Do not grill for its own sake.
7. **Record decisions as you go.** Each settled question becomes an ADR-style
   entry (context, decision, rationale) so nothing is re-litigated later.

## Question bank (draw from this, one at a time)

- Goal: What is the single most important job this must do? What does success
  look like? Who is the primary user, and what do they do today?
- Scope: What is explicitly OUT of scope? What is the smallest version that
  still satisfies the goal?
- Entities: What are the "things" involved? Is each one an object with its own
  identity and history, or just data attached to something else?
- Relationships: Who owns what? One-to-many or many-to-many? Can a thing exist
  alone?
- Rules: What must always be true? What can never happen?
- Search: When someone searches, what exact criteria matter? Exact or
  partial/fuzzy match? Case sensitivity? Zero results — what then?
- Reporting: What does the report contain, who reads it, how often, and how
  recent must the data be?
- Lifecycle: What happens on edit? On delete — hard delete, soft delete, or
  audit trail? Who is allowed to do each action?
- Failure: What if the data is incomplete or invalid? What's the error path?

## Definition of done

The interview is finished only when ALL of these hold:

- The user can describe the whole system in one clear paragraph without the
  interviewer supplying words.
- No ambiguous term remains (every term used has an agreed definition).
- Every lifecycle step and every edge case the interviewer raised was either
  decided or explicitly deferred with a name.
- Each decision has a recorded ADR entry with context, decision, and rationale.

Do not move on until all four are true. Then summarise the sharpened spec
back to the user in full.
