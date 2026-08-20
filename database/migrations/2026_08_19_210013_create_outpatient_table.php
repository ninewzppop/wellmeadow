<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Outpatient', function (Blueprint $table) {
            $table->string('Appt_out_No', 10)->primary();

            $table->foreign('Appt_out_No')->references('Appt_No')->on('Appointment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Outpatient');
    }
};
