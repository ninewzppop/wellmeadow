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

        $this->call([
            PosSeeder::class,
            WdSeeder::class,
            BedSeeder::class,
            StaffSeeder::class,
            ClinicalSeeder::class,
            SuppliesSeeder::class,
        ]);
    }
}
