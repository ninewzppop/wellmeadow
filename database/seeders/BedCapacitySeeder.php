<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BedCapacitySeeder extends Seeder
{
    public function run(): void
    {
        $wards = DB::table('Wd')->orderBy('Wd_No')->get();
        $beds = [];

        foreach ($wards as $ward) {
            $total = (int) $ward->TotalBeds;
            $targetOccupied = (int) round($total * 0.7);

            $existing = DB::table('Bed')->where('Wd_No', $ward->Wd_No)->get();
            $occupiedSoFar = $existing->where('BedStatus', 'Occupied')->count();
            $needed = $total - count($existing);

            for ($i = 1; $i <= $needed; $i++) {
                $bedNo = $ward->Wd_No.sprintf('%02d', $i);

                $status = $occupiedSoFar < $targetOccupied ? 'Occupied' : 'Available';
                if ($status === 'Occupied') {
                    $occupiedSoFar++;
                }

                $beds[] = [
                    'Bed_No' => $bedNo,
                    'Wd_No' => $ward->Wd_No,
                    'BedStatus' => $status,
                ];
            }
        }

        DB::table('Bed')->insertOrIgnore($beds);
    }
}