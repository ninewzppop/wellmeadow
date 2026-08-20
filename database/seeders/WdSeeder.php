<?php

namespace Database\Seeders;

use App\Models\Wd;
use Illuminate\Database\Seeder;

class WdSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            ['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'Block A, Floor 1', 'TotalBeds' => 30, 'TelExtension' => '2101'],
            ['Wd_No' => 'WD02', 'Wd_Name' => 'Paediatrics', 'Location' => 'Block A, Floor 2', 'TotalBeds' => 24, 'TelExtension' => '2102'],
            ['Wd_No' => 'WD03', 'Wd_Name' => 'Surgical', 'Location' => 'Block B, Floor 1', 'TotalBeds' => 36, 'TelExtension' => '2201'],
            ['Wd_No' => 'WD04', 'Wd_Name' => 'Maternity', 'Location' => 'Block B, Floor 2', 'TotalBeds' => 20, 'TelExtension' => '2202'],
        ];

        foreach ($wards as $ward) {
            Wd::firstOrCreate(['Wd_No' => $ward['Wd_No']], $ward);
        }
    }
}
