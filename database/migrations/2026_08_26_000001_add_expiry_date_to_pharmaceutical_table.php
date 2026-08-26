<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('Pharmaceutical', function (Blueprint $table) {
            $table->date('ExpiryDate')->nullable()->after('AdminMethod');
        });
    }

    public function down(): void
    {
        Schema::table('Pharmaceutical', function (Blueprint $table) {
            $table->dropColumn('ExpiryDate');
        });
    }
};
