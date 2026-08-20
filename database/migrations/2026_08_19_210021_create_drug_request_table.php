<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Drugrequest', function (Blueprint $table) {
            $table->string('Wd_Req_No', 10);
            $table->string('Drug_No', 10);
            $table->integer('QtyReq');

            $table->primary(['Wd_Req_No', 'Drug_No']);

            $table->foreign('Wd_Req_No')->references('Wd_Req_No')->on('Wardrequisitions');
            $table->foreign('Drug_No')->references('Drug_No')->on('Pharmaceutical');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Drugrequest');
    }
};
