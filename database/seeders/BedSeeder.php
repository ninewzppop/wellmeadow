<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BedSeeder extends Seeder
{
    public function run(): void
    {
        // ลบเตียงเก่าแบบ B* ที่ไม่ตรงสเปคใหม่ และเตียงที่เลขไม่เริ่ม 101,201...
        // ต้องเคลียร์ FK InPatient.Bed_No ก่อนลบ Bed (InPatient.Bed_No -> Bed.Bed_No)
        $oldBeds = DB::table('Bed')->where('Bed_No', 'like', 'B%')->pluck('Bed_No');
        if ($oldBeds->isNotEmpty()) {
            DB::table('InPatient')->whereIn('Bed_No', $oldBeds)->update(['Bed_No' => null]);
            DB::table('Bed')->whereIn('Bed_No', $oldBeds)->delete();
        }

        // ลบเตียงเก่าที่เลขไม่ตรงรูปแบบใหม่ (เช่น 500 แทน 501) เพื่อแก้ให้เริ่ม 101 ตามคำขอ
        foreach (['WD01','WD02','WD03','WD04','WD05','WD06','WD07','WD08','WD09','WD10','WD11','WD12','WD13','WD14','WD15','WD16','WD17'] as $ward) {
            $wardNum = (int) substr($ward, 2);
            $base = $wardNum * 100; // 100, 200, ..., 1700
            $baseBed = (string) $base;
            $exists = DB::table('Bed')->where('Wd_No', $ward)->where('Bed_No', $baseBed)->exists();
            if ($exists) {
                DB::table('InPatient')->where('Bed_No', $baseBed)->update(['Bed_No' => null]);
                DB::table('Bed')->where('Wd_No', $ward)->where('Bed_No', $baseBed)->delete();
            }
        }
        // ลบเตียงของวอร์ดที่เกิน 17 (WD18+) ถ้ามีหลงเหลือ
        $toDeleteWards = ['WD18','WD19','WD20'];
        $wardBeds = DB::table('Bed')->whereIn('Wd_No', $toDeleteWards)->pluck('Bed_No');
        if ($wardBeds->isNotEmpty()) {
            DB::table('InPatient')->whereIn('Bed_No', $wardBeds)->update(['Bed_No' => null]);
        }
        DB::table('Bed')->whereIn('Wd_No', $toDeleteWards)->delete();

        $beds = [];

        // 17 วอร์ด: WD01-WD05=14, WD06-WD07=15 (คงเดิม), WD08-WD17=14 (ใหม่) = 240 เตียง
        $allWards = [
            'WD01' => 14,
            'WD02' => 14,
            'WD03' => 14,
            'WD04' => 14,
            'WD05' => 14,
            'WD06' => 15,
            'WD07' => 15,
            'WD08' => 14,
            'WD09' => 14,
            'WD10' => 14,
            'WD11' => 14,
            'WD12' => 14,
            'WD13' => 14,
            'WD14' => 14,
            'WD15' => 14,
            'WD16' => 14,
            'WD17' => 14,
        ];

        foreach ($allWards as $ward => $count) {
            $wardNum = (int) substr($ward, 2);
            $base = $wardNum * 100; // 100, 200, ...
            for ($n = 1; $n <= $count; $n++) {
                $bedNo = (string) ($base + $n); // 101-114, 501-514, ...
                $beds[] = ['Bed_No' => $bedNo, 'Wd_No' => $ward, 'BedStatus' => 'Available'];
            }
        }

        foreach ($beds as $bed) {
            DB::table('Bed')->updateOrInsert(['Bed_No' => $bed['Bed_No']], $bed);
        }
    }
}
