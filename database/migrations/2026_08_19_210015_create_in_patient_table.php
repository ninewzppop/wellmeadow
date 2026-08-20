<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('InPatient', function (Blueprint $table) {
            $table->string('In_Pt_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('Bed_No', 255)->nullable();
            $table->date('DateWaitList')->nullable();
            $table->smallInteger('ExpStayDays')->nullable();
            $table->date('DatePlaced')->nullable();
            $table->date('DateLeave')->nullable();
            $table->date('ActDateLeft')->nullable();

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
            $table->foreign('Bed_No')->references('Bed_No')->on('Bed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('InPatient');
    }
};
