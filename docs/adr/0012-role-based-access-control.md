# ADR-0012: Role-based access control (RBAC) on top of existing Auth

- **Date**: 2026-09-05
- **Status**: Implemented (2026-09-05; all 97 tests pass incl. 11 new `RoleAccessTest`)
- **Constraints**: Do NOT touch the login page, the existing Auth logic, existing migrations, or the `staffs`/`Stf` data. Additive changes only (new migration, new middleware/policies/views, seeder additions).

## Context

Login + session Auth already work (`users` table, `Auth::attempt`, ADR-0002).
Every protected route currently requires only `auth` — any logged-in user can
open everything. The Wellmeadows requirements define 8 roles with different
data scopes (own-ward only, own-patients only, no salary, no patient data).
Blocker found during grilling: `users` has NO link to `Stf`, so the system
cannot know which staff member (ward, position) is logged in.

## Decisions (from grilling)

### ADR-12.1 — Link User ↔ Staff (accepted: add `users.stf_no`)

- New migration (existing migrations untouched): `users.stf_no` nullable
  string FK → `Stf.Stf_No`, unique (one login account per staff member),
  indexed. `ON DELETE SET NULL` so deleting staff never deletes accounts.
- `User::staff()` BelongsTo; `Stf::user()` HasOne (new relations only).
- Staff without an account, and accounts with `stf_no = NULL` (e.g. legacy
  rows), keep working: ward scope falls back to "unrestricted" (ADR-12.5).

### ADR-12.2 — `admin` merges into `medical_director` (accepted)

- `users.role` value set becomes exactly 8 values:
  `medical_director, personnel_officer, charge_nurse, doctor, consultant,
  senior_nurse, staff_nurse, auxiliary`.
- `User::isAdmin()` is redefined as `role === 'medical_director'`.
  `EnsureUserIsAdmin` + the `admin` alias + `users.index` route keep working
  unchanged (now gated to medical directors). New `role` middleware coexists.

### ADR-12.3 — Position → Role mapping (accepted)

| Pos | Position | Role |
|---|---|---|
| P001 | Medical Director | `medical_director` |
| P002 | Personnel Officer | `personnel_officer` |
| P003 | Charge Nurse | `charge_nurse` |
| P004 | Senior Nurse | `senior_nurse` |
| P005 | Junior Nurse | `staff_nurse` |
| P006 | Doctor | `doctor` |
| P007 | Auxiliary | `auxiliary` |
| P008 | Consultant | `consultant` |
| P009 | Physiotherapist | `staff_nurse` (permission-wise; no separate role) |

Seeder backfills `users.role` from the linked staff member's `StfPos`
(first position wins when several exist).

### ADR-12.4 — Doctor/Consultant patient scope (accepted: via Appointment)

- A doctor's patient set = distinct `Pt_No` from `Appointment` where
  `Consult_Stf_No` = own `Stf_No`, **any date** (no date restriction).
- Medication/allergy/appointment rows for those `Pt_No` follow automatically.
- Implemented as a reusable scope (`Patient::forDoctor(Stf_No)`) used by the
  `PatientPolicy` and index queries — one source of truth, not per-controller
  heroics.

### ADR-12.5 — Staff with `Alloc_Wd_No = NULL` sees ALL wards (accepted, risky)

- Ward-scoped roles resolve their ward from `staff.Alloc_Wd_No`.
- `NULL` means **unrestricted** (all wards), NOT denied.
- Risk acknowledged: a forgotten ward allocation silently grants full
  visibility. Mitigation (required in seeder): every test/demo account gets a
  ward; ward-less accounts are flagged in the Users page (future).

### ADR-12.6 — Salary hiding (accepted: hide whole compensation group)

- For `doctor`/`consultant`, Blade hides `CurrSalary`, `HrsPerWk`,
  `ContractType`, `PaymentType` in ALL staff views (index/show/edit) via a
  role check. No API exists, so view-level hiding suffices; the columns are
  never sent to these roles.

### ADR-12.7 — Login/Logout audit log (accepted: file log)

