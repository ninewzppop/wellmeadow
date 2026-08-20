# Domain Model — Hospital

Source of truth: `hospital.sql` (converted to migrations on 2026-08-19). 23 tables, 30 foreign keys.

## Entities

| Entity | Table | Key attributes | Lifecycle |
|---|---|---|---|
| Staff | `Stf` | Stf_No PK, name, contact, DOB, Sex, NIN (unique), **Alloc_Wd_No** (primary ward) | create/edit/delete (children removed first) |
| Ward | `Wd` | Wd_No PK, Wd_Name, Location, TotalBeds, TelExtension | seeded |
| Bed | `Bed` | Bed_No PK, Wd_No FK, BedStatus | seeded |
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