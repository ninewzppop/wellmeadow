<?php

namespace Database\Seeders;

use App\Models\Wd;
use Illuminate\Database\Seeder;

class WdSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            // แก้ทั้งหมดให้เลขเตียงเริ่มที่ X01 ตามคำขอ: วอร์ด1 101-114 (14), วอร์ด8-9 มี 15
            ['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'Block A, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2101'],
            ['Wd_No' => 'WD02', 'Wd_Name' => 'Paediatrics', 'Location' => 'Block A, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2102'],
            ['Wd_No' => 'WD03', 'Wd_Name' => 'Surgical', 'Location' => 'Block B, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2201'],
            ['Wd_No' => 'WD04', 'Wd_Name' => 'Maternity', 'Location' => 'Block B, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2202'],
            ['Wd_No' => 'WD05', 'Wd_Name' => 'General Medicine', 'Location' => 'Block C, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2301'],
            ['Wd_No' => 'WD06', 'Wd_Name' => 'Orthopaedics', 'Location' => 'Block C, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2302'],
            ['Wd_No' => 'WD07', 'Wd_Name' => 'Neurology', 'Location' => 'Block D, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2401'],
            ['Wd_No' => 'WD08', 'Wd_Name' => 'Oncology', 'Location' => 'Block D, Floor 2', 'TotalBeds' => 15, 'TelExtension' => '2402'],
            ['Wd_No' => 'WD09', 'Wd_Name' => 'ICU', 'Location' => 'Block E, Floor 1', 'TotalBeds' => 15, 'TelExtension' => '2501'],
        ];

        foreach ($wards as $ward) {
            \Illuminate\Support\Facades\DB::table('Wd')->updateOrInsert(['Wd_No' => $ward['Wd_No']], $ward);
        }
    }
}
