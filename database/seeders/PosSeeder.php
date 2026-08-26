<?php

namespace Database\Seeders;

use App\Models\Pos;
use Illuminate\Database\Seeder;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['Pos_No' => 'P001', 'Pos_Name' => 'Medical Director', 'SalaryScale' => 'Band 8'],
            ['Pos_No' => 'P002', 'Pos_Name' => 'Personnel Officer', 'SalaryScale' => 'Band 6'],
            ['Pos_No' => 'P003', 'Pos_Name' => 'Charge Nurse', 'SalaryScale' => 'Band 6'],
            ['Pos_No' => 'P004', 'Pos_Name' => 'Senior Nurse', 'SalaryScale' => 'Band 6'],
            ['Pos_No' => 'P005', 'Pos_Name' => 'Junior Nurse', 'SalaryScale' => 'Band 5'],
            ['Pos_No' => 'P006', 'Pos_Name' => 'Doctor', 'SalaryScale' => 'Band 7'],
            ['Pos_No' => 'P007', 'Pos_Name' => 'Auxiliary', 'SalaryScale' => 'Band 2'],
            ['Pos_No' => 'P008', 'Pos_Name' => 'Consultant', 'SalaryScale' => 'Consultant'],
            ['Pos_No' => 'P009', 'Pos_Name' => 'Physiotherapist', 'SalaryScale' => 'Band 5'],
        ];

        foreach ($positions as $pos) {
            Pos::updateOrCreate(['Pos_No' => $pos['Pos_No']], $pos);
        }
    }
}
