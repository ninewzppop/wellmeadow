# Domain Model — Hospital

Source of truth: `hospital.sql` (converted to migrations on 2026-08-19). 23 tables, 30 foreign keys.

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| Staff | `Stf` | Stf_No PK, name, contact, DOB, Sex, NIN (unique), **Alloc_Wd_No** (primary ward) | create/edit/delete (children removed first) |
| Ward | `Wd` | Wd_No PK, Wd_Name, Location, TotalBeds, TelExtension | seeded (17 wards WD01-WD17; WD01-WD05/WD08-WD17=14 beds, WD06-WD07=15 beds — ADR-0010) |
| Bed | `Bed` | Bed_No PK, Wd_No FK, BedStatus | seeded (240 beds: 101-114…1701-1714 — ADR-0010) |
| Position | `Pos` | Pos_No PK, Pos_Name (unique), SalaryScale | seeded |
| Staff Position | `StfPos` | StfPos_No PK, Stf_No FK, Pos_No FK, CurrSalary, HrsPerWk, ContractType, PaymentType | rebuilt on staff save |
| Qualification | `StfQual` | Qual_No PK, Stf_No FK, Type, QualDate, Institution | rebuilt on staff save |
| Work Experience | `StfWorkExp` | WorkExp_No PK, Stf_No FK, Organization, Position, StartDate, FinishDate | rebuilt on staff save |
| Roster allocation | `StfRota` | StfRota_No PK, Stf_No FK, Wd_No FK, WkBegin, Shift | managed via /allocations |
| Local Doctor | `LocalDr` | Clinic_No PK | seeded |
| Patient | `Patient` | Pt_No PK, Clinic_No FK → LocalDr | seeded |
| Next of Kin | `NextOfKin` | NOK_No PK, Pt_No FK | seeded |
| Appointment | `Appointment` | Appt_No PK, Pt_No FK, Consult_Stf_No FK, Room_No FK, ApptDate, ApptTime | seeded |
| Outpatient | `Outpatient` | Appt_out_No PK (→ Appointment) | seeded |
| InPatient | `InPatient` | In_Pt_No PK, Pt_No FK, Bed_No FK, stay dates | seeded |
| Room | `Room` | Room_No PK, RoomName, Location | seeded |
| Supplier | `Supplier` | Suppl_No PK | seeded |
| Stock item | `CentralStock` | Item_No PK, Suppl_No FK | seeded |
| Drug | `Pharmaceutical` | Drug_No PK, Suppl_No FK | seeded |
| Medication | `Medications` | Med_No PK, Pt_No FK, Stf_No FK, Drug_No FK | seeded |
| Ward requisition | `Wardrequisitions` | Wd_Req_No PK, Stf_No FK, Wd_No FK, dates | seeded |
| Item request line | `Itemrequest` | (Wd_Req_No, Item_No) PK | seeded |
| Drug request line | `Drugrequest` | (Wd_Req_No, Drug_No) PK | seeded |
| Allergy | `PatientAllergy` | Allergy_No PK, Pt_No FK, Drug_No FK, Rec_Stf_No FK | seeded |
| Medication order | `MedicationOrder` (**new**) | Order_No PK `MO{n}` un-padded, Pt_No FK, Stf_No FK (prescriber), Appt_No FK, status {pending,dispensed,cancelled}, OrderedAt, PaidAt, DispensedAt, CancelledAt, CancelReason | create via room modal → pending in dispensing queue → dispensed (copies to Medications) or cancelled |
| Medication order item | `MedicationOrderItem` (**new**) | id AI PK, Order_No FK → MedicationOrder, Drug_No FK, UnitsPerDay, AdminMethod, StartDate, FinishDate | sub-rows of an order; created with header, deleted by cascade |

## Relationships (site-relevant)

- Staff 1—N Qualification / WorkExperience / StaffPosition / RosterAllocation
- Ward 1—N Bed, 1—N StfRota
- Staff N—1 Ward (primary, via `Alloc_Wd_No`) and N—N Ward (via StfRota)
- Pos 1—N StfPos

## Ward allocation design

