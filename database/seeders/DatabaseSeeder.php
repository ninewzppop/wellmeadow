<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Test Admin', 'password' => 'password', 'role' => 'admin'],
        );

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password', 'role' => 'staff'],
        );

        // ADR-12.2: retire legacy role values (passwords untouched).
        User::where('email', 'admin@example.com')->where('role', 'admin')->update(['role' => 'medical_director']);
        User::where('email', 'test@example.com')->where('role', 'staff')->update(['role' => 'staff_nurse']);

        $this->call([
            PosSeeder::class,
            WdSeeder::class,
            BedSeeder::class,
            StaffSeeder::class,
            SuppliesSeeder::class,
            ClinicalSeeder::class,
        ]);

        // Test accounts per role, linked to staff (ADR-0012).
        // Created AFTER StaffSeeder so users.stf_no FK is satisfied.
        // All share password 'password'. Existing rows are never overwritten.
        $roleUsers = [
            ['email' => 'director@example.com', 'name' => 'Medical Director', 'role' => 'medical_director', 'stf_no' => 'S1001'],
            ['email' => 'personnel@example.com', 'name' => 'Personnel Officer', 'role' => 'personnel_officer', 'stf_no' => 'S1003'],
            ['email' => 'charge@example.com', 'name' => 'Charge Nurse', 'role' => 'charge_nurse', 'stf_no' => 'S1002'],
            ['email' => 'doctor@example.com', 'name' => 'Doctor', 'role' => 'doctor', 'stf_no' => 'S1004'],
            ['email' => 'consultant@example.com', 'name' => 'Consultant', 'role' => 'consultant', 'stf_no' => 'S1006'],
            ['email' => 'senior@example.com', 'name' => 'Senior Nurse', 'role' => 'senior_nurse', 'stf_no' => 'S1007'],
            ['email' => 'nurse@example.com', 'name' => 'Staff Nurse', 'role' => 'staff_nurse', 'stf_no' => 'S1005'],
            ['email' => 'aux@example.com', 'name' => 'Auxiliary', 'role' => 'auxiliary', 'stf_no' => 'S1008'],
            ['email' => 'physio@example.com', 'name' => 'Physiotherapist', 'role' => 'staff_nurse', 'stf_no' => 'S1009'],
        ];

        foreach ($roleUsers as $account) {
            User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => 'password', 'role' => $account['role'], 'stf_no' => $account['stf_no']],
            );
        }
    }
}
