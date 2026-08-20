<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StfWorkExp', function (Blueprint $table) {
            $table->string('WorkExp_No', 10)->primary();
            $table->string('Stf_No', 10)->nullable();
            $table->string('Organization', 100)->nullable();
            $table->string('Position', 50)->nullable();
            $table->date('StartDate')->nullable();
            $table->date('FinishDate')->nullable();

            $table->foreign('Stf_No')->references('Stf_No')->on('Stf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StfWorkExp');
    }
};