- **Primary ward**: `Stf.Alloc_Wd_No` (a staff member's home/assigned ward).
- **Shift roster**: `StfRota` rows (Stf_No, Wd_No, WkBegin, Shift) — a dated/week-based shift assignment. The `/wards/report` page groups StfRota rows by ward, filterable by WkBegin.

## Searchable fields

Staff search (LIKE, case-insensitive): `StfQual.Type`, `StfQual.Institution`, `StfWorkExp.Organization`, `StfWorkExp.Position`.

## Invariants

- `Stf.NIN` unique (nullable → multiple NULLs allowed).
- `Pos.Pos_Name` unique.
- Composite PKs on `Itemrequest`, `Drugrequest` enforce one line per (requisition, item/drug).
- PK/FK columns are nullable except PKs and the NOT NULL columns in `Medications`, `Drugrequest.QtyReq`, and `PatientAllergy` (matches source SQL).

---

# Authentication — login/logout on the existing `users` table

Added 2026-08-20 (ADR-0002).

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| User | `users` (existing, **unmodified**) | `id` PK, `name`, `email` (unique), `email_verified_at`, `password` (hashed), `remember_token`, timestamps | created via `Hash::make` seed; login reads it; no in-app write |

## Value objects

- **Credentials**: an email + password pair submitted at login. Validated as
  required `email` / required string `password`.
- **Session**: the authenticated web session created by the `web` guard after
  `Auth::attempt()` succeeds.

## Relationships

- User is standalone — unrelated to the hospital domain tables (`Stf`, `Wd`, ...).
- Web session 1—1 User (per login), from the `sessions` table (`user_id` FK).

## Invariants

- `users.email` unique — a single email identifies at most one account.
- `users.password` always stored hashed (`User::casts` → `hashed`; seed uses `Hash::make`).
- Protected routes (staff, search, allocations, ward report) reject unauthenticated
  requests with a redirect to `/login`.
- `/login` rejects already-authenticated visits with a redirect to `staff.index`.

## Persistence map

| Concern | Storage | Mutations |
|---|---|---|
| Account | `users` table (existing) | Insert 1 test row (tinker + `Hash::make`) only |
| Auth state | `sessions` table (existing, standard Laravel) | created on login, regenerated + flushed on logout |
| Schema | — | none — no new tables, no migration edits |

---

# Allergies page — grouped read model

Added 2026-08-25 (ADR-0003).

Purely a **read-model** change over the existing entities — no new entities,
tables or columns. "Group" is not an entity; it is a presentation-time
grouping of `PatientAllergy` rows by `Pt_No`.

## Entities involved (all existing)

| Entity | Role in this feature |
|---|---|
| Patient | Group key; name A–Z ordering via `LastName`, `FirstName` |
| PatientAllergy | Rows inside a group; ordered `DiagDate desc`, `Allergy_No asc`; `Pt_No` nullable → "No Patient" group last |
| Pharmaceutical / Stf | Eager-loaded for allergen and recorded-by display |

## Value objects

- **View mode**: `grouped` (default) or `flat`, carried by query parameter
  `view`; invalid values fall back to `grouped`.
- **Allergy count**: computed value per group = number of filtered records
  displayed for that patient (never stored).

## Invariants

- A patient's group never spans two pages in grouped mode (pagination is
  per group, 10 groups/page).
- Badge counts always equal the number of visible rows for that group.
- Dropdown counts always equal the patient's total `PatientAllergy` rows,
  independent of search/severity filters.
- Every `PatientAllergy` row appears in exactly one group per page-set;
  NULL `Pt_No` rows appear only in the trailing "No Patient" group.

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Grouped listing | `PatientAllergy::with(['patient','drug','recordedBy'])` + filters, grouped in PHP | manual `LengthAwarePaginator` over groups |
| Flat listing | same base query, existing 15/page paginator | unchanged behaviour |
| Dropdown totals | single grouped count query (`GROUP BY Pt_No`) | independent of other filters |
| Schema | — | none — read-only over existing tables |

---

# Room queue & post-consultation actions (Patient Visit)

Added 2026-08-25 (ADR-0004).

Read/write flow over **existing entities only** — the queue is a filtered
view of `Appointment`; completing a visit updates `Appointment.status` and
optionally creates a `Medications` or waiting-list `InPatient` row.

## Entities involved (all existing)

