<?php

namespace Database\Seeders;

use App\Models\Wd;
use Illuminate\Database\Seeder;

class WdSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            // แก้ทั้งหมดให้เลขเตียงเริ่มที่ X01: วอร์ด1 101-114 (14) — ลบ WD08/WD09 ตามคำขอ
            ['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'Block A, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2101'],
            ['Wd_No' => 'WD02', 'Wd_Name' => 'Paediatrics', 'Location' => 'Block A, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2102'],
            ['Wd_No' => 'WD03', 'Wd_Name' => 'Surgical', 'Location' => 'Block B, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2201'],
            ['Wd_No' => 'WD04', 'Wd_Name' => 'Maternity', 'Location' => 'Block B, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2202'],
            ['Wd_No' => 'WD05', 'Wd_Name' => 'General Medicine', 'Location' => 'Block C, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2301'],
            ['Wd_No' => 'WD06', 'Wd_Name' => 'Orthopaedics', 'Location' => 'Block C, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2302'],
            ['Wd_No' => 'WD07', 'Wd_Name' => 'Neurology', 'Location' => 'Block D, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2401'],
        ];

        foreach ($wards as $ward) {
            \Illuminate\Support\Facades\DB::table('Wd')->updateOrInsert(['Wd_No' => $ward['Wd_No']], $ward);
        }

        // ลบ WD08/WD09 ตามคำขอ: จัดการ FK ก่อนลบ Wd
        $toDelete = ['WD08', 'WD09'];
        // InPatient ที่ผูกกับเตียงของวอร์ดที่จะลบ (Bed -> InPatient)
        $bedNos = \Illuminate\Support\Facades\DB::table('Bed')->whereIn('Wd_No', $toDelete)->pluck('Bed_No');
        if ($bedNos->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('InPatient')->whereIn('Bed_No', $bedNos)->delete();
        }
        // Bed (ถ้าเหลือ)
        \Illuminate\Support\Facades\DB::table('Bed')->whereIn('Wd_No', $toDelete)->delete();
        // Stf Alloc
        \Illuminate\Support\Facades\DB::table('Stf')->whereIn('Alloc_Wd_No', $toDelete)->update(['Alloc_Wd_No' => null]);
        // Rota
        \Illuminate\Support\Facades\DB::table('StfRota')->whereIn('Wd_No', $toDelete)->delete();
        // Wardrequisitions + lines
        $reqNos = \Illuminate\Support\Facades\DB::table('Wardrequisitions')->whereIn('Wd_No', $toDelete)->pluck('Wd_Req_No');
        if ($reqNos->isNotEmpty()) {
            \Illuminate\Support\Facades\DB::table('Itemrequest')->whereIn('Wd_Req_No', $reqNos)->delete();
            \Illuminate\Support\Facades\DB::table('Drugrequest')->whereIn('Wd_Req_No', $reqNos)->delete();
            \Illuminate\Support\Facades\DB::table('Wardrequisitions')->whereIn('Wd_Req_No', $reqNos)->delete();
        }
        \Illuminate\Support\Facades\DB::table('Wd')->whereIn('Wd_No', $toDelete)->delete();
    }
}
