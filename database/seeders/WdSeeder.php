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
            // ใหม่ตามคำขอ: 3 วอร์ด ×14 เตียง + 2 วอร์ด ×15 เตียง (เลขเตียงตามวอร์ด เช่น WD05: 500-513)
            ['Wd_No' => 'WD05', 'Wd_Name' => 'General Medicine', 'Location' => 'Block C, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2301'],
            ['Wd_No' => 'WD06', 'Wd_Name' => 'Orthopaedics', 'Location' => 'Block C, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2302'],
            ['Wd_No' => 'WD07', 'Wd_Name' => 'Neurology', 'Location' => 'Block D, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2401'],
            ['Wd_No' => 'WD08', 'Wd_Name' => 'Oncology', 'Location' => 'Block D, Floor 2', 'TotalBeds' => 15, 'TelExtension' => '2402'],
            ['Wd_No' => 'WD09', 'Wd_Name' => 'ICU', 'Location' => 'Block E, Floor 1', 'TotalBeds' => 15, 'TelExtension' => '2501'],
        ];

        foreach ($wards as $ward) {
            Wd::firstOrCreate(['Wd_No' => $ward['Wd_No']], $ward);
        }
    }
}