| Entity | Role in this feature | Lifecycle touched |
|---|---|---|
| Appointment | The queue entry / visit; `status` drives the flow | created via appointments page; status transitions here |
| Room | Groups appointments into queues | read-only |
| Medications | Created when visit ends with dispensing | create-only here |
| InPatient | Created as waiting-list row on admit (`Bed_No` NULL, `DateWaitList` = today, `ExpStayDays` set) | create-only here; bed placement later via in-patients page |
| PatientAllergy | Queue indicator + medication safety check | read-only |
| Patient / Stf / Pharmaceutical / Wd | Display + validation references | read-only |

## Value objects

- **Queue status** (stored ≤ 20 chars, labels mapped in code):
  `waiting list`, `scheduled`, `in consultation`,
  `completed-medication`, `completed-waitlist`, `completed`
  (+ legacy `cancelled`, `no-show` outside queues).
- **Active statuses**: {waiting list, scheduled, in consultation}.
- **Allergy conflict**: prescribed drug matches an allergy by `Drug_No`
  OR case-insensitive `Allergy_Name` = drug name.

## Relationships

- Room 1—N Appointment (queue grouping)
- Appointment N—1 Patient, N—1 Stf (consultant)
- Medications N—1 Patient, Drug, Stf (= consultant who completed)
- InPatient N—1 Patient, optional Bed (NULL while waiting)

## Invariants

1. At most one appointment per room has status `in consultation`.
2. Only `waiting list`/`scheduled` → `in consultation` → exactly one of
   `completed-*`; terminal states never transition via queue actions.
3. A room's active queue contains only same-day appointments with active
   statuses, ordered by `ApptTime`, then `Appt_No`.
4. Medication rows require `FinishDate ≥ StartDate`, `UnitsPerDay ≥ 1`;
   saving with a conflict requires explicit override confirmation
   (client checkbox + server re-check).
5. Waiting-list `InPatient` rows always have `Bed_No` NULL,
   `DateWaitList` set, `DatePlaced` NULL.
6. New `Med_No` / `In_Pt_No` are generated server-side within
   `varchar(10)`.

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Room board counts | `GROUP BY Room_No, status` for today | index on `status` exists |
| Day queue | `Appointment` whereDate `ApptDate` + status IN (active) orderBy `ApptTime` | eager loads patient.allergies.drug, consultant |
| Allergy indicator | nested eager load `patient.allergies.drug` | no extra queries per row |
| Medication save | insert `Medications` + status update, transaction | conflict re-checked server-side |
| Admit save | insert `InPatient` (waiting shape) + status update, transaction | ward chosen later at bed placement |
| Schema | — | none — existing columns only |

---

# Appointment form & index alignment

Added 2026-08-26 (ADR-0005). UI/read-model refinement of the same
entities — no schema impact.

- `Appt_No` auto-generated on create (prefix `A`, zero-padded max-suffix
  increment); never typed by users.
- Form-selectable statuses limited to {`waiting list`, `scheduled`,
  `cancelled`, `no-show`}; lifecycle states (`in consultation`,
  `completed-*`) remain queue-action-only per invariant 2 above.
- Appointments list reuses `statusLabels()` + shared badge component so
  status vocabulary has one source of truth across pages.

Schema: unchanged.

Added 2026-08-26 (ADR-0006): the queue date is selected once on the rooms
board (`?date=`, default today, invalid → today) and flows through card
links into each room's day queue; the queue page itself is picker-less.

---

# Pharmaceutical & Stock pages

Added 2026-08-26 (ADR-0007). Two inventory pages over `Pharmaceutical`
and `CentralStock` plus one new movement log table; two owner-approved
schema additions.

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| Pharmaceutical (drug) | `Pharmaceutical` (+ approved `ExpiryDate DATE NULL`) | Drug_No PK, Dosage, AdminMethod, QtyInStock, ReorderLvl, ExpiryDate, Suppl_No FK | create (auto-ID DRnn) / edit / restock / adjust / guarded delete |
| Stock item (supply) | `CentralStock` (unmodified schema) | Item_No PK, ItemType ∈ {surgical, non-surgical}, Description, QtyInStock, ReorderLvl, Suppl_No FK | create (auto-ID ITnn) / edit / restock / adjust / guarded delete |
| Stock movement | `StockMovement` (**new**) | id AI PK, Drug_No NULL FK, Item_No NULL FK, QtyChange signed, Note NULL, Moved_By FK users.id NULL, MoveDate | insert-only audit log |

