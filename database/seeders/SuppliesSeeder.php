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

        DB::table('CentralStock')->updateOrInsert(['Item_No' => 'IT01'], [
            'Name' => 'Surgical Gloves', 'ItemType' => 'Consumable', 'Description' => 'Latex-free gloves (box of 100)',
            'QtyInStock' => 120, 'ReorderLvl' => 50, 'CostPerUnit' => 6.50, 'Suppl_No' => 'SUP01',
        ]);
        DB::table('CentralStock')->updateOrInsert(['Item_No' => 'IT02'], [
            'Name' => 'Bandages', 'ItemType' => 'Consumable', 'Description' => 'Cotton bandage 10cm',
            'QtyInStock' => 200, 'ReorderLvl' => 80, 'CostPerUnit' => 1.20, 'Suppl_No' => 'SUP01',
        ]);
        DB::table('CentralStock')->updateOrInsert(['Item_No' => 'IT03'], [
            'Name' => 'Thermometer', 'ItemType' => 'Equipment', 'Description' => 'Digital oral thermometer',
            'QtyInStock' => 40, 'ReorderLvl' => 10, 'CostPerUnit' => 4.75, 'Suppl_No' => 'SUP02',
        ]);

        DB::table('Pharmaceutical')->updateOrInsert(['Drug_No' => 'DR01'], [
            'Name' => 'Paracetamol', 'Description' => '500mg tablets', 'Dosage' => '500mg',
            'AdminMethod' => 'Oral', 'QtyInStock' => 500, 'ReorderLvl' => 200, 'CostPerUnit' => 0.05, 'Suppl_No' => 'SUP02',
        ]);
        DB::table('Pharmaceutical')->updateOrInsert(['Drug_No' => 'DR02'], [
            'Name' => 'Amoxicillin', 'Description' => 'Antibiotic 250mg capsules', 'Dosage' => '250mg',
            'AdminMethod' => 'Oral', 'QtyInStock' => 300, 'ReorderLvl' => 100, 'CostPerUnit' => 0.30, 'Suppl_No' => 'SUP02',
        ]);
        DB::table('Pharmaceutical')->updateOrInsert(['Drug_No' => 'DR03'], [
            'Name' => 'Insulin', 'Description' => 'Insulin injection 100 IU/ml', 'Dosage' => '100 IU/ml',
            'AdminMethod' => 'Injection', 'QtyInStock' => 80, 'ReorderLvl' => 30, 'CostPerUnit' => 12.00, 'Suppl_No' => 'SUP01',
        ]);

        DB::table('Wardrequisitions')->updateOrInsert(['Wd_Req_No' => 'WR01'], [
            'Stf_No' => 'S1003', 'Wd_No' => 'WD01', 'DateOrd' => '2026-08-18', 'DateRecv' => '2026-08-19',
        ]);
        DB::table('Wardrequisitions')->updateOrInsert(['Wd_Req_No' => 'WR02'], [
            'Stf_No' => 'S1001', 'Wd_No' => 'WD02', 'DateOrd' => '2026-08-19', 'DateRecv' => null,
        ]);

        DB::table('Itemrequest')->updateOrInsert(['Wd_Req_No' => 'WR01', 'Item_No' => 'IT01'], ['QtyReq' => 20]);
        DB::table('Itemrequest')->updateOrInsert(['Wd_Req_No' => 'WR02', 'Item_No' => 'IT03'], ['QtyReq' => 5]);

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
}
