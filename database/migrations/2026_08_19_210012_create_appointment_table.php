<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Appointment', function (Blueprint $table) {
            $table->string('Appt_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('Consult_Stf_No', 10)->nullable();
            $table->date('ApptDate')->nullable();
            $table->time('ApptTime')->nullable();
            $table->string('Room_No', 10)->nullable();

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
            $table->foreign('Consult_Stf_No')->references('Stf_No')->on('Stf');
            $table->foreign('Room_No')->references('Room_No')->on('Room');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Appointment');
    }
};
