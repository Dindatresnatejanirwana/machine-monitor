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
        Schema::create('machines', function (Blueprint $table) {
            $table->id(); // Primary key auto increment
            $table->string('name'); // Nama mesin
            $table->string('location'); // Lokasi mesin
            $table->string('status')->default('running'); // Status mesin, default "running"
            $table->timestamps(); // Kolom created_at & updated_at
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};