Seeded via `SuppliesSeeder`: 27 drugs (DR01-DR27) + 26 supplies (IT01-IT26) = 53 items after ADR-0011 (was 17+16=33); new 20 items added to preserve old data for test coverage (ADR-0011).

## Value objects

- **Stock status**: `out` (qty = 0), `low` (0 < qty ≤ ReorderLvl),
  `normal` (qty > ReorderLvl); computed in model accessors —
  QtyInStock NULL ⇒ 0, ReorderLvl NULL ⇒ always normal.
- **Expiry status** (pharma only): `expired` (ExpiryDate < today),
  `near-expiry` (today ≤ ExpiryDate ≤ today + 90 days); reported as
  separate buckets, never merged.
- **Movement direction**: restock = +N; adjust-down = −N with required
  Note.

## Relationships

- Pharmaceutical 1—N StockMovement; CentralStock 1—N StockMovement
  (exactly one of the two FKs set per movement row).
- StockMovement N—1 User (who performed the action).
- Both items N—1 Supplier (existing).

## Invariants

1. Every restock/adjust writes exactly one `StockMovement` row in the
   same transaction as the `QtyInStock` update.
2. A movement row references exactly one of {Drug_No, Item_No}.
3. Forms require QtyInStock ≥ 0 and ReorderLvl ≥ 0 — new rows are never
   status-ambiguous.
4. Adjust-down requires a non-empty Note; QtyChange ≠ 0.
5. Delete fails gracefully when Medications/PatientAllergy/Itemrequest
   still reference the row.
6. Status/expiry logic exists only in model accessors — never duplicated
   in controllers or views.

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Dashboard counts | aggregate queries on status accessors' underlying conditions (`qty = 0`, `qty <= ReorderLvl`, expiry window) | per page |
| Urgent-restock list | ORDER BY qty = 0 DESC, then low-stock | out first, then low |
| Search | LIKE on Name / code / ItemType / Description | case-insensitive |
| Filters | status ∈ {all, low, out, normal}; stock page adds surgical/non-surgical | query params |
| Restock/adjust | transaction: UPDATE QtyInStock + INSERT StockMovement | Moved_By = auth user |
| Movement history | StockMovement by Drug_No/Item_No, newest first | insert-only |
| Schema | ADD `Pharmaceutical.ExpiryDate`; CREATE `StockMovement` | only approved changes |

---

# Medication ordering queue

Added 2026-08-26 (ADR-0008). Header+items order queue separating "ordered" from "dispensed history".

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| Medication order | `MedicationOrder` (**new**) | Order_No PK `MO{n}`, Pt_No FK, Stf_No FK, Appt_No FK, status {pending,dispensed,cancelled}, OrderedAt/PaidAt/DispensedAt/CancelledAt, CancelReason | created in room modal (pending) → pharmacy confirms → dispensed (copies items to `Medications`) or cancelled (reason required); pending rows form the queue |
| Medication order item | `MedicationOrderItem` (**new**) | id AI PK, Order_No FK CASCADE, Drug_No FK, UnitsPerDay, AdminMethod, StartDate, FinishDate | one per drug line; created with header |

## Value objects

- **Order status**: `pending` (in queue), `dispensed` (history written, leaves queue), `cancelled` (reason stored, leaves queue). No `Ready` state — single combined confirm per Q2.
- **Drug row**: a line's `Drug_No`/dose/method/dates; history-copy uses current selection, validated `FinishDate ≥ StartDate`.

## Relationships

- MedicationOrder N—1 Patient, N—1 Stf (prescriber = appointment consultant), N—1 Appointment
- MedicationOrder 1—N MedicationOrderItem CASCADE
- MedicationOrderItem N—1 Pharmaceutical (drug)
- Prior dispensed history lives in `Medications` (existing) — copied to, not linked from, on confirm.

## Invariants

