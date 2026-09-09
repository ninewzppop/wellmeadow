## 1. Supplier (New supplier) — กรอกก่อน เพราะ Stock/ยา ต้องเลือก

**ชุดที่ 1**
- Name: `Bangkok Medical Supply`
- Address: `99 Rama 4 Rd, Bangkok`
- Tel No: `02-123-4567`
- Fax No: `02-123-4568`

**ชุดที่ 2**
- Name: `Siam Pharma Co.`
- Address: `88 Sukhumvit 21, Bangkok`
- Tel No: `02-987-6543`
- Fax No: *(ว่างได้)*

## 2. Stock Item (New item) — ต้อง login สิทธิ์ director/admin

**ชุดที่ 1**
- Name: `Alcohol Swabs Test`
- Category: `non-surgical`
- Supplier: เลือก `Bangkok Medical Supply` (ที่เพิ่งเพิ่ม)
- Description: `Test item for manual testing`
- Qty in stock: `100`
- Reorder level: `20`
- Cost per unit: `0.50`

**ชุดที่ 2**
- Name: `Test Scalpel`
- Category: `surgical`
- Supplier: `Siam Pharma Co.`
- Description: *(ว่างได้)*
- Qty in stock: `0`
- Reorder level: `10`
- Cost per unit: `15.00`

## 3. Drug (New drug) — ต้อง login สิทธิ์ director/admin

**ชุดที่ 1**
- Name: `Test Paracetamol`
- Supplier: `Siam Pharma Co.`
- Dosage: `500mg`
- Admin method: `Oral`
- Expiry date: `2027-12-31`
- Description: `Manual test drug`
- Qty in stock: `200`
- Reorder level: `50`
- Cost per unit: `0.10`

**ชุดที่ 2** (ทดสอบยาใกล้หมดอายุ)
- Name: `Test Adrenaline`
- Supplier: `Bangkok Medical Supply`
- Dosage: `1mg/ml`
- Admin method: `Injection`
- Expiry date: `2026-09-20`
- Qty in stock: `5`
- Reorder level: `10`
- Cost per unit: `9.90`

## 4. Local Doctor (New local doctor)

**ชุดที่ 1**
- First name: `Somchai`
- Last name: `Jaidee`
- Address: `12 Phetchaburi Rd, Bangkok`
- Tel No: `081-111-2222`

**ชุดที่ 2**
- First name: `Malee`
- Last name: `Sukjai`
- Address: `34 Silom Rd, Bangkok`
- Tel No: `081-333-4444`

## 5. Staff (New staff member)

**ชุดที่ 1**
- First name: `Nidnoi`
- Last name: `Teststaff`
- National Insurance No: *(ว่างได้)*
- Date of birth: `1990-05-15`
- Sex: `F`
- Assigned ward: `WD01`
- Address: `56 Rama 9 Rd, Bangkok`
- Telephone: `082-555-6666`

**ชุดที่ 2**
- First name: `Mana`
- Last name: `Testdoctor`
- Date of birth: `1985-11-20`
- Sex: `M`
- Assigned ward: `WD03`
- Address: *(ว่างได้)*
- Telephone: `083-777-8888`

## 6. Patient (Register Patient)

**ชุดที่ 1** (มีญาติ + หมอประจำ)
- Date Registered: `2026-09-10`
- First Name: `Somsak`
- Last Name: `Testpatient`
- Date of Birth: `1975-03-12`
- Sex: `M`
- Address: `78 Ladprao Rd, Bangkok`
- Telephone: `084-111-2233`
- Marital Status: `Married`
- Local Doctor: พิมพ์ค้น `Somchai` แล้วเลือก
- Next of Kin — Full Name: `Somying Testpatient`
- Next of Kin — Relationship: `Wife`
- Next of Kin — Address: `78 Ladprao Rd, Bangkok`
- Next of Kin — Telephone: `084-444-5566`

**ชุดที่ 2** (ขั้นต่ำ: แค่ชื่อก็บันทึกได้)
- First Name: `Wilai`
- Last Name: `Testtwo`
- นอกนั้นว่างทั้งหมด

**ชุดที่ 3**
- Date Registered: `2026-09-11`
- First Name: `Prasert`
- Last Name: `Testthree`
- Date of Birth: `1960-08-25`
- Sex: `M`
- Telephone: `085-999-0011`
- Marital Status: `Widowed`
- Local Doctor: พิมพ์ค้น `Malee` แล้วเลือก

## 7. Allergy (New Allergy Record)

