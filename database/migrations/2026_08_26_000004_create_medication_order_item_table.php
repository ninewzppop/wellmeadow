<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('MedicationOrderItem', function (Blueprint $table) {
            $table->id();
            $table->string('Order_No', 10);
            $table->string('Drug_No', 30)->nullable();
            $table->integer('UnitsPerDay');
            $table->string('AdminMethod', 30);
            $table->date('StartDate');
            $table->date('FinishDate');

            $table->foreign('Order_No')->references('Order_No')->on('MedicationOrder')->cascadeOnDelete();
            $table->foreign('Drug_No')->references('Drug_No')->on('Pharmaceutical');

            $table->index(['Order_No']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('MedicationOrderItem');
    }
};
