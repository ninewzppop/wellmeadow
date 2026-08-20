<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('NextOfKin', function (Blueprint $table) {
            $table->string('NOK_No', 10)->primary();
            $table->string('Pt_No', 10)->nullable();
            $table->string('FirstName', 50)->nullable();
            $table->string('LastName', 50)->nullable();
            $table->string('Relationship', 20)->nullable();
            $table->string('Address', 150)->nullable();
            $table->string('TelNo', 15)->nullable();

            $table->foreign('Pt_No')->references('Pt_No')->on('Patient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('NextOfKin');
    }
};
