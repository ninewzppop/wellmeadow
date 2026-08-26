# ADR-0008: Medication ordering queue (multi-drug orders + pharmacy dispense)

- **Date**: 2026-08-26
- **Status**: Accepted

## Context

The EMR previously had a single-drug "Dispense medication" modal in the room queue (`rooms.show`): doctor picked one `Drug_No`/dose/method/dates and `RoomController::medicate` wrote directly to `Medications(Med_No, Pt_No, Stf_No, Drug_No, UnitsPerDay, AdminMethod, StartDate, FinishDate)` and set the appointment status to `completed-medication` (visit ended in the same action). There was no intermediate state — an order was instantly a history record. Requirements for the new workflow (validated in grilling interview):

1. **Doctor side**: multi-drug order in one action, checkbox "Apply same dates to all drugs", row add/remove, summary above Save, and a left panel of the patient's previous medication history with checkboxes to copy into the order. Save must mean "send to the pharmacy dispensing queue", **not** "complete the visit" — the existing separate "Finish visit" button stays and the appointment remains `in consultation` until the doctor finishes explicitly.
2. **Pharmacy side**: the `/medications` placeholder becomes a real queue index showing pending orders (order time, patient + HN, prescriber, drug count + names, status `Pending`), a detail page with full item list and patient-allergy warnings, a single combined confirmation "Confirm dispense & payment" and a separate cancellation with required reason. On final confirmation the system copies order items into `Medications` as patient history and the order leaves the queue. Cancellation keeps the order with `cancelled` status and `CancelReason` for audit — also leaving the queue.
3. **Hard schema constraint**: existing tables/columns must not be altered (`ALTER TABLE`, rename/drop forbidden). Creating new tables is acceptable (grilling Q1 = A). Single-button dispense+payment was chosen (grilling Q2 = A), so the queue has effectively one queue status (`Pending` until removed).

The legacy `Medications` table has no status, payment, or cancellation columns and cannot gain them without violating the constraint.

## Decision

1. **Two new tables** (CREATE TABLE only — zero changes to existing schema):
   - `MedicationOrder(Order_No VARCHAR(10) PK, Pt_No VARCHAR(10) NULL FK→Patient, Stf_No VARCHAR(10) NULL FK→Stf, Appt_No VARCHAR(10) NULL FK→Appointment, status VARCHAR(20) DEFAULT 'pending' CHECK in {pending,dispensed,cancelled} application-enforced, OrderedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PaidAt TIMESTAMP NULL, DispensedAt TIMESTAMP NULL, CancelledAt TIMESTAMP NULL, CancelReason VARCHAR(255) NULL, INDEX(status), INDEX(Pt_No))`
   - `MedicationOrderItem(id BIGINT AI PK, Order_No VARCHAR(10) FK→MedicationOrder CASCADE, Drug_No VARCHAR(30) NULL FK→Pharmaceutical, UnitsPerDay INT, AdminMethod VARCHAR(30), StartDate DATE, FinishDate DATE, INDEX(Order_No))`
   `Order_No` is generated **server-side** as `MO{n}` (un-padded, `MO1`, `MO2` …) via `MedicationOrder::nextNo()` (max numeric suffix + 1, same no-padding convention as recent `PT`/`A`/`IP` work) and previewed nowhere (pharmacy creates no orders — doctors do).

2. **Routing**:
   - `POST /rooms/{room}/appointments/{appointment}/medication-order` → `MedicationOrderController::store` replaces the old `rooms.medicate` (old route/action removed; the admit/complete routes are untouched).
   - `GET /medications` and `GET /medications/{order}` take over the former placeholder (keeps existing menu/sidebar name `medications.index`).
   - `POST /medications/{order}/confirm` and `POST /medications/{order}/cancel` handle pharmacy actions.

