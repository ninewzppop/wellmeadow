<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('Patient', function (Blueprint $table) {
            $table->index('DateReg');
        });

        Schema::table('Appointment', function (Blueprint $table) {
            $table->index('ApptDate');
        });

        Schema::table('Bed', function (Blueprint $table) {
            $table->index('BedStatus');
        });

        Schema::table('StfRota', function (Blueprint $table) {
            $table->index('WkBegin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Patient', function (Blueprint $table) {
            $table->dropIndex(['DateReg']);
        });

        Schema::table('Appointment', function (Blueprint $table) {
            $table->dropIndex(['ApptDate']);
        });

        Schema::table('Bed', function (Blueprint $table) {
            $table->dropIndex(['BedStatus']);
        });

        Schema::table('StfRota', function (Blueprint $table) {
            $table->dropIndex(['WkBegin']);
        });
    }
};