- `Log::info('auth.login' / 'auth.logout', ['user_id','email','ip'])` added
  inside `AuthController@login/logout` (additive lines only, no logic change).
- No new table. Uses the default `stack` channel (`storage/logs`).

### ADR-12.8 — Self-service password change (accepted: single page)

- New `GET/PUT /profile/password` (own `PasswordController`, new views only):
  `current_password` + `password|min:8|confirmed`. Linked from the user
  dropdown. All roles may use it. Login/Auth untouched.

## Enforcement layers

1. **Route middleware** `role:a,b` (`RoleMiddleware`, variadic): denies
   whole areas per the route matrix below → redirect to `route('forbidden')`.
2. **Policies** (row-level): `PatientPolicy`, `StaffPolicy`,
   `MedicationPolicy`, `RequisitionPolicy`, `SupplyPolicy` — ward/doctor
   scoping inside allowed areas.
3. **Blade**: `@role` directive + sidebar menus filtered by role; header
   shows staff name, position, ward of the logged-in user.
4. **403 page** `errors/403` with a back button target chosen per role
   (own ward page / dashboard).

## Route → Role matrix (scope in brackets)

| Area | medical_director | personnel_officer | charge_nurse | doctor/consultant | senior/staff_nurse | auxiliary |
|---|---|---|---|---|---|---|
| Dashboard `/` | all | all | own ward | own | own ward | own |
| Staff CRUD + search | all | all (no patient data anyway) | — | view own-ward, salary hidden | — | — |
| Patients/Appointments/In-patients/Allergies/Rooms | all | — | manage [own ward] | own patients | view [own ward] | — |
| Medications (+ order/confirm) | all | — | record+view [own ward] | record+view [own patients] | view [own ward] | — |
| Stock/Pharmacy | all | — | view [own ward] | read drug list (prescribing) | view [own ward] | — |
| Requisitions | all | — | create+manage [own ward] | — | view [own ward] | — |
| Suppliers, Local doctors | manage | — | — | — | — | — |
| Rota | all | — | manage [own ward] | view own | view own | view own |
| Ward/patient/supply/waiting reports | all wards | staff-per-ward | own ward | — | — | — |
| Users & Roles (`users.index`) | manage | — | — | — | — | — |
| Password change | self | self | self | self | self | self |

`—` = `role` middleware redirects to the 403 page.

## Seeder plan

- `DatabaseSeeder`: `admin@example.com` → `medical_director`;
  `test@example.com` keeps working (role `staff_nurse`, linked to a ward).
- One test account per role (`director@`, `personnel@`, `charge@`,
  `doctor@`, `consultant@`, `senior@`, `nurse@`, `aux@` `@example.com`,
  password `password`), each linked to a `Stf` row with matching `Pos`
  and a ward (except personnel — ward optional).
- Existing `Stf` rows get roles via the §12.3 mapping; no `Stf` data edited.

## Consequences

- One new nullable column + index on `users`; everything else is new files.
- `role` middleware + 5 policies + `@role` + sidebar filtering + 403 page +
  password page + seeder additions.
- Assumption flagged for owner review: doctors get read-only pharmacy list
  access for prescribing (spec is silent; deny-by-default would break the
  order modal).

## Implementation notes (2026-09-05)

- Ward scope for entities WITHOUT a ward FK (Patient/Appointment/Medication/
  Allergy) is derived via `InPatient → Bed → Wd_No` (`User::accessiblePatientIds()`).
  Outpatients never admitted are invisible to ward roles — documented gap;
  adding `Wd_No` to `Appointment` needs owner approval (schema change).
- Unplaced waiting-list `InPatient` rows (`Bed_No` NULL) are visible to
  charge nurses of any ward (no ward to filter by yet).
- Resource write-routes register BEFORE read-routes: otherwise the `show`
  route captures `/create` and returns 404.
- Legacy test users migrated: `admin@example.com` → `medical_director`,
  `test@example.com` → `staff_nurse` (passwords untouched).
- Old feature tests updated to `role = medical_director` (they assume
  full access); `DashboardTest` users-page test now asserts via an explicit
  `staff_nurse` account.
