# ADR-0001: Replace hospital.sql with Laravel migrations

- **Date**: 2026-08-19
- **Status**: Accepted

## Context

The database schema lived in `hospital.sql` (23 tables), loaded by a single
raw `DB::unprepared()` migration. This made the schema unversioned per-table,
hard to inspect, and divergent from the Laravel migration workflow.

## Decision

Convert every `CREATE TABLE` in `hospital.sql` into its own Laravel migration:

- One migration per table, named `create_<table>_table.php`.
- Ordering by foreign-key dependency (no-FK tables first, FK tables after).
- Column types mapped: `varchar(n)` → `string(n)`, `int` → `integer()`,
  `smallint` → `smallInteger()`, `decimal(p,s)` → `decimal(p,s)`,
  `date` → `date()`, `time` → `time()`, `char(n)` → `char(n)`.
- `PRIMARY KEY` → `primary()`; composite PKs → `primary([...])`; `UNIQUE` → `unique()`.
- `FOREIGN KEY` → `foreign()->references()->on()`; non-PK columns nullable (no `NOT NULL` in source).
- Each migration has `up()`/`down()`.

The old `import_hospital_schema` migration and `hospital.sql` were deleted.

## Rationale

- Per-table, ordered migrations are reviewable and match Laravel conventions.
- Keeps the exact source schema (23 tables, 30 FKs, column sizes) as the source of truth.
- The site's ward features were refactored to the schema's own structures:
  `StfRota` for the shift roster/report and `Stf.Alloc_Wd_No` for the primary
  ward; the custom `StfWd` pivot was removed.

## Consequences

- Schema is fully managed by `php artisan migrate`.
- Site features (staff CRUD, search, allocations, ward report) now use
  `StfRota` + `Stf.Alloc_Wd_No`.
- Data can be regenerated with `migrate:fresh --seed`.