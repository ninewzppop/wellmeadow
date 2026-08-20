<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Room', function (Blueprint $table) {
            $table->string('Room_No', 10)->primary();
            $table->string('RoomName', 50)->nullable();
            $table->string('Location', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Room');
    }
};
