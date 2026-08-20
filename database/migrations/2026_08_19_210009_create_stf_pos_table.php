<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StfPos', function (Blueprint $table) {
            $table->string('StfPos_No', 10)->primary();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Pos_No', 10)->nullable();
            $table->decimal('CurrSalary', 10, 2)->nullable();
            $table->decimal('HrsPerWk', 4, 1)->nullable();
            $table->string('ContractType', 20)->nullable();
            $table->string('PaymentType', 20)->nullable();

            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
            $table->foreign('Pos_No')->references('Pos_No')->on('Pos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StfPos');
    }
};
