<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Supplier', function (Blueprint $table) {
            $table->string('Suppl_No', 10)->primary();
            $table->string('Name', 100)->nullable();
            $table->string('Address', 50)->nullable();
            $table->string('TelNo', 15)->nullable();
            $table->string('FaxNo', 15)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Supplier');
    }
};
