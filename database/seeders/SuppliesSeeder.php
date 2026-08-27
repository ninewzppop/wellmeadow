<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('Supplier')->updateOrInsert(['Suppl_No' => 'SUP01'], [
            'Name' => 'MedSupply Ltd', 'Address' => '1 Industrial Estate, Bristol', 'TelNo' => '0117 555 0400', 'FaxNo' => '0117 555 0401',
        ]);
        DB::table('Supplier')->updateOrInsert(['Suppl_No' => 'SUP02'], [
            'Name' => 'PharmaCare', 'Address' => '44 Trade Park, Bath', 'TelNo' => '01225 555 0410', 'FaxNo' => '01225 555 0411',
        ]);

        foreach ($this->stockItems() as [$no, $name, $type, $desc, $qty, $reorder, $cost, $suppl]) {
            DB::table('CentralStock')->updateOrInsert(['Item_No' => $no], [
                'Name' => $name, 'ItemType' => $type, 'Description' => $desc,
                'QtyInStock' => $qty, 'ReorderLvl' => $reorder, 'CostPerUnit' => $cost, 'Suppl_No' => $suppl,
            ]);
        }

        foreach ($this->drugs() as [$no, $name, $desc, $dosage, $method, $qty, $reorder, $cost, $suppl, $expiryDays]) {
            DB::table('Pharmaceutical')->updateOrInsert(['Drug_No' => $no], [
                'Name' => $name, 'Description' => $desc, 'Dosage' => $dosage,
                'AdminMethod' => $method, 'QtyInStock' => $qty, 'ReorderLvl' => $reorder,
                'CostPerUnit' => $cost, 'Suppl_No' => $suppl,
                'ExpiryDate' => $expiryDays === null ? null : now()->addDays($expiryDays)->toDateString(),
            ]);
        }

        $userId = DB::table('users')->value('id');

        DB::table('StockMovement')->insert([
            ['Drug_No' => 'DR01', 'Item_No' => null, 'QtyChange' => 100, 'Note' => 'Monthly delivery', 'Moved_By' => $userId, 'MoveDate' => now()->subDays(3)],
            ['Drug_No' => 'DR03', 'Item_No' => null, 'QtyChange' => -5, 'Note' => 'Damaged vials', 'Moved_By' => $userId, 'MoveDate' => now()->subDays(1)],
            ['Drug_No' => null, 'Item_No' => 'IT01', 'QtyChange' => 50, 'Note' => 'Supplier restock', 'Moved_By' => $userId, 'MoveDate' => now()->subDays(2)],
        ]);

        DB::table('Wardrequisitions')->updateOrInsert(['Wd_Req_No' => 'WR01'], [
            'Stf_No' => 'S1003', 'Wd_No' => 'WD01', 'DateOrd' => '2026-08-18', 'DateRecv' => '2026-08-19',
        ]);
        DB::table('Wardrequisitions')->updateOrInsert(['Wd_Req_No' => 'WR02'], [
            'Stf_No' => 'S1001', 'Wd_No' => 'WD02', 'DateOrd' => '2026-08-19', 'DateRecv' => null,
        ]);

        DB::table('Itemrequest')->updateOrInsert(['Wd_Req_No' => 'WR01', 'Item_No' => 'IT01'], ['QtyReq' => 20]);
        DB::table('Itemrequest')->updateOrInsert(['Wd_Req_No' => 'WR02', 'Item_No' => 'IT09'], ['QtyReq' => 5]);

        DB::table('Drugrequest')->updateOrInsert(['Wd_Req_No' => 'WR01', 'Drug_No' => 'DR01'], ['QtyReq' => 50]);
        DB::table('Drugrequest')->updateOrInsert(['Wd_Req_No' => 'WR02', 'Drug_No' => 'DR02'], ['QtyReq' => 30]);

        // Medications / PatientAllergy moved to ClinicalSeeder (need Patient FK) — see ClinicalSeeder
    }

    private function stockItems(): array
    {
        // [Item_No, Name, ItemType, Description, QtyInStock, ReorderLvl, CostPerUnit, Suppl_No]
        return [
            ['IT01', 'Surgical Gloves', 'surgical', 'Latex-free gloves (box of 100)', 120, 50, 6.50, 'SUP01'],
            ['IT02', 'Bandages', 'non-surgical', 'Cotton bandage 10cm', 200, 80, 1.20, 'SUP01'],
            ['IT03', 'Scalpel Set', 'surgical', 'Sterile disposable scalpel set', 0, 15, 9.80, 'SUP01'],
            ['IT04', 'Syringe 5ml', 'non-surgical', 'Disposable syringe 5ml', 45, 50, 0.35, 'SUP01'],
            ['IT05', 'IV Catheter', 'surgical', 'Intravenous catheter 20G', 300, 100, 1.75, 'SUP02'],
            ['IT06', 'Gauze Pads', 'non-surgical', 'Sterile gauze pads 10x10cm', 8, 20, 0.90, 'SUP01'],
            ['IT07', 'Face Shields', 'non-surgical', 'Full-face protective shields', 500, 150, 2.40, 'SUP02'],
            ['IT08', 'Suture Kit', 'surgical', 'Basic wound closure kit', 0, 10, 14.50, 'SUP01'],
            ['IT09', 'Blood Pressure Monitor', 'non-surgical', 'Digital upper-arm monitor', 25, 5, 24.00, 'SUP02'],
            ['IT10', 'Sterile Drapes', 'surgical', 'Operating field drapes', 18, 25, 4.20, 'SUP01'],
            ['IT11', 'Oxygen Masks', 'non-surgical', 'Adult oxygen mask with tubing', 90, 40, 3.10, 'SUP02'],
            ['IT12', 'Forceps', 'surgical', 'Stainless tissue forceps', 60, 30, 7.60, 'SUP01'],
            ['IT13', 'Urinary Catheter', 'non-surgical', 'Foley catheter 16Fr', 3, 15, 5.40, 'SUP02'],
            ['IT14', 'Needle Holder', 'surgical', 'Mayo-Hegar needle holder 16cm', 22, 8, 11.90, 'SUP01'],
            ['IT15', 'Wheelchair', 'non-surgical', 'Folding transit wheelchair', 4, 2, 95.00, 'SUP02'],
            ['IT16', 'Chest Drain Kit', 'surgical', 'Tube thoracostomy kit', 0, 6, 32.00, 'SUP01'],
            // เพิ่ม 10 รายการใหม่สำหรับทดสอบ (คละ surgical/non-surgical + out/low/normal)
            ['IT17', 'Ventilator Tubing', 'surgical', 'Disposable ventilator circuit tubing', 5, 10, 18.50, 'SUP01'],
            ['IT18', 'Alcohol Swabs', 'non-surgical', 'Isopropyl alcohol prep pads (box 200)', 600, 200, 0.12, 'SUP02'],
            ['IT19', 'Surgical Scissors', 'surgical', 'Curved Metzenbaum scissors 14cm', 0, 8, 12.00, 'SUP01'],
            ['IT20', 'Disposable Gowns', 'non-surgical', 'Isolation gowns level 2 (pack 50)', 30, 40, 3.80, 'SUP02'],
            ['IT21', 'ECG Electrodes', 'non-surgical', 'Disposable ECG electrodes (pack 50)', 150, 50, 1.10, 'SUP02'],
            ['IT22', 'Bone Drill Kit', 'surgical', 'Orthopaedic bone drill set', 2, 5, 145.00, 'SUP01'],
            ['IT23', 'Specimen Containers', 'non-surgical', 'Sterile 60ml specimen pots', 80, 30, 0.95, 'SUP01'],
            ['IT24', 'Laparoscopic Trocar', 'surgical', 'Disposable 12mm trocar', 0, 12, 28.00, 'SUP02'],
            ['IT25', 'Pulse Oximeter', 'non-surgical', 'Fingertip pulse oximeter', 12, 10, 18.00, 'SUP02'],
            ['IT26', 'Surgical Stapler', 'surgical', 'Skin stapler 35W', 7, 10, 42.00, 'SUP01'],
        ];
    }

    private function drugs(): array
    {
        // [Drug_No, Name, Description, Dosage, AdminMethod, QtyInStock, ReorderLvl, CostPerUnit, Suppl_No, ExpiryDaysFromNow]
        return [
            ['DR01', 'Paracetamol', '500mg tablets', '500mg', 'Oral', 500, 200, 0.05, 'SUP02', null],
            ['DR02', 'Amoxicillin', 'Antibiotic capsules', '250mg', 'Oral', 300, 100, 0.30, 'SUP02', 400],
            ['DR03', 'Insulin', 'Insulin injection', '100 IU/ml', 'Injection', 80, 30, 12.00, 'SUP01', 60],
            ['DR04', 'Ibuprofen', 'Anti-inflammatory tablets', '400mg', 'Oral', 150, 60, 0.08, 'SUP02', 730],
            ['DR05', 'Morphine', 'Opioid analgesic injection', '10mg/ml', 'Injection', 25, 10, 8.50, 'SUP01', 30],
            ['DR06', 'Aspirin', 'Low-dose dispersible tablets', '75mg', 'Oral', 0, 40, 0.03, 'SUP02', 200],
            ['DR07', 'Metformin', 'Antidiabetic tablets', '850mg', 'Oral', 55, 60, 0.12, 'SUP02', 365],
            ['DR08', 'Salbutamol Inhaler', 'Bronchodilator inhaler', '100mcg/dose', 'Inhalation', 0, 15, 5.20, 'SUP01', 90],
            ['DR09', 'Ceftriaxone', 'Antibiotic injection', '1g', 'Injection', 12, 20, 6.80, 'SUP01', -10],
            ['DR10', 'Warfarin', 'Anticoagulant tablets', '5mg', 'Oral', 90, 30, 0.22, 'SUP02', -45],
            ['DR11', 'Omeprazole', 'Proton pump inhibitor capsules', '20mg', 'Oral', 210, 80, 0.15, 'SUP02', 15],
            ['DR12', 'Diazepam', 'Anxiolytic tablets', '5mg', 'Oral', 35, 15, 0.40, 'SUP01', 180],
            ['DR13', 'Furosemide', 'Diuretic tablets', '40mg', 'Oral', 48, 50, 0.07, 'SUP02', 250],
            ['DR14', 'Heparin', 'Anticoagulant injection', '5000 IU', 'Injection', 70, 25, 3.90, 'SUP01', 85],
            ['DR15', 'Cetirizine', 'Antihistamine tablets', '10mg', 'Oral', 260, 100, 0.06, 'SUP02', 600],
            ['DR16', 'Adrenaline', 'Emergency anaphylaxis injection', '1mg/ml', 'Injection', 9, 12, 9.90, 'SUP01', 7],
            ['DR17', 'Diclofenac Gel', 'Topical anti-inflammatory gel', '1%', 'Topical', 140, 50, 2.80, 'SUP02', null],
            // เพิ่ม 10 รายการใหม่สำหรับทดสอบ (คละ out/low/normal + expired/near/ok/no-expiry)
            ['DR18', 'Ciprofloxacin', 'Fluoroquinolone antibiotic', '500mg', 'Oral', 0, 25, 0.45, 'SUP02', 120],
            ['DR19', 'Prednisolone', 'Corticosteroid tablets', '5mg', 'Oral', 18, 20, 0.18, 'SUP02', 20],
            ['DR20', 'Atropine', 'Antimuscarinic injection', '1mg/ml', 'Injection', 45, 30, 7.20, 'SUP01', 500],
            ['DR21', 'Lorazepam', 'Sedative tablets', '2mg', 'Oral', 6, 10, 0.65, 'SUP01', 5],
            ['DR22', 'Erythromycin', 'Macrolide antibiotic', '250mg', 'Oral', 200, 80, 0.25, 'SUP02', -30],
            ['DR23', 'Midazolam', 'Sedative injection', '5mg/ml', 'Injection', 33, 15, 4.10, 'SUP01', 80],
            ['DR24', 'Saline Solution', 'IV fluid 0.9% sodium chloride', '0.9%', 'IV', 0, 50, 1.50, 'SUP01', null],
            ['DR25', 'Losartan', 'Antihypertensive tablets', '50mg', 'Oral', 95, 40, 0.09, 'SUP02', 400],
            ['DR26', 'Tramadol', 'Opioid analgesic capsules', '50mg', 'Oral', 28, 30, 0.32, 'SUP02', 60],
            ['DR27', 'Chlorphenamine', 'Antihistamine injection', '10mg/ml', 'Injection', 110, 50, 2.10, 'SUP01', -5],
        ];
    }
}
