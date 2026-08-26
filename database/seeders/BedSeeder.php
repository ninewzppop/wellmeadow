<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BedSeeder extends Seeder
{
    public function run(): void
    {
        $beds = [];

        foreach (['WD01', 'WD02', 'WD03', 'WD04'] as $i => $ward) {
            $beds[] = ['Bed_No' => "B{$i}01", 'Wd_No' => $ward, 'BedStatus' => 'Occupied'];
            $beds[] = ['Bed_No' => "B{$i}02", 'Wd_No' => $ward, 'BedStatus' => 'Available'];
        }

        // วอร์ดใหม่: เลขเตียงตามวอร์ด เช่น WD05 -> 500-513, WD06 -> 600-613 ฯลฯ
        $newWards = [
            'WD05' => 14,
            'WD06' => 14,
            'WD07' => 14,
            'WD08' => 15,
            'WD09' => 15,
        ];

        foreach ($newWards as $ward => $count) {
            $wardNum = (int) substr($ward, 2); // WD05 -> 5
            $base = $wardNum * 100; // 500, 600, ...
            for ($n = 0; $n < $count; $n++) {
                $bedNo = (string) ($base + $n); // 500, 501, ...
                $beds[] = ['Bed_No' => $bedNo, 'Wd_No' => $ward, 'BedStatus' => 'Available'];
            }
        }

        foreach ($beds as $bed) {
            DB::table('Bed')->updateOrInsert(['Bed_No' => $bed['Bed_No']], $bed);
        }
    }
}
