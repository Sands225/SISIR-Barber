<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Exceptions\InvalidStateTransitionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'barber_id',
        'service_id',
        'scheduled_at',
        'status',
        'dp_amount',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_qr_code_url',
        'lock_expires_at',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'status'          => BookingStatus::class,
        'scheduled_at'    => 'datetime',
        'lock_expires_at' => 'datetime',
        'dp_amount'       => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(Barber::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    // ── State Machine ────────────────────────────────────────────────────────

    /**
     * Transition booking to a new status.
     * Throws InvalidStateTransitionException if transition is not allowed.
     *
     * @throws InvalidStateTransitionException
     */
    public function transitionTo(BookingStatus $newStatus, ?string $reason = null): void
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            throw new InvalidStateTransitionException(
                "Cannot transition booking #{$this->id} from [{$this->status->value}] to [{$newStatus->value}]."
            );
        }

        $updateData = ['status' => $newStatus];

        if ($reason) {
            $updateData['cancellation_reason'] = $reason;
        }

        $this->update($updateData);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeLocked($query)
    {
        return $query->where('status', BookingStatus::TEMP_LOCKED)
                     ->where('lock_expires_at', '>', now());
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            BookingStatus::COMPLETED->value,
            BookingStatus::CANCELLED_BY_SYSTEM->value,
            BookingStatus::NO_SHOW->value,
        ]);
    }

    public function scopePendingConfirmation($query)
    {
        return $query->where('status', BookingStatus::BOOKED)
                     ->where('scheduled_at', '<=', now()->addMinutes(35))
                     ->where('scheduled_at', '>', now());
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isLockExpired(): bool
    {
        return $this->status === BookingStatus::TEMP_LOCKED
            && $this->lock_expires_at
            && $this->lock_expires_at->isPast();
    }

    /**
     * Generate a unique Midtrans order ID.
     */
    public static function generateOrderId(int $bookingId): string
    {
        return 'SISIR-' . str_pad($bookingId, 6, '0', STR_PAD_LEFT) . '-' . time();
    }

    public function scheduledAtFormatted(): string
    {
        return $this->scheduled_at->locale('id')->isoFormat('dddd, D MMMM YYYY [pukul] HH:mm');
    }
}
