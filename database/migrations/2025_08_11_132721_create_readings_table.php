<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('readings', function (Blueprint $table) {
            $table->id(); // primary key
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->decimal('temperature', 5, 2);   // 20.00 - 100.00
            $table->decimal('conveyor_speed', 4, 2);// 0.50 - 5.00
            $table->timestamp('recorded_at');       // waktu pembacaan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('readings');
    }
};