1. Every order has ≥ 1 item; FinishDate ≥ StartDate and UnitsPerDay ≥ 1 per item.
2. An order is pending until exactly one of: confirm (copies each item to `Medications` + sets PaidAt/DispensedAt + deducts stock) or cancel (sets CancelledAt/CancelReason); terminal states never re-enter the queue.
3. Allergy check is aggregated across all items (Drug_No OR name match against `PatientAllergy`); a pending conflict blocks saving without explicit `override_allergy`.
4. The pharmacy queue is `status = pending` ordered by `OrderedAt` ascending; finished/cancelled orders are absent.
5. Order_No `MO{n}` and `Medications.Med_No` `MD{n}` generation is server-side, un-padded increment.
6. Dispensing requires `Pharmaceutical.QtyInStock` ≥ `UnitsPerDay × daysInclusive` per item; if any item is out-of-stock the whole confirm is blocked with `Cannot dispense :drug — only :stock left, need :need.` No partial dispense.

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Doctor ordering (modal) | Transaction: INSERT MedicationOrder + N MedicationOrderItem | history panel from `Patient.medications`; sync-dates helper is client-only |
| Pharmacy queue | `MedicationOrder` WHERE status=pending ORDER BY OrderedAt | index on status |
| Pharmacy detail | `MedicationOrder` + items.drug + patient.allergies.drug + prescriber | allergy warning display-only |
| Confirm (single button) | Transaction (lockForUpdate on Pharmaceutical): check QtyInStock ≥ need per item; if all pass, decrement QtyInStock, insert StockMovement (QtyChange = -need, Note = Dispensed for order), N INSERT `Medications` (`MD{n}`) + UPDATE order dispensed/PaidAt/DispensedAt | all-or-nothing; blocks when out-of-stock |
| Cancel | UPDATE order cancelled/CancelledAt/CancelReason (reason required) | leaves queue; no history write |
| Schema | CREATE `MedicationOrder`, `MedicationOrderItem` — no ALTER of existing tables | owner-approved via grilling Q1 |

---

# Ward Requisition (store → ward supply)

Added 2026-08-27 (ADR-0009). Header + two line tables for surgical/non-surgical supplies and drugs.

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| Ward requisition | `Wardrequisitions` (+ `status`, `Received_By` approved) | Wd_Req_No PK `WR{n}`, Stf_No FK (requester), Wd_No FK, DateOrd, DateRecv, status {Pending,Approved,Completed}, Received_By FK | create Pending → approve (deduct stock) → Completed (receive) → history; edit/delete only while Pending |
| Supply request line | `Itemrequest` (**existing**) | (Wd_Req_No, Item_No) PK, QtyReq | child of requisition; rewritten on update |
| Drug request line | `Drugrequest` (**existing**) | (Wd_Req_No, Drug_No) PK, QtyReq | child of requisition; rewritten on update |

## Value objects

- **Requisition status**: `Pending` (awaiting store), `Approved` (stock deducted, awaiting delivery), `Completed` (received at ward with signer + date). No `Delivered` gap — Approved covers it until receipt.
- **Cost**: `QtyReq × CostPerUnit` live from `CentralStock`/`Pharmaceutical`; `Total = Σ lines`.

## Relationships

- Wardrequisition N—1 Wd, N—1 Stf (requester), N—1 Stf (receiver via Received_By)
- Wardrequisition 1—N Itemrequest, 1—N Drugrequest (at least one line total; can mix supplies + drugs in one header)
- Itemrequest N—1 CentralStock, Drugrequest N—1 Pharmaceutical
- Both stock masters 1—N StockMovement (deduction logged on approve)

## Invariants

1. Every requisition has ≥1 line and `Wd_No + Stf_No` required; `Wd_Req_No` `WR{n}` un-padded.
2. Only `Pending` may be edited/deleted; `Approved` may only be received; `Completed` is archival (queue hides it).
3. Approve checks `QtyInStock ≥ QtyReq` per line (lockForUpdate); if any line fails the whole approve is blocked with `Cannot approve :item — only :stock left`.
4. Approve deducts `QtyInStock` and writes one `StockMovement` per line (`QtyChange = -QtyReq`, Note = Approved requisition :no) in the same transaction.
5. Receive requires `Received_By` + `DateRecv` and moves the requisition to history/report.

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Create (Charge Nurse) | Transaction: INSERT Wardrequisitions + N Itemrequest/Drugrequest | grouped select with stock & cost data-attrs; DateOrd defaults today |
| Queue (Store) | `Wardrequisitions` WHERE status IN (Pending,Approved) ORDER BY DateOrd | paginated, filters ward/status; total cost live |
| Detail | `Wardrequisitions` + ward/requester/receiver + itemRequests.item / drugRequests.drug | shows cost, stock with Low badge (`QtyInStock ≤ ReorderLvl`) |
| Approve | Transaction lockForUpdate on CentralStock/Pharmaceutical → deduct → StockMovement → status Approved | blocks when out-of-stock |
| Receive | UPDATE Received_By/DateRecv/status Completed | history hides from queue |
| History/Report | `Wardrequisitions` WHERE status=Completed + filters ward/date range; low-stock lists are `QtyInStock ≤ ReorderLvl` from masters | per ward + time period as per (n) |
| Schema | ADD `Wardrequisitions.status`, `Received_By` — only approved change | no new tables |
| Suppliers (l) | `Supplier` CRUD via existing `SupplierController` | Medical Director scope, no change |

