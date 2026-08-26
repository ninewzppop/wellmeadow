<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MedicationOrder', function (Blueprint $table) {
            $table->string('Order_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Appt_No', 10)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('OrderedAt')->useCurrent();
            $table->timestamp('PaidAt')->nullable();
            $table->timestamp('DispensedAt')->nullable();
            $table->timestamp('CancelledAt')->nullable();
            $table->string('CancelReason', 255)->nullable();

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
            $table->foreign('Appt_No')->references('Appt_No')->on('Appointment');

            $table->index(['status']);
            $table->index(['Pt_No']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicationOrder');
    }
};
