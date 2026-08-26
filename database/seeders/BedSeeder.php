<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BedSeeder extends Seeder
{
    public function run(): void
    {
        // ลบเตียงเก่าแบบ B* ที่ไม่ตรงสเปคใหม่ และเตียงที่เลขไม่เริ่ม 101,201...
        DB::table('Bed')->where('Bed_No', 'like', 'B%')->delete();

        // ลบเตียงเก่าที่เลขไม่ตรงรูปแบบใหม่ (เช่น 500 แทน 501) เพื่อแก้ให้เริ่ม 101 ตามคำขอ
        foreach (['WD01','WD02','WD03','WD04','WD05','WD06','WD07'] as $ward) {
            $wardNum = (int) substr($ward, 2);
            $base = $wardNum * 100; // 100, 200, ...
            // ลบเตียงที่ลงท้ายด้วย 00 (เช่น 500) ซึ่งควรเริ่ม 501
            DB::table('Bed')->where('Wd_No', $ward)->where('Bed_No', (string) $base)->delete();
        }
        // ลบเตียงของวอร์ดที่ถูกลบ (WD08/WD09)
        DB::table('Bed')->whereIn('Wd_No', ['WD08','WD09'])->delete();

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
