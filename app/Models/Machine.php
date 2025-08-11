<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    // Mengizinkan mass-assignment pada kolom berikut
    protected $fillable = ['name', 'location', 'status'];

    /**
     * Relasi: satu Machine punya banyak Reading
     * -> $machine->readings()
     */
    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    /**
     * Relasi helper: ambil reading terbaru berdasarkan recorded_at
     * -> $machine->latestReading
     */
    public function latestReading()
    {
        // latestOfMany menggunakan kolom recorded_at untuk menentukan terbaru
        return $this->hasOne(Reading::class)->latestOfMany('recorded_at');
    }
}