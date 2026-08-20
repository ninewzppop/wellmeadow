<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Pos', function (Blueprint $table) {
            $table->string('Pos_No', 10)->primary();
            $table->string('Pos_Name', 50)->nullable()->unique();
            $table->string('SalaryScale', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Pos');
    }
};
