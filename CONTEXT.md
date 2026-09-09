# CONTEXT.md — wellmeadow (stateful memory for grill-with-docs)

> Created by `/setup-matt-pocock-skills`. `grill-with-docs` appends here.
> Keep it a clean glossary + decision log, not a dump.

## Project

- Laravel 11, PHP 8.3, MySQL 8.4 via Laravel Sail (`compose.yaml`: `laravel.test` + `mysql` + `phpmyadmin`).
- DB commands run via `sail artisan`, never bare `php artisan`.
- Source of truth schema: per-table migrations in `database/migrations/` (converted from `hospital.sql`, ADR-0001). 23 base tables + Laravel defaults + feature additions (StockMovement, MedicationOrder*, users.role/stf_no, appointment.status, ward indexes).
- Run `sail artisan migrate:fresh --seed` to regenerate. Seeds in `database/seeders/`.

## Vocabulary (pointer — full definitions live elsewhere)

- Full glossary: `docs/glossary.md`
- Domain model (entities, invariants, persistence map): `docs/domain-model.md`
- ADRs: `docs/adr/` (0001 migrations … 0012 RBAC). New hard-to-reverse decisions → new ADR file.
- `domain-modeling` is the single source of truth for domain words; `grill-with-docs` drives it.

## Tracker / triage (pointer)

- Tracker config + label vocabulary: `docs/agents/issue-tracker.md`
- Local tickets: `.scratch/<feature-slug>/issues/<NN>-<slug>.md` with `Blocked by` edges + `Status: ready-for-agent`.

## Active decisions

- DB audit 2026-09-10 (grill-with-docs): Requirements source = PDF Appendix B Wellmeadows case study (Data B.3.1 + Transactions (a)-(n) B.3.2). `sail artisan migrate:fresh --seed` runnable (mysql healthy). Audit result: 35 migrations pass in FK order, 9/9 audit tests pass (168 asserts). Transactions (a)-(n) all mappable, no missing table/column. Known smells (non-blocking): `InPatient.Bed_No` varchar(255) vs `Bed` varchar(10), `Medications/MedicationOrderItem.Drug_No` varchar(30) vs `Pharmaceutical` varchar(10); nullable qty/dates; `MedicationOrder*` intentionally unseeded (queue tables, covered by workflow test).
