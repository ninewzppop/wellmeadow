<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('PatientAllergy', function (Blueprint $table) {
            $table->string('Allergy_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('Drug_No', 10)->nullable();
            $table->string('Allergy_Name', 100)->nullable();
            $table->string('Reaction', 150);
            $table->string('Severity', 15);
            $table->date('DiagDate');
            $table->string('Rec_Stf_No', 10)->nullable();

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
            $table->foreign('Drug_No')->references('Drug_No')->on('Pharmaceutical');
            $table->foreign('Rec_Stf_No')->references('Stf_No')->on('Stf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('PatientAllergy');
    }
};
