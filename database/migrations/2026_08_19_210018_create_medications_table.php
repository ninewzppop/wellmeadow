<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Medications', function (Blueprint $table) {
            $table->string('Med_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Drug_No', 30)->nullable();
            $table->integer('UnitsPerDay');
            $table->string('AdminMethod', 30);
            $table->date('StartDate');
            $table->date('FinishDate');

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
            $table->foreign('Drug_No')->references('Drug_No')->on('Pharmaceutical');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Medications');
    }
};
