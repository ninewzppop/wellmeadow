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

        DB::table('Medications')->updateOrInsert(['Med_No' => 'M01'], [
            'Pt_No' => 'PT002', 'Stf_No' => 'S1002', 'Drug_No' => 'DR01',
            'UnitsPerDay' => 4, 'AdminMethod' => 'Oral', 'StartDate' => '2026-08-10', 'FinishDate' => '2026-08-24',
        ]);
        DB::table('Medications')->updateOrInsert(['Med_No' => 'M02'], [
            'Pt_No' => 'PT001', 'Stf_No' => 'S1002', 'Drug_No' => 'DR03',
            'UnitsPerDay' => 2, 'AdminMethod' => 'Injection', 'StartDate' => '2026-08-01', 'FinishDate' => '2026-08-31',
        ]);

        DB::table('PatientAllergy')->updateOrInsert(['Allergy_No' => 'AL01'], [
            'Pt_No' => 'PT001', 'Drug_No' => 'DR02', 'Allergy_Name' => 'Penicillin',
            'Reaction' => 'Rash', 'Severity' => 'Moderate', 'DiagDate' => '2026-01-15', 'Rec_Stf_No' => 'S1002',
        ]);
    }

    private function stockItems(): array
    {
        // [Item_No, Name, ItemType, Description, QtyInStock, ReorderLvl, CostPerUnit, Suppl_No]
        return [
            ['IT01', 'Surgical Gloves', 'Surgical', 'Latex-free gloves (box of 100)', 120, 50, 6.50, 'SUP01'],
            ['IT02', 'Bandages', 'NonSurgical', 'Cotton bandage 10cm', 200, 80, 1.20, 'SUP01'],
            ['IT03', 'Scalpel Set', 'Surgical', 'Sterile disposable scalpel set', 0, 15, 9.80, 'SUP01'],
            ['IT04', 'Syringe 5ml', 'NonSurgical', 'Disposable syringe 5ml', 45, 50, 0.35, 'SUP01'],
            ['IT05', 'IV Catheter', 'Surgical', 'Intravenous catheter 20G', 300, 100, 1.75, 'SUP02'],
            ['IT06', 'Gauze Pads', 'NonSurgical', 'Sterile gauze pads 10x10cm', 8, 20, 0.90, 'SUP01'],
            ['IT07', 'Face Shields', 'NonSurgical', 'Full-face protective shields', 500, 150, 2.40, 'SUP02'],
            ['IT08', 'Suture Kit', 'Surgical', 'Basic wound closure kit', 0, 10, 14.50, 'SUP01'],
            ['IT09', 'Blood Pressure Monitor', 'NonSurgical', 'Digital upper-arm monitor', 25, 5, 24.00, 'SUP02'],
            ['IT10', 'Sterile Drapes', 'Surgical', 'Operating field drapes', 18, 25, 4.20, 'SUP01'],
            ['IT11', 'Oxygen Masks', 'NonSurgical', 'Adult oxygen mask with tubing', 90, 40, 3.10, 'SUP02'],
            ['IT12', 'Forceps', 'Surgical', 'Stainless tissue forceps', 60, 30, 7.60, 'SUP01'],
            ['IT13', 'Urinary Catheter', 'NonSurgical', 'Foley catheter 16Fr', 3, 15, 5.40, 'SUP02'],
            ['IT14', 'Needle Holder', 'Surgical', 'Mayo-Hegar needle holder 16cm', 22, 8, 11.90, 'SUP01'],
            ['IT15', 'Wheelchair', 'NonSurgical', 'Folding transit wheelchair', 4, 2, 95.00, 'SUP02'],
            ['IT16', 'Chest Drain Kit', 'Surgical', 'Tube thoracostomy kit', 0, 6, 32.00, 'SUP01'],
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
        ];
    }
}