---

# Role-based access control (RBAC)

Added 2026-09-05 (ADR-0012). Grilled spec (8 sub-decisions ADR-12.1–12.8),
owner-confirmed. Additive only: one new nullable column on `users`, new
middleware/policies/views/seeders. Login page, Auth logic, old migrations
and `Stf` data untouched.

## Entity list

| Entity | Type | Key attributes | Lifecycle |
|---|---|---|---|
| User | entity (`users` + `stf_no` nullable unique FK → `Stf.Stf_No`, `role` ∈ 8 values) | id PK, email unique, password hashed, role, stf_no | seeded (test accounts per role); self-service password change only |
| Role | value object (enum string on `users.role`, no table) | one of `medical_director, personnel_officer, charge_nurse, doctor, consultant, senior_nurse, staff_nurse, auxiliary` | fixed set; seeded from Pos mapping |
| WardScope | value object (derived per request) | `own ward` (= `staff.Alloc_Wd_No`) or `all` (medical_director, or `Alloc_Wd_No` NULL per ADR-12.5) | computed, never stored |
| CareRelationship | derived link doctor→patients | via `Appointment.Consult_Stf_No`, any date | read-only derivation |
| CompensationBlock | value object (presentation) | `CurrSalary, HrsPerWk, ContractType, PaymentType` — hidden from doctor/consultant | render-time only |

## Relationships

- User N—1 Stf (nullable, via `stf_no`; NULL = legacy/unlinked account).
- Stf N—1 Ward primary (`Alloc_Wd_No`); Stf N—N Ward via `StfRota`.
- Stf N—N Pos via `StfPos` (position seeds the user's role, first wins).
- Doctor N—N Patient via `Appointment` (`Consult_Stf_No`).
- Session 1—1 User (existing, ADR-0002).

## Invariants

1. `users.email` unique; `users.password` always hashed (existing).
2. `users.role` ∈ the 8-value set; `users.stf_no` unique-or-NULL.
3. `isAdmin()` ⟺ `role === 'medical_director'` (old `admin` value retired).
4. Ward-scoped queries always filter by `WardScope`; `NULL` ward = all wards.
5. Doctor queries always restrict patients to the CareRelationship set.
6. CompensationBlock is never rendered for `doctor`/`consultant`.
7. `personnel_officer` and `auxiliary` can never reach patient/medication
   routes (middleware deny, not just hidden menus).
8. Every login/logout writes `Log::info` with user id + email + IP.
9. Deleting `Stf` sets `users.stf_no` NULL (accounts survive).

## Persistence map

| Concern | Storage/Query | Notes |
|---|---|---|
| Link + role | `ALTER users ADD stf_no VARCHAR NULL UNIQUE, FK → Stf` (+ index) | only schema change; new migration |
| Role check (area) | `RoleMiddleware role:a,b` on route groups | redirect to `forbidden` (403 page) |
| Role check (row) | 5 policies: Patient/Staff/Medication/Requisition/Supply | share `WardScope` + `forDoctor` helpers |
| Doctor patient set | `WHERE Pt_No IN (SELECT Pt_No FROM Appointment WHERE Consult_Stf_No = ?)` | `Patient::forDoctor()` scope, one source |
| Salary hiding | Blade `@role` / role check in staff views | no API to protect |
| Sidebar/header | menus filtered by role; header shows staff name + Pos + Ward | `@role` directive |
| Audit | `Log::info` in `AuthController` (additive) | file channel, no table |
| Password self-change | new `PasswordController` + views, `current_password` rule | all roles |
| Seeds | role backfill via Pos mapping + 8 test accounts | `Stf` rows unmodified |