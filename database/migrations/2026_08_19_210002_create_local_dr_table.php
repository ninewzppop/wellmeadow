<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('LocalDr', function (Blueprint $table) {
            $table->string('Clinic_No', 10)->primary();
            $table->string('FirstName', 50)->nullable();
            $table->string('LastName', 50)->nullable();
            $table->string('Address', 150)->nullable();
            $table->string('TelNo', 15)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('LocalDr');
    }
};
