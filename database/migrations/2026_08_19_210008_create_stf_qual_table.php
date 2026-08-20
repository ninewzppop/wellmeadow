<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StfQual', function (Blueprint $table) {
            $table->string('Qual_No', 10)->primary();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Type', 50)->nullable();
            $table->date('QualDate')->nullable();
            $table->string('Institution', 100)->nullable();

            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StfQual');
    }
};
