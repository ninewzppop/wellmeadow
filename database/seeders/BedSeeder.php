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
        foreach (['WD01','WD02','WD03','WD04','WD05','WD06','WD07'] as $ward) {
            $wardNum = (int) substr($ward, 2);
            $base = $wardNum * 100; // 100, 200, ...
            $baseBed = (string) $base;
            $exists = DB::table('Bed')->where('Wd_No', $ward)->where('Bed_No', $baseBed)->exists();
            if ($exists) {
                DB::table('InPatient')->where('Bed_No', $baseBed)->update(['Bed_No' => null]);
                DB::table('Bed')->where('Wd_No', $ward)->where('Bed_No', $baseBed)->delete();
            }
        }
        // ลบเตียงของวอร์ดที่ถูกลบ (WD08/WD09)
        $toDeleteWards = ['WD08','WD09'];
        $wardBeds = DB::table('Bed')->whereIn('Wd_No', $toDeleteWards)->pluck('Bed_No');
        if ($wardBeds->isNotEmpty()) {
            DB::table('InPatient')->whereIn('Bed_No', $wardBeds)->update(['Bed_No' => null]);
        }
        DB::table('Bed')->whereIn('Wd_No', $toDeleteWards)->delete();

        $beds = [];

        // ทุกวอร์ดใช้เลขตามวอร์ด เริ่ม 101, 201, 501... วอร์ด6-7 เพิ่มเป็น 15 ตามคำขอล่าสุด
        $allWards = [
            'WD01' => 14,
            'WD02' => 14,
            'WD03' => 14,
            'WD04' => 14,
            'WD05' => 14,
            'WD06' => 15,
            'WD07' => 15,
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
