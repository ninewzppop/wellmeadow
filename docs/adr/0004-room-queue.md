# ADR-0004: Room queue & post-consultation actions on existing schema

- **Date**: 2026-08-25
- **Status**: Accepted

## Context

The hospital case study requires a "patient visit" flow: a patient arrives
(via local-doctor referral or walk-in) with an appointment for a consultant
in a consultation room; each room holds a patient queue in appointment order;
after examination the operator must finish the visit by either dispensing
medication, placing the patient on the in-patient waiting list, or simply
completing. Hard constraint: no schema changes — existing tables only.
Seeders may be extended for demo data.

Schema facts verified from migrations before design:
`Appointment(Appt_No PK, Pt_No, Consult_Stf_No, ApptDate, ApptTime,
Room_No, status varchar(20) default 'waiting list', index(status))`,
`Medications(Med_No PK, Pt_No, Stf_No, Drug_No, UnitsPerDay, AdminMethod,
StartDate, FinishDate)`, `InPatient(In_Pt_No PK, Pt_No, Bed_No nullable,
DateWaitList, ExpStayDays, DatePlaced, ...)`, `Outpatient(Appt_out_No PK =
Appt_No)`.

## Decision

1. **Queue status uses the existing `Appointment.status` column** with short
   codes that fit `varchar(20)`; long display text lives in an application
   label map (`Appointment::statusLabels()`), not the DB:
   - `waiting list` (existing default) → "Waiting for consultation"
   - `scheduled` (existing UI value) → treated as waiting in the queue
   - `in consultation` → "In consultation"
   - `completed-medication` → "Completed - Medication Dispensed"
   - `completed-waitlist` → "Completed - Admitted to Waiting List"
   - `completed` → "Completed"
   Legacy `cancelled` / `no-show` remain valid but never appear in queues.
2. **A room's queue** = appointments of that room for the selected date
   (default today) whose status ∈ {waiting list, scheduled, in consultation},
   ordered by `ApptTime`, then `Appt_No`. Same-day completed visits render in
   a separate dimmed "Finished today" section below the active queue.
3. **Consultation lifecycle**: "Start consultation" button moves
   waiting/scheduled → `in consultation`; only an appointment that is
   currently `in consultation` can be completed via any of the three exit
   paths. At most one appointment per room may be `in consultation` at any
   time (enforced server-side and reflected in disabled UI).
4. **Admit as inpatient** writes a row to the existing `InPatient` table in
   its waiting-list shape: `Bed_No = NULL`, `DateWaitList = today`,
   `ExpStayDays` from the form, all other dates NULL. The desired ward and
   expected entry date are **not stored anywhere** — the existing table has
   no home for them and adding columns was declined; ward becomes visible
   once a bed is actually placed via the existing in-patients page.
5. **Dispense medication** writes one `Medications` row
   (`Stf_No` = the appointment's consultant, `Med_No` auto-generated) then
   sets `completed-medication`. Before saving, the chosen drug is checked
   against the patient's `PatientAllergy` rows: a conflict is a matching
   `Drug_No` OR a case-insensitive `Allergy_Name` equal to the drug name.
   On conflict the modal shows a red warning listing allergens/reactions and
   the save button stays disabled until an explicit "dispense anyway"
   checkbox is ticked; the server re-checks and rejects without the
   `override_allergy` flag.
6. **Allergy indicator in the queue**: patients with ≥ 1 `PatientAllergy`
   row get a warning icon + red accent next to their name (tooltip lists
   allergens). Any severity triggers it.
7. **IDs auto-generated** for new rows (`Medications.Med_No`,
   `InPatient.In_Pt_No`): prefix + zero-padded increment based on the highest
   existing numeric suffix (retry-safe within `varchar(10)`).
8. `/rooms` placeholder route replaced by a real `RoomController`
   (index = room board with today counts; show = day queue); sidebar menu
   already pointed at `rooms.index`.
9. `ClinicalSeeder` gains same-day demo appointments across both rooms with
   mixed statuses plus an allergy whose drug matches a seed pharmaceutical so
   the indicator and the medication warning are demonstrable.

## Rationale

- Short status codes + label map satisfy the long-text requirement inside
  the immutable `varchar(20)` column — no migration needed.
- Restricting completion to `in consultation` keeps a single auditable path
  through the lifecycle and makes the "who is being seen now" state real
  instead of implicit.
- Waiting-list shape follows the existing `InPatientController` convention
  (waiting = `DateWaitList` set, `DatePlaced` NULL).

## Consequences

- Dashboard/appointments pages keep working: they read the same column;
  unknown legacy values still fall back to slate styling.
- Completing requires two clicks (start, then action) — deliberate.
- If preferred-ward tracking is wanted later, it needs a new approved
  migration (`PrefWd_No`, optional `ExpectedDate`) — documented, not built.
