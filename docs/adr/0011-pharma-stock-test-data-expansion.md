# ADR-0011: Pharma & Stock test-data expansion (+10+10)

Date: 2026-08-27

## Context
Request: "เพิ่มข้อมูล phamacy กับ stock ประมาณ 10-20 รายการ ไว้ใช้ในการทดสอบ". Existing seed was 17 drugs (DR01-DR17) + 16 supplies (IT01-IT16) = 33 items (SuppliesSeeder). Grilling pinned:

- Q1: จำนวน — เพิ่มใหม่ 10 drugs + 10 supplies = 20 รายการ คงของเดิมไว้ รวม 53 รายการ (ไม่แทนที่)
- Q2: รายละเอียด — ใช้ SUP01/SUP02 เดิมสลับกัน, ชื่อทั่วไปสมจริง, คละเคสทดสอบ (stock status out/low/normal, expiry expired/near/ok/no-expiry, ItemType surgical/non-surgical)

## Decision
- Keep all existing 33 rows (updateOrInsert on PK, no delete).
- Add in `SuppliesSeeder::stockItems()` IT17-IT26 (10):
  - IT17 Ventilator Tubing surgical 5/10 low SUP01
  - IT18 Alcohol Swabs non-surgical 600/200 normal SUP02
  - IT19 Surgical Scissors surgical 0/8 out SUP01
  - IT20 Disposable Gowns non-surgical 30/40 low SUP02
  - IT21 ECG Electrodes non-surgical 150/50 normal SUP02
  - IT22 Bone Drill Kit surgical 2/5 low SUP01
  - IT23 Specimen Containers non-surgical 80/30 normal SUP01
  - IT24 Laparoscopic Trocar surgical 0/12 out SUP02
  - IT25 Pulse Oximeter non-surgical 12/10 normal SUP02
  - IT26 Surgical Stapler surgical 7/10 low SUP01
  -> Total stock 26: surgical 13 / non-surgical 13, out 5, low 8, normal 13

- Add in `SuppliesSeeder::drugs()` DR18-DR27 (10):
  - DR18 Ciprofloxacin 500mg Oral 0/25 out 120d ok SUP02
  - DR19 Prednisolone 5mg Oral 18/20 low 20d near SUP02
  - DR20 Atropine 1mg/ml Injection 45/30 normal 500d ok SUP01
  - DR21 Lorazepam 2mg Oral 6/10 low 5d near SUP01
  - DR22 Erythromycin 250mg Oral 200/80 normal -30d expired SUP02
  - DR23 Midazolam 5mg/ml Injection 33/15 normal 80d near SUP01
  - DR24 Saline Solution 0.9% IV 0/50 out null no-expiry SUP01
  - DR25 Losartan 50mg Oral 95/40 normal 400d ok SUP02
  - DR26 Tramadol 50mg Oral 28/30 low 60d near SUP02
  - DR27 Chlorphenamine 10mg/ml Injection 110/50 normal -5d expired SUP01
  -> Total pharma 27: out 4, low 7, normal 16, expired 4, near 10, ok 10, no-expiry 3

- Distribution SUP01/SUP02 preserved roughly balanced; CostPerUnit, Dosage, AdminMethod filled realistically.
- No schema change; only seed data. StockMovement seed unchanged (3 demo rows).

## Rationale
- Preserves FKs from Medications/PatientAllergy/Itemrequest/Wardrequisitions (DR01-17/IT01-16 still referenced).
- Diverse statuses enable testing of filters (status, expiry, surgical), urgent-restock ordering, restock/adjust flows, dispensing blocked when out-of-stock, and requisition approve blocked.
- Idempotent `updateOrInsert` keeps re-seed safe.

## Consequences
- `SuppliesSeeder` now yields 53 inventory masters; `migrate:fresh --seed` reflects this.
- Dashboard /reports will show higher counts; no breaking change for existing code (accessors handle new rows).
- Future test data should extend DR28+, IT27+ to avoid PK collision.

## Alternatives considered
- Replace instead of add — rejected (would break existing FK references and lose regression coverage).
- Add only 10 total — rejected per user choice of 10+10=20.
