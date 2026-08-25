# ADR-0006: Queue date chosen once on the rooms board

- **Date**: 2026-08-26
- **Status**: Accepted

## Context

Since ADR-0004 each room's queue page carried its own date picker, so a
user browsing several rooms re-picked the same day repeatedly and the board
always showed today's counts regardless of what day was being worked with.

## Decision

1. **The date is selected once, on the rooms board** (`/rooms`): an
   auto-submitting date input (inline `onchange` vanilla JS, consistent
   with existing project patterns) plus a "Today" reset link. All room
   cards' waiting/done/total counts are computed for the selected date,
   defaulting to today; invalid dates fall back to today.
2. **Card links carry the context**: every room card opens that room's day
   queue with `?date=`, so the whole browsing session follows one chosen
   day.
3. **The queue page loses its date picker**: it displays the active date as
   plain text next to a "change date" link back to the board. The
   "+ Add to queue" button continues to use the displayed date.
4. No schema changes; this is read-model/presentation only.

## Rationale

- One picker removes duplicate controls and the ambiguity of two places
  that could disagree about "which day am I looking at".
- Auto-submit matches how little friction a single-field filter needs;
  an Apply button would be ceremony for one input.

## Consequences

- Deep links into a queue still work by URL (`/rooms/R001?date=...`) even
  though no picker exists there.
- Appointments-index "Open queue" links keep passing explicit dates and
  remain unaffected.
