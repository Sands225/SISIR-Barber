<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
        'photo_path',
    ];

    protected $casts = [
        'price'            => 'integer',
        'duration_minutes' => 'integer',
        'is_active'        => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function waitlists(): HasMany
    {
        return $this->hasMany(Waitlist::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns formatted price in IDR.
     */
    public function formattedPrice(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
}
