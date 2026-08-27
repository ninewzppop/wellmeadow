# ADR-0010: Ward expansion to 17 wards (14 beds each, preserve WD01-WD07)

Date: 2026-08-27

## Context
Request: "เพิ่มวอร์ดพร้อมเตียง รวมวอร์ดเดิมเป็น 17 วอร์ด วอร์ดละ 14 เตียง ไม่ต้องแก้ข้อมูลวอร์ดเก่า". Existing state was 7 wards (WD01-WD07) totalling 100 beds (WD01-WD05=14, WD06-WD07=15). Prior decision (WdSeeder comment "ลบ WD08/WD09 ตามคำขอ") deleted WD08/WD09. New requirement is 17 wards total, 14 beds per ward nominal, but explicitly must not modify old ward data.

Grilling pinned two key decisions:
1. WD06/W07 currently have 15 beds — keep at 15 vs. reduce to 14 for uniformity. Decision: keep at 15 (preserve old data).
2. Names/locations for WD08-WD17 were undefined — proposed and confirmed: WD08 Intensive Care (Block D F2 2402), WD09 Emergency (Block E F1 2501), WD10 Oncology (Block E F2 2502), WD11 Rehabilitation (Block F F1 2601), WD12 Psychiatry (Block F F2 2602), WD13 Dermatology (Block G F1 2701), WD14 Ophthalmology (Block G F2 2702), WD15 ENT (Block H F1 2801), WD16 Urology (Block H F2 2802), WD17 Geriatrics (Block I F1 2901).

## Decision
- Keep WD01-WD07 rows exactly as before (`updateOrInsert` on PK, no rename/location/TotalBeds change). WD06/W07 remain TotalBeds=15.
- Add WD08-WD17 via WdSeeder `updateOrInsert`, each TotalBeds=14, TelExtension sequential as above.
- BedSeeder: generate beds per pattern `Bed_No = wardNum*100 + n` (e.g. WD08 801-814, WD10 1001-1014 ... WD17 1701-1714), BedStatus='Available', via `updateOrInsert`. Total becomes 240 beds (70 +30 +140).
- Remove WD08/WD09 deletion logic from both seeders; replace with cleanup of WD18+ only (guards against stray data beyond 17).
- Extend cleanup loop for base-bed `X00` to cover WD01-WD17.

## Rationale
- Satisfies "ไม่ต้องแก้ข้อมูลวอร์ดเก่า" — old wards untouched, so FKs from Stf, StfRota, Wardrequisitions, InPatient remain valid.
- Uniform 14 for new wards matches spec; exception for WD06/W07 documented explicitly to avoid silent data loss.
- Keeps Bed_No invariant `{wardNum}01..{wardNum}count` and Wd.TotalBeds sync (new wards) for dashboard/report queries.
- Idempotent seeders remain safe to re-run.

## Consequences
- `Wd` now 17 rows, `Bed` 240 rows after `migrate:fresh --seed`.
- WD06/W07 have 15 vs 14 elsewhere — noted in domain model; no schema change (Wd_No varchar 10, Bed_No varchar 10 still fits 4-digit 1001-1714).
- ADR-0001 hospital.sql replica constraint honored (no ALTER, only seed data).
- Future ward additions beyond WD17 must extend seeder arrays and possibly TelExtension/Location blocks.

## Alternatives considered
- Reduce WD06/W07 to 14 to achieve uniform 238 beds — rejected because it violates "don't edit old ward data" and would delete beds 615,715.
- Use generic names "Ward 8..17" — rejected after user confirmed proposed clinical names.
