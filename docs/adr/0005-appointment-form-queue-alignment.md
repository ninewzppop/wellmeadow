# ADR-0005: Appointment form & index aligned with the room-queue flow

- **Date**: 2026-08-26
- **Status**: Accepted

## Context

After ADR-0004 the queue lifecycle lives in `Appointment.status`, but the
appointments pages still predate it: the form forces manual `Appt_No`
entry, its status dropdown offers only the legacy vocabulary
(scheduled/completed/cancelled/no-show — not even the DB default
`waiting list`), and nothing links the appointments list back to a room's
queue where visits are actually worked. Requirement: make New/Edit
Appointment effortless (nothing typed by hand, everything selectable) and
make the appointments list speak the same status language as the rooms
page. Constraint: no schema changes. The `/medications` page stays a
placeholder — dispensing happens in the room queue.

## Decision

1. **`Appt_No` is auto-generated** on create (`A` prefix + zero-padded
   increment of the highest existing suffix, collision-safe within
   `varchar(10)`). The create form has no ID field; edit shows it
   read-only. The generator moves to the base `Controller` as a protected
   helper shared with `RoomController` (Medications/InPatient IDs).
2. **Form status options are exactly**: `waiting list`, `scheduled`,
   `cancelled`, `no-show`. `in consultation` and the three `completed-*`
   states can only be produced by queue actions (ADR-0004 lifecycle) and
   are never hand-selectable.
3. **Queue-context creation**: the room queue gains an "+ Add to queue"
   button linking to the appointment form with `?room=` and `?date=`;
   those pre-fill Room and Appointment Date. Hidden context fields carry
   them through validation failures, and after save the controller
   redirects back to that room's day queue instead of the appointments
   list. Without context, behaviour is unchanged.
4. **Appointments index speaks queue language**: badges use
   `Appointment::statusLabel()` with the same colour mapping as the rooms
   page (shared `x-status-badge` Blade component), the status filter offers
   every status from `statusLabels()`, and active rows get an "Open queue"
   button jumping to that appointment's room/day queue.
5. No changes to routes structure, schema, or the medications placeholder.

## Rationale

- Manual primary-key entry was the only "typing" left in the form; every
  other field is already a select/picker. Removing it satisfies "nothing
  typed by hand" without new UI patterns.
- Restricting selectable statuses keeps the state machine honest: the DB
  never receives `completed-*` values that were never earned by a visit.
- One badge component means label/colour drift between the rooms page and
  the appointments page cannot happen again.

## Consequences

- Existing seeded/manual rows keep any legacy status value; unknown values
  still render via the component's slate fallback.
- The base `Controller` now carries a small DB-backed helper — acceptable
  coupling for this app size.
