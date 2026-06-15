<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    protected $fillable = [
        'customer_id',
        'service_id',
        'preferred_date',
        'preferred_time',
        'notified_at',
        'is_active',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'notified_at'    => 'datetime',
        'is_active'      => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('notified_at');
    }
}
