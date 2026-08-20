<?php

namespace Database\Seeders;

use App\Models\Pos;
use Illuminate\Database\Seeder;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['Pos_No' => 'P001', 'Pos_Name' => 'Registered Nurse', 'SalaryScale' => 'Band 5'],
            ['Pos_No' => 'P002', 'Pos_Name' => 'Senior Nurse', 'SalaryScale' => 'Band 6'],
            ['Pos_No' => 'P003', 'Pos_Name' => 'Consultant Doctor', 'SalaryScale' => 'Consultant'],
            ['Pos_No' => 'P004', 'Pos_Name' => 'Junior Doctor', 'SalaryScale' => 'Band 4'],
            ['Pos_No' => 'P005', 'Pos_Name' => 'Administrator', 'SalaryScale' => 'Band 2'],
            ['Pos_No' => 'P006', 'Pos_Name' => 'IT Technician', 'SalaryScale' => 'Band 3'],
            ['Pos_No' => 'P007', 'Pos_Name' => 'Cleaner', 'SalaryScale' => 'Band 1'],
            ['Pos_No' => 'P008', 'Pos_Name' => 'Ward Manager', 'SalaryScale' => 'Band 7'],
        ];

        foreach ($positions as $pos) {
            Pos::firstOrCreate(['Pos_No' => $pos['Pos_No']], $pos);
        }
    }
}
