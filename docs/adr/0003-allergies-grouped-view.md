# ADR-0003: Allergies page — grouped-by-patient view with flat toggle

- **Date**: 2026-08-25
- **Status**: Accepted

## Context

`allergies.index` renders one row per `PatientAllergy` record (15/page). A
patient with several allergies repeats on many rows, so the overview is noisy
and the true per-patient allergy burden is hard to see. Requirement: a
grouped-by-patient default view plus a record-level view, without any schema
changes (read-model work only).

## Decision

- **Two view modes** selected by query parameter `view=grouped|flat`
  (default `grouped`; invalid values fall back to `grouped`). Rendered
  server-side in Blade — no JS framework (the project uses none).
  The filter form carries a hidden `view` input so filtering keeps the
  chosen mode; the toggle preserves all other query-string filters via
  `fullUrlWithQuery()`.
- **Grouped mode paginates by patient, not by record** (10 patients/page,
  manual `LengthAwarePaginator`). A patient's group is never split across
  pages. Groups are ordered patient name A–Z (`LastName`, `FirstName`);
  allergies inside a group keep `DiagDate desc`, `Allergy_No asc`.
  Records whose `Pt_No` is NULL form a "No Patient" group sorted last.
- **Badge counts reflect the filtered result**: under an active filter the
  badge shows how many matching records are displayed (e.g.
  Severity=Severe → "(1)"), not the patient's lifetime total — the number
  always matches visible rows.
- **Patient dropdown counts show true totals**, computed once via a grouped
  count query independent of search/severity filters, for every patient
  including those with zero (shown as `(0)`), because the dropdown is also a
  navigation filter to empty results.
- **Grouped table presentation**: patient name cell merged with `rowspan`,
  name + Pt_No + "(N allergies)" badge; alternating background per group
  (zebra by group); groups containing any Severe record get a red left-edge
  indicator and warning icon next to the patient name. Edit/delete actions
  stay available per row in both modes.
- **Flat mode unchanged**: existing behaviour (15 records/page, current
  ordering and actions) preserved as-is.
- **Severity colours fixed to spec**: Mild = green, Moderate = amber,
  Severe = red (Mild was sky-blue before).
- **Responsive**: below `md` the grouped table collapses into one card per
  patient listing their allergies; flat mode keeps the horizontally
  scrollable table.

## Rationale

- Grouping is a read-model/presentation concern: no new tables, columns or
  migrations — the constraint forbids schema changes outright.
- Per-record pagination would split merged rowspan groups across pages;
  per-patient pagination is the only option that keeps rows coherent.
- PHP-side grouping after fetching the (small, seeded) filtered set keeps
  the code simple and testable; SQL window-function pagination would be
  complexity with no payoff at this volume.

## Consequences

- `AllergyController@index` branches on `view`; both modes share the same
  filter logic and eager loading (`patient`, `drug`, `recordedBy`).
- Grouped page size (10 patients) differs from flat page size (15 records);
  acceptable since the modes answer different questions.
- No data model change; `docs/domain-model.md` gains a read-model section.
