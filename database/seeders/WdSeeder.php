<?php

namespace Database\Seeders;

use App\Models\Wd;
use Illuminate\Database\Seeder;

class WdSeeder extends Seeder
{
    public function run(): void
    {
        $wards = [
            // วอร์ดเดิม WD01-WD07 คงไว้ไม่แก้ (WD06/W07 =15 ตามเดิม) — เพิ่ม WD08-WD17 วอร์ดละ 14 เตียง รวม 17 วอร์ด
            ['Wd_No' => 'WD01', 'Wd_Name' => 'Cardiology', 'Location' => 'Block A, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2101'],
            ['Wd_No' => 'WD02', 'Wd_Name' => 'Paediatrics', 'Location' => 'Block A, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2102'],
            ['Wd_No' => 'WD03', 'Wd_Name' => 'Surgical', 'Location' => 'Block B, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2201'],
            ['Wd_No' => 'WD04', 'Wd_Name' => 'Maternity', 'Location' => 'Block B, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2202'],
            ['Wd_No' => 'WD05', 'Wd_Name' => 'General Medicine', 'Location' => 'Block C, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2301'],
            ['Wd_No' => 'WD06', 'Wd_Name' => 'Orthopaedics', 'Location' => 'Block C, Floor 2', 'TotalBeds' => 15, 'TelExtension' => '2302'],
            ['Wd_No' => 'WD07', 'Wd_Name' => 'Neurology', 'Location' => 'Block D, Floor 1', 'TotalBeds' => 15, 'TelExtension' => '2401'],
            ['Wd_No' => 'WD08', 'Wd_Name' => 'Intensive Care', 'Location' => 'Block D, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2402'],
            ['Wd_No' => 'WD09', 'Wd_Name' => 'Emergency', 'Location' => 'Block E, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2501'],
            ['Wd_No' => 'WD10', 'Wd_Name' => 'Oncology', 'Location' => 'Block E, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2502'],
            ['Wd_No' => 'WD11', 'Wd_Name' => 'Rehabilitation', 'Location' => 'Block F, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2601'],
            ['Wd_No' => 'WD12', 'Wd_Name' => 'Psychiatry', 'Location' => 'Block F, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2602'],
            ['Wd_No' => 'WD13', 'Wd_Name' => 'Dermatology', 'Location' => 'Block G, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2701'],
            ['Wd_No' => 'WD14', 'Wd_Name' => 'Ophthalmology', 'Location' => 'Block G, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2702'],
            ['Wd_No' => 'WD15', 'Wd_Name' => 'ENT', 'Location' => 'Block H, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2801'],
            ['Wd_No' => 'WD16', 'Wd_Name' => 'Urology', 'Location' => 'Block H, Floor 2', 'TotalBeds' => 14, 'TelExtension' => '2802'],
            ['Wd_No' => 'WD17', 'Wd_Name' => 'Geriatrics', 'Location' => 'Block I, Floor 1', 'TotalBeds' => 14, 'TelExtension' => '2901'],
        ];

        foreach ($wards as $ward) {
            \Illuminate\Support\Facades\DB::table('Wd')->updateOrInsert(['Wd_No' => $ward['Wd_No']], $ward);
        }

        // กันวอร์ดเกิน 17 (WD18+ ถ้ามีหลงเหลือจากข้อมูลเก่า) — ไม่ลบ WD08-WD17 แล้ว
        $toDelete = ['WD18', 'WD19', 'WD20'];
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
