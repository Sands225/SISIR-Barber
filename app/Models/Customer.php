<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'wa_id',
        'conversation_state',
        'conversation_context',
    ];

    protected $casts = [
        'conversation_context' => 'array',
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

    public function activeBooking(): ?Booking
    {
        return $this->bookings()
            ->whereNotIn('status', ['COMPLETED', 'CANCELLED_BY_SYSTEM', 'NO_SHOW'])
            ->latest('scheduled_at')
            ->first();
    }

    /**
     * Update conversation state and context.
     */
    public function updateConversationState(string $state, array $context = []): void
    {
        $this->update([
            'conversation_state'   => $state,
            'conversation_context' => array_merge($this->conversation_context ?? [], $context),
        ]);
    }

    /**
     * Reset conversation to idle.
     */
    public function resetConversation(): void
    {
        $this->update([
            'conversation_state'   => 'idle',
            'conversation_context' => null,
        ]);
    }
}