3. **Doctor modal redesign** (`rooms.show`):
   - Wider dialog (`max-w-4xl`), two-column layout: left = medication history (`Patient.medications` with drug names, 20 most recent) with a checkbox per row to append as a new order row; empty-state text when none. Right = dynamic order rows (`template` clone, JS `addRow`/`removeRow`), each row has `Drug_No` select (with `data-name` for allergy matching), `UnitsPerDay`, `AdminMethod`, `StartDate`, `FinishDate` plus a trash button; `+ Add another drug` button; top "Apply same dates to all drugs" checkbox with two master date inputs that sync into every row; allergy warning panel (aggregated) with an `override_allergy` checkbox gating submit; order summary list above the Save button.
   - Form field naming: `drugs[][Drug_No]` etc. (sequential arrays); server validation: `drugs` required array min 1, `drugs.*.{Drug_No,UnitsPerDay,AdminMethod,StartDate,FinishDate}` with `FinishDate after_or_equal:drugs.*.StartDate`; aggregated allergy check (Drug_No OR name match against `PatientAllergy`) blocks without `override_allergy`.
   - Save ("Send to dispensing queue") creates `MedicationOrder` + `MedicationOrderItem` rows in a transaction, keeps the appointment in `in consultation`, and redirects back to the room queue with `Sent medication order :no to the dispensing queue.` The history-panel checkboxes are write-once (checked → appended row, unchecked does not delete).

4. **Pharmacy pages** (`medication-orders/index` + `show`):
   - Index filters `status = pending`, ordered by `OrderedAt`, shows order time, patient+HN, prescriber, badge with item count + truncated drug names, and status badge `Pending`; empty-state copy is included.
   - Show loads `items.drug`, `patient.allergies.drug`, `prescriber`; displays the same allergy warning (display-only) and the item table. Two separate cards: (a) green "Confirm dispense & payment" single POST (chosen over a two-step payment-then-dispense flow per Q2) which — in a transaction — creates one `Medications` row per item (via existing `generateId('Medications','Med_No','M')`, continuing the legacy padded `M0000…` generation — intentionally not switched to un-padded in this change; see Consequences) with `Pt_No/Stf_No` from the order, and marks the order `dispensed` (`PaidAt = DispensedAt = now()`); (b) red "Cancel order" card with required `CancelReason` textarea, marking `cancelled`/`CancelledAt`/`CancelReason`. Both actions remove the order from the queue (index queries pending only).

5. **RoomController**: `show` now eager-loads `patient.medications.drug` for the history panel; the old `medicate` method and its server-side `Drug_No` single-field validation are deleted (the logic lives in `MedicationOrderController::store` with multi-row validation).

6. **No existing column or row is migrated**: `Medications` retains its 8 columns, `Appointment` status vocabulary stays unchanged (the appointment is completed only via the existing `Finish visit` path), and no price/amount column is introduced — payment is a boolean timestamp (`PaidAt`), not an amount, because the `Pharmaceutical` table has no pricing column to compute from.

## Rationale

- A header+items order table cleanly separates "ordered but not yet dispensed" from "dispensed history" without overloading `Medications`; the existing constraint made an in-table status column impossible, and reusing `Wardrequisitions/Drugrequest` was rejected as a domain mismatch (ward requisitions ≠ OPD prescriptions).
- Header-level `status/cancelReason/paidAt` satisfies the spec's cancellation-with-reason and combined-payment asks without touching legacy rows.
- Replacing `rooms.medicate` while keeping `rooms.complete` as a separate action matches the spec's "keep the existing complete button, don't delete it, let it work alongside order creation" and avoids silently changing visit lifecycle.
- Single-button confirm keeps the pharmacy UX to one decisive action while still yielding the required `Pending`-only queue column; future split into two steps (pay → ready → dispense) would only need `status = 'ready'` and an extra route, no schema change.

## Consequences

- `MedicationOrder` display code (`MO`) follows the new un-padded convention, but `Medications.Med_No` written on confirm still uses the legacy padded `M0000…` generator. Mixing prefixes is visible but harmless; unifying `M` to un-padded is deferred (scope: ordering, not history renumbering).
- `Medications` rows are copies of order items — no FK back to `MedicationOrder`, so lineage is via `(Pt_No, Drug_No, StartDate)` similarity only; a future `Source_Order_No` column would require an ADR-approved schema addition.
- `StockMovement` is not written on dispense; wiring dispense to stock decrements is explicitly deferred (mirrors the existing gap noted in ADR-0007).
- Order numbers are sequential per table (max+1) with no gap guarantee under concurrent inserts; unique PK prevents duplicates, retry logic is the same best-effort approach used for `PT/A/IP`.
