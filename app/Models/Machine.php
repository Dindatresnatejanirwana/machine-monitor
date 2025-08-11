<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory; // Mengaktifkan factory untuk membuat data dummy

    // Kolom yang boleh diisi secara mass-assignment
    protected $fillable = [
        'name',      // Nama mesin
        'location',  // Lokasi mesin
        'status',    // Status mesin (running / stopped)
    ];

    // Relasi: 1 Machine punya banyak Reading
    public function readings()
    {
        return $this->hasMany(Reading::class);
    }
}