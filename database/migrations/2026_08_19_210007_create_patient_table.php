<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Patient', function (Blueprint $table) {
            $table->string('Pt_No', 10)->primary();
            $table->string('FirstName', 50)->nullable();
            $table->string('LastName', 50)->nullable();
            $table->string('Address', 150)->nullable();
            $table->string('TelNo', 15)->nullable();
            $table->date('DOB')->nullable();
            $table->char('Sex', 1)->nullable();
            $table->string('MaritalStat', 15)->nullable();
            $table->date('DateReg')->nullable();
            $table->string('Clinic_No', 10)->nullable();

            $table->foreign('Clinic_No')->references('Clinic_No')->on('LocalDr');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Patient');
    }
};
