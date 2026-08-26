<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Wardrequisitions', function (Blueprint $table) {
            $table->string('status', 20)->default('Pending')->after('DateRecv');
            $table->string('Received_By', 10)->nullable()->after('status');
            $table->foreign('Received_By')->references('Stf_No')->on('Stf');
            $table->index('status');
        });

        // backfill existing rows
        DB::table('Wardrequisitions')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'Pending']);

        DB::table('Wardrequisitions')
            ->whereNotNull('DateRecv')
            ->update(['status' => 'Completed']);
    }

    public function down(): void
    {
        Schema::table('Wardrequisitions', function (Blueprint $table) {
            $table->dropForeign(['Received_By']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'Received_By']);
        });
    }
};
