<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('StockMovement', function (Blueprint $table) {
            $table->id();
            $table->string('Drug_No', 10)->nullable();
            $table->string('Item_No', 10)->nullable();
            $table->integer('QtyChange');
            $table->string('Note', 255)->nullable();
            $table->unsignedBigInteger('Moved_By')->nullable();
            $table->timestamp('MoveDate')->useCurrent();

            $table->foreign('Drug_No')->references('Drug_No')->on('Pharmaceutical');
            $table->foreign('Item_No')->references('Item_No')->on('CentralStock');
            $table->foreign('Moved_By')->references('id')->on('users');

            $table->index(['Drug_No']);
            $table->index(['Item_No']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('StockMovement');
    }
};
