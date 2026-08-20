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
| Patient | A registered patient. `Pt_No`, FK `Clinic_No`. |
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