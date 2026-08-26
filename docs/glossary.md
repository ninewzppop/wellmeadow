# Glossary

| Term | Definition |
|---|---|
| Stf / Staff | A hospital employee (medical or non-clinical). Primary key `Stf_No`. |
| Wd / Ward | A hospital ward. Primary key `Wd_No`. |
| Bed | A physical bed within a ward. `Bed_No`, FK `Wd_No`, `BedStatus`. |
| Pos / Position | A job position. `Pos_No`, unique `Pos_Name`, `SalaryScale`. |
| StfPos | A staff member's appointment to a position (salary, hours, contract). |
| StfQual | A staff member's qualification (type, date, institution). |
| StfWorkExp | A staff member's previous work experience (organization, position, dates). |
| StfRota | A dated shift allocation of a staff member to a ward. `WkBegin` = week beginning date, `Shift` = Morning/Evening/Night. |
| Alloc_Wd_No | Staff member's primary/assigned ward. |
| LocalDr | A local (community) doctor, referenced by patients as `Clinic_No`. |
| Patient | A registered patient. `Pt_No` auto-generated as `PT{n}` (no zero-padding, continues from the highest existing number), FK `Clinic_No`. |
| NextOfKin | A patient's next of kin. |
| Appointment | An outpatient appointment; references patient, consulting staff, room. |
| Outpatient / InPatient | Appointment subtypes; InPatient also references a `Bed`. |
| Room | A consultation/treatment room. |
| Supplier | A supplier of stock or drugs. |
| CentralStock | Non-drug stock items. |
| Pharmaceutical | Drugs in pharmacy stock. |
| Medications | A drug course prescribed to a patient. |
| Wardrequisitions | A ward's stock/drug order header. |
| Itemrequest / Drugrequest | Order lines; composite PK on (requisition, item/drug). |
| PatientAllergy | A patient's recorded allergy to a drug. |
| User | A system account in the existing `users` table (`id`, `name`, `email`, `password`, remember token, timestamps). Same model/table Laravel ships; no schema changes. |
| Login | Session-based authentication using Laravel's `web` guard. `Auth::attempt(email + password)` checks the existing `users` table; a logged-in session is created on success. |
| Logout | `Auth::logout()` + session regeneration + redirect to `/login`. |
| Guest | State where no authenticated `User` session exists; visiting `/login` while already logged in redirects to `staff.index`. |
| Test user | Seed row in `users`: name `Test User`, email `test@example.com`, password `password123` (hashed with `Hash::make`). Used to verify login. |
| Grouped view | Default allergies listing mode (`view=grouped`): records merged under one rowspan patient cell per patient, paginated per patient (10/page), ordered patient A–Z. |
| Flat view | Record-level allergies listing mode (`view=flat`): 1 row = 1 record, 15/page — used for editing/deleting individual records. |
| View mode | Query parameter `view` with value `grouped` or `flat`; anything else falls back to `grouped`. Preserved across filter submissions and pagination links. |
| No Patient group | Trailing group in grouped view holding allergy records whose `Pt_No` is NULL. |
| Allergy count | Number of filtered `PatientAllergy` rows displayed for a patient in grouped view (badge "(N allergies)"); dropdown counts instead show the unfiltered lifetime total. |
| Room queue | Same-day appointments of one room with status in {waiting list, scheduled, in consultation}, ordered by `ApptTime` then `Appt_No`. |
| Active statuses | `waiting list`, `scheduled`, `in consultation` — the only states eligible for queue actions. |
| In consultation | Status of the single appointment currently being examined in a room (max 1 per room). |
| Completed - Medication Dispensed | Terminal visit state (`completed-medication`): a `Medications` row was recorded. |
| Completed - Admitted to Waiting List | Terminal visit state (`completed-waitlist`): an `InPatient` waiting-list row was created (`Bed_No` NULL until a bed is placed). |
| Allergy conflict | Prescribed drug matches a `PatientAllergy` row by `Drug_No` or case-insensitive name; saving requires explicit override confirmation. |
| Queue-context creation | Creating an appointment from a room's queue page (`?room=&date=`): form pre-fills room/date and saving redirects back to that day queue. |
| Form-selectable statuses | `waiting list`, `scheduled`, `cancelled`, `no-show` — the only statuses settable via the appointment form; lifecycle states are queue-action-only. |
| Board date | The queue day chosen once on the rooms board (`/rooms?date=`, default today) and carried into every room card link and the queue pages it opens. |
| Stock status | Computed state of an item: `out` (qty = 0), `low` (0 < qty ≤ ReorderLvl), `normal` (qty > ReorderLvl). NULL qty counts as 0; NULL ReorderLvl means always `normal`. |
| Expiry status | Pharma-only computed state: `expired` (ExpiryDate < today) or `near-expiry` (today ≤ ExpiryDate ≤ today + 90 days). Reported as separate buckets. |
| Surgical / NonSurgical | The two values of `CentralStock.ItemType`; the Stock page's category filter and search vocabulary. |
| Restock | Adding quantity to an item (+N): updates `QtyInStock` and writes one `StockMovement` row in a transaction. |
| Adjust-down | Removing stock without a dispensing flow (−N, e.g. damaged/count correction); requires a Note; also logged as a `StockMovement`. |
| StockMovement | Insert-only audit log of restocks/adjustments from the two inventory pages: which item, ±quantity, who (`Moved_By`), when (`MoveDate`), why (`Note`). Outbound flows are not logged. |
| Urgent-restock list | Dashboard list ordered out-of-stock first, then low-stock items. |
| ExpiryDate | New nullable DATE column on `Pharmaceutical`; one expiry per drug row (no batch/lot tracking). |
| Medication order | A header+lines prescription sent from the room queue to the pharmacy dispensing queue: `MedicationOrder(Order_No MO{n}, Pt_No, Stf_No=prescriber, Appt_No, status)` + child `MedicationOrderItem` rows (Drug_No, UnitsPerDay, AdminMethod, StartDate, FinishDate). Status `pending` means in the pharmacy queue; `dispensed`/`cancelled` mean it has left the queue (dispensed rows are copied into `Medications` as patient history with PaidAt/DispensedAt; cancelled rows keep CancelReason/CancelledAt). |
| Dispensing queue | The pharmacy worklist at `/medications`: all `MedicationOrder` rows with `status=pending` ordered by `OrderedAt` (order time). Shown with order time, patient + HN, prescriber, drug count/names, status `Pending`. |
| Order medication | The room-queue modal action that creates a multi-drug `MedicationOrder` (and its items) in `pending` state; the visit stays `in consultation` until finished separately via "Finish visit". |
| Confirm dispense & payment | Single combined pharmacy action (`POST /medications/{order}/confirm`) that, in one transaction, creates one `Medications` history row per order item and marks the order `dispensed` (`PaidAt = DispensedAt = now()`), removing it from the queue. |