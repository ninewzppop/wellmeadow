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

        foreach ($beds as $bed) {
            DB::table('Bed')->updateOrInsert(['Bed_No' => $bed['Bed_No']], $bed);
        }
    }
}
