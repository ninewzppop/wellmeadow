<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link a login account to its staff record (ADR-0012).
     * Nullable: legacy accounts without staff keep working (unrestricted scope).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('stf_no', 10)->nullable()->unique()->after('role');
            $table->foreign('stf_no')->references('Stf_No')->on('Stf')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['stf_no']);
            $table->dropUnique(['stf_no']);
            $table->dropColumn('stf_no');
        });
    }
};
