<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('CentralStock')->where('ItemType', 'Surgical')->update(['ItemType' => 'surgical']);
        DB::table('CentralStock')->where('ItemType', 'NonSurgical')->update(['ItemType' => 'non-surgical']);
        // handle legacy variations if any
        DB::table('CentralStock')->where('ItemType', 'Non-surgical')->update(['ItemType' => 'non-surgical']);
    }

    public function down(): void
    {
        DB::table('CentralStock')->where('ItemType', 'surgical')->update(['ItemType' => 'Surgical']);
        DB::table('CentralStock')->where('ItemType', 'non-surgical')->update(['ItemType' => 'NonSurgical']);
    }
};
