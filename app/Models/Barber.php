<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'nickname',
        'bio',
        'photo_path',
        'capacity_per_slot',
        'is_active',
    ];

    protected $casts = [
        'capacity_per_slot' => 'integer',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(BarberSchedule::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function displayName(): string
    {
        return $this->nickname ?? $this->user->name;
    }

    /**
     * Count today's active bookings for this barber.
     */
    public function todayActiveBookingsCount(): int
    {
        return $this->bookings()
            ->whereDate('scheduled_at', today())
            ->whereNotIn('status', ['CANCELLED_BY_SYSTEM', 'NO_SHOW', 'COMPLETED'])
            ->count();
    }
}
