<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    // Mass assignment fields
    protected $fillable = ['machine_id', 'temperature', 'conveyor_speed', 'recorded_at'];

    /**
     * Relasi: satu Reading milik satu Machine
     * -> $reading->machine
     */
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    // Jika ingin otomatis casting recorded_at menjadi Carbon instance:
    protected $casts = [
        'recorded_at' => 'datetime',
    ];
}