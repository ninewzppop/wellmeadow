<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StfRota', function (Blueprint $table) {
            $table->string('StfRota_No', 10)->primary();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Wd_No', 10)->nullable();
            $table->date('WkBegin')->nullable();
            $table->string('Shift', 10)->nullable();

            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
            $table->foreign('Wd_No')->references('Wd_No')->on('Wd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StfRota');
    }
};
