<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',        // ID mesin yang melakukan pembacaan
        'temperature',       // Suhu mesin
        'conveyor_speed',    // Kecepatan conveyor
    ];

    // Relasi: Reading dimiliki oleh satu Machine
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