**ชุดที่ 1** (ผูกกับ Patient ชุดที่ 1)
- Diagnosed Date: `2026-09-01`
- Patient: พิมพ์ค้น `Somsak` แล้วเลือก
- Recorded By: เลือก `James Wong (S1002)`
- Allergen: เลือก `Paracetamol (DR01)`
- Reaction: `Rash and itching on arms`
- Severity: `Moderate`

**ชุดที่ 2** (ไม่ผูก Patient — ทดสอบเคสว่างได้)
- Diagnosed Date: `2026-09-05`
- Patient: *(ว่าง)*
- Recorded By: *(ว่าง)*
- Allergen: เลือก `Amoxicillin (DR02)`
- Reaction: `Swelling of lips`
- Severity: `Severe`

> หมายเหตุ: ชื่อสารก่อภูมิแพ้จะตามยาที่เลือกอัตโนมัติ

## 8. Appointment (New Appointment)

**ชุดที่ 1**
- Patient: พิมพ์ค้น `Somsak` แล้วเลือก
- Doctor: เลือก `James Wong (S1002)`
- Room: เลือก `Consulting Room 1 (R001)`
- Status: `scheduled`
- Appointment Date: `2026-09-15`
- Appointment Time: `09:30`

**ชุดที่ 2** (พร้อมบันทึก Allergy ตอนจอง)
- Patient: พิมพ์ค้น `Wilai` แล้วเลือก
- Doctor: เลือก `Rachel Kim (S1006)`
- Room: เลือก `Treatment Room (R002)`
- Status: `scheduled`
- Appointment Date: `2026-09-16`
- Appointment Time: `13:00`
- Allergy Details (พาเนลเหลือง):
  - Allergen: `Amoxicillin (DR02)`
  - Reaction: `Hives after taking antibiotic`
  - Severity: `Mild`
  - Diagnosed Date: `2026-09-12`
  - Recorded By: `James Wong (S1002)`

**ชุดที่ 3**
- Patient: พิมพ์ค้น `Prasert` แล้วเลือก
- Doctor: เลือก `James Wong (S1002)`
- Room: เลือก `Consulting Room 1 (R001)`
- Status: `scheduled`
- Appointment Date: `2026-09-15`
- Appointment Time: `11:00`

## 9. Admission (New Admission)

**ชุดที่ 1**
- Patient: พิมพ์ค้น `Prasert` แล้วเลือก
- Ward: เลือก `WD01`
- Bed: เลือกเตียงที่ว่าง (เลือกหลัง Ward)
- Wait List Date: `2026-09-10`
- Expected Stay (Days): `5`
- Admission Date: `2026-09-12`
- Expected Leave Date: `2026-09-17`
- Actual Leave Date: *(ว่าง — ยังไม่ออก)*

**ชุดที่ 2** (ขั้นต่ำ: แค่ Patient)
- Patient: พิมพ์ค้น `Wilai` แล้วเลือก
- นอกนั้นว่างทั้งหมด

> เงื่อนไข: Expected Leave ต้องไม่ก่อน Admission Date

## 10. Requisition (New Requisition — เบิกของเข้าวอร์ด)

**ชุดที่ 1**
- Ward: เลือก `WD01`
- Requested by: เลือก `Priya Patel (S1003)`
- Date Ordered: `2026-09-10`
- กด `+ Add item` แล้วเพิ่ม 2 แถว:
  - แถว 1: Item = `Surgical Gloves (IT01)`, Qty required = `10`
  - แถว 2: Item = `Paracetamol (DR01)`, Qty required = `50`
- กด `Submit requisition`

## 11. Rota (จัดเวร — หน้า Rota)

- Staff: เลือก Staff ที่เพิ่มในข้อ 5 (พิมพ์ค้นชื่อ)
- Ward: `WD01`
- Week Beginning: `2026-09-14` (วันจันทร์)
- Shift: `Morning`

## 12. โฟลว์ห้องตรวจ (Room queue — ทดสอบสถานะนัดหมาย)

1. เปิดหน้า Rooms → เลือก `Consulting Room 1` → วันที่ `2026-09-15`
2. กด Start ที่นัดของ `Somsak` (scheduled → in consultation)
3. สั่งยา (Medication order): Drug = `Paracetamol (DR01)`, Units/Day = `3`, Method = `Oral`, Start = `2026-09-15`, Finish = `2026-09-22`
4. กด Admit (รับแอดมิด): ExpStayDays = `3`
5. กด Complete (จบเคส)
