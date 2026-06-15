<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarberSchedule extends Model
{
    protected $fillable = [
        'barber_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_active',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_active'   => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function dayName(): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return $days[$this->day_of_week] ?? 'Unknown';
    }
}
