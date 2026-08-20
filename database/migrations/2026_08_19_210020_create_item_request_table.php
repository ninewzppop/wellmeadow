<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Itemrequest', function (Blueprint $table) {
            $table->string('Wd_Req_No', 10);
            $table->string('Item_No', 10);
            $table->integer('QtyReq')->nullable();

            $table->primary(['Wd_Req_No', 'Item_No']);

            $table->foreign('Wd_Req_No')->references('Wd_Req_No')->on('Wardrequisitions');
            $table->foreign('Item_No')->references('Item_No')->on('CentralStock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Itemrequest');
    }
};
