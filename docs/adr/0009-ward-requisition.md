# ADR-0009: Ward Requisition flow (multi-item + stock deduction)

- **Date**: 2026-08-27
- **Status**: Accepted

## Context

Legacy schema already had `Wardrequisitions(Wd_Req_No PK, Stf_No→Stf, Wd_No→Wd, DateOrd, DateRecv)` plus line tables `Itemrequest(Wd_Req_No, Item_No, QtyReq)` and `Drugrequest(Wd_Req_No, Drug_No, QtyReq)` (one requisition can have both supplies and drugs). The placeholder route `/requisitions` existed but had no logic. Requirements (A–D) demand: multi-item create with autocomplete + cost, queue pending, approve & deduct stock, receive with sign-off, and reports per ward + low-stock. Hard constraint: existing tables/columns must not be altered without approval; if new tables/columns are needed, ask first.

Grilling Q1 confirmed we may add two columns to `Wardrequisitions`: `status` (`Pending→Approved→Completed`; `Delivered` collapses into `Approved` until receipt) and `Received_By` (FK→Stf, signer at ward). Q2 confirmed quantity deduction formula `Units` is simply `QtyReq` per line (no per-day math), and `Cost = Σ QtyReq × CostPerUnit` from current `CentralStock/Pharmaceutical` (no snapshot).

## Decision

1. **One approved migration** (2026_08_27_000001):
   - `Wardrequisitions.status VARCHAR(20) DEFAULT 'Pending' INDEX`
   - `Wardrequisitions.Received_By VARCHAR(10) NULL FK→Stf`
   - Backfill: existing rows with `DateRecv NOT NULL → Completed`, else `Pending`.

2. **No new tables** – reuse `Itemrequest`/`Drugrequest` as line items; `Suppl_No` stays via the stock masters. Cost and description are live from `CentralStock/Pharmaceutical` (no denormalized snapshot).

3. **IDs**: `Wd_Req_No` auto `WR{n}` un-padded via `Wardrequisition::nextNo()` (`WR1`, `WR2`…), `DateOrd` defaults to today if blank.

4. **Flow**:
   - Create/Edit (Charge Nurse): `Wd_No + Stf_No + DateOrd + items[]` where each item is `ref = "ITEM:IT01" | "DRUG:DR01"` + `QtyReq` (≥1). Autocomplete is a grouped `<select>` (Surgical / Non-surgical / Pharmaceutical) with `data-cost/qty/name` for live stock & cost display; per-row `QtyReq > QtyInStock` shows warning, `Approve` later blocks. Form shows `Cost/Unit` and `Subtotal`, and a `Total cost` footer. Edit/destroy allowed only while `Pending`; update rewrites line tables in a transaction.
   - Queue (Store): `GET /requisitions` lists `status IN (Pending,Approved)` ordered by `DateOrd`, paginated, filterable by `ward/status`. Columns: `Wd_Req_No`, ward, requester, item count, total cost (live sum), status badge, `DateOrd`.
   - Detail (`GET /requisitions/{id}`) shows ward/requester, item & drug tables with `Cost/Unit`, `QtyReq`, `Subtotal`, `Total cost`, and stock badge `Low` when `QtyInStock ≤ ReorderLvl`. Actions: `Approve & Prepare` (Pending → Approved) does `SELECT … FOR UPDATE` on `CentralStock`/`Pharmaceutical`, checks `QtyInStock ≥ QtyReq` per line, then `QtyInStock -= QtyReq` and `StockMovement( QtyChange = -QtyReq, Note = "Approved requisition :no for :ward")`; low stock after deduction keeps the `Low` badge (no auto-reorder). `Confirm receipt` (Approved → Completed) requires `Received_By` + `DateRecv` and moves the requisition to history (queue hides `Completed`).
   - History (`GET /requisitions/history`) and Report (`GET /requisitions/report?ward=&date_from=&date_to=`) list `Completed` rows; report also shows low-stock lists (`QtyInStock ≤ ReorderLvl`) for supplies & drugs.

5. **Permissions**: any authenticated user may create/approve/receive (same as suppliers/pharmacy); no role gate – charge-nurse vs store distinction is UI-only.

## Rationale

- Adding only `status`/`Received_By` satisfies the 4-state workflow with minimal schema churn; reusing the two line tables avoids a new unified `requisition_items` table that would duplicate `Itemrequest`/`Drugrequest`.
- Grouped `<select>` with data-attributes gives autocomplete + stock visibility without a JS framework; live cost avoids snapshot complexity.
- Deduct-on-approve (not on-create, not on-receive) matches the physical hand-over point and provides the required stock-block and low-stock flag; `StockMovement` logging reuses the existing audit pattern from `InventoryController`.

## Consequences

- Historical `Total cost` is live, not snapshotted – price changes retroactively affect old requisitions display.
- `Received_By` is constrained to `Stf`; a non-staff receiver cannot be recorded without a `Stf` row.
- Low-stock flag is display-only; no automatic purchase order is created.
