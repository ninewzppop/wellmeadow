<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Stf', function (Blueprint $table) {
            $table->string('Stf_No', 10)->primary();
            $table->string('FirstName', 50)->nullable();
            $table->string('LastName', 50)->nullable();
            $table->string('Address', 150)->nullable();
            $table->string('TelNo', 15)->nullable();
            $table->date('DOB')->nullable();
            $table->char('Sex', 1)->nullable();
            $table->char('NIN', 13)->nullable()->unique();
            $table->string('Alloc_Wd_No', 10)->nullable();

            $table->foreign('Alloc_Wd_No')->references('Wd_No')->on('Wd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Stf');
    }
};
