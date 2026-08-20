<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Bed', function (Blueprint $table) {
            $table->string('Bed_No', 10)->primary();
            $table->string('Wd_No', 10)->nullable();
            $table->string('BedStatus', 15)->nullable();

            $table->foreign('Wd_No')->references('Wd_No')->on('Wd');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Bed');
    }
};
