# ADR-0007: Pharmaceutical & Stock pages on extended schema

- **Date**: 2026-08-26
- **Status**: Accepted

## Context

The system needs two inventory pages sharing one pattern:

1. **Pharmaceutical** (`/pharmacy`) — drugs dispensed to patients directly.
2. **Stock** (`/stock`) — medical supplies split into surgical / non-surgical.

Both need a top dashboard (total items, low-stock count, out-of-stock
count; pharma adds near-expiry count), a "restock urgently" list
(out-of-stock first, then low-stock), and a searchable/filterable table.
Both need create forms ("add new drug/item") and restock/adjust actions.
Verified before design: `Pharmaceutical(Drug_No PK, ..., QtyInStock,
ReorderLvl nullable)` has **no expiry column**, `CentralStock.ItemType`
held seeded values 'Consumable'/'Equipment', neither table tracks stock
movements, and the whole legacy schema has no audit/timestamp columns.

Hard constraint from project rules: existing columns are never modified
or removed; any schema change requires explicit owner approval.

## Decision

1. **Two approved schema additions** (owner-approved in interview):
   - `Pharmaceutical.ExpiryDate DATE NULL` — one expiry date per drug row
     (batch/lot tracking was considered and declined as too complex).
   - New table `StockMovement(id AI PK, Drug_No VARCHAR(10) NULL FK →
     Pharmaceutical, Item_No VARCHAR(10) NULL FK → CentralStock,
     QtyChange INT (signed), Note VARCHAR(255) NULL, Moved_By BIGINT NULL
     FK → users.id, MoveDate TIMESTAMP)` — exactly one of `Drug_No` /
     `Item_No` must be set per row (application-enforced).
   No other existing table or column is touched.
2. **Surgical/non-surgical reuses the existing `CentralStock.ItemType`
   column** with values 'Surgical'/'NonSurgical' (replacing the seeded
   'Consumable'/'Equipment' *data* via seeder update — data change only,
   no schema change). The filter dropdown reads this column.
3. **Search box covers Name + code + ItemType + Description** (LIKE,
   case-insensitive); status filter = {all, low, out, normal}; the Stock
   page additionally filters by surgical/non-surgical.
4. **Status logic lives in one place** — Eloquent accessors backed by a
   shared concern/service, never duplicated in controllers/views:
   - `out` : effective qty == 0
   - `low` : 0 < effective qty ≤ ReorderLvl
   - `normal`: effective qty > ReorderLvl
   where effective QtyInStock NULL counts as 0 and ReorderLvl NULL makes
   an item always `normal`.
   Pharma adds expiry statuses: `expired` (ExpiryDate < today) and
   `near-expiry` (today ≤ ExpiryDate ≤ today+90d), reported separately —
   expired items are never merged into the ≤90-day bucket.
5. **Restock & adjust-down both exist in v1 UI**: restock (+N) and
   adjust-down (-N with required Note). Each action updates `QtyInStock`
   in a transaction with exactly one `StockMovement` row recording who
   (`Moved_By` = auth user) and when (`MoveDate`). Movements originating
   outside these pages (Medications dispensing, Wardrequisitions) remain
   untracked — explicitly deferred.
6. **Create/edit forms require** QtyInStock ≥ 0 (default 0) and
   ReorderLvl ≥ 0 so new rows can never be status-ambiguous; legacy NULL
   rows keep rendering via the accessor fallbacks above.
7. **IDs auto-generated** continuing the existing scheme (`DR04…`,
   `IT04…`, prefix + zero-padded max numeric suffix increment).
8. **Delete is hard delete guarded by FK reality**: allowed when nothing
   references the row; FK violation surfaces as a friendly "in use"
   message, not a stack trace.
9. **Permissions**: every action is available to all authenticated users
   (same convention as Suppliers/Rooms/Allergies); the admin middleware
   stays reserved for `/users`.
10. **Seeding**: seeder/factory provides ≥ 15 rows per page mixing all
    statuses (normal, low, out; pharma also expired and near-expiry) so
    dashboards render every state during testing.

## Rationale

- Single-column expiry keeps `QtyInStock` authoritative without batch
  sync machinery this system's scope cannot justify yet.
- Reusing `ItemType` avoids schema churn while making the surgical split
  a pure data matter; search/filter semantics stay single-source.
- A shared signed-movement log gives "who restocked, when, how much"
  (the audit question the legacy schema never answers) at minimal cost,
  while leaving existing flows untouched.

## Consequences

- `StockMovement` grows unbounded over time — acceptable at this scale;
  retention/pruning is deferred until it bites.
- Outbound movements (dispensing/requisitions) still do not decrement
  stock; wiring them is future work requiring its own decision.
- Existing seeded ItemType values change meaning ('Consumable'/
  'Equipment' → 'Surgical'/'NonSurgical'); any report reading ItemType
  must expect the new vocabulary.
