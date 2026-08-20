<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Pharmaceutical', function (Blueprint $table) {
            $table->string('Drug_No', 10)->primary();
            $table->string('Name', 100)->nullable();
            $table->string('Description', 255)->nullable();
            $table->string('Dosage', 30)->nullable();
            $table->string('AdminMethod', 30)->nullable();
            $table->integer('QtyInStock')->nullable();
            $table->integer('ReorderLvl')->nullable();
            $table->decimal('CostPerUnit', 8, 2)->nullable();
            $table->string('Suppl_No', 10)->nullable();

            $table->foreign('Suppl_No')->references('Suppl_No')->on('Supplier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Pharmaceutical');
    }
};
