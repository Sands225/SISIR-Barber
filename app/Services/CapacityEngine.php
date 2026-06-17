<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\BarberSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CapacityEngine
{
    private int $lockTtl;

    public function __construct()
    {
        $this->lockTtl = config('sisir.slot_lock_ttl', 600);
    }

    /**
     * Get all available time slots for a barber on a given date.
     * A slot is available if not locked, booked, or walk-in occupied.
     *
     * @return Collection<array{time: string, available: bool, bookings_count: int}>
     */
    public function getAvailableSlots(int $barberId, Carbon $date): Collection
    {
        $barber   = Barber::findOrFail($barberId);
        $schedule = BarberSchedule::where('barber_id', $barberId)
            ->where('day_of_week', $date->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $schedule) {
            return collect();
        }

        $slots         = [];
        $slotDuration  = 30; // minutes per slot
        $open          = Carbon::parse($date->toDateString() . ' ' . $schedule->open_time, 'Asia/Jakarta');
        $close         = Carbon::parse($date->toDateString() . ' ' . $schedule->close_time, 'Asia/Jakarta');

        $existingBookings = Booking::where('barber_id', $barberId)
            ->whereDate('scheduled_at', $date->toDateString())
            ->whereNotIn('status', [
                BookingStatus::CANCELLED_BY_SYSTEM->value,
                BookingStatus::NO_SHOW->value,
            ])
            ->get()
            ->keyBy(fn ($b) => $b->scheduled_at->format('H:i'));

        $current = $open->copy();
        while ($current->lt($close)) {
            $timeKey  = $current->format('H:i');
            $booked   = $existingBookings->has($timeKey);
            $locked   = $this->isRedisLocked($barberId, $current);
            $capacity = $barber->capacity_per_slot;

            $slots[] = [
                'time'            => $timeKey,
                'datetime'        => $current->toIso8601String(),
                'available'       => ! $booked && ! $locked,
                'bookings_count'  => $booked ? 1 : 0,
                'capacity'        => $capacity,
                'is_locked'       => $locked,
            ];

            $current->addMinutes($slotDuration);
        }

        return collect($slots);
    }

    /**
     * Acquire a temporary slot lock for a booking.
     * Uses DB pessimistic lock + Redis NX (set if not exists) as dual guard.
     *
     * Returns true if lock was acquired, false if slot was already taken.
     */
    public function lockSlot(int $bookingId, int $barberId, Carbon $scheduledAt): bool
    {
        $redisKey = $this->redisLockKey($barberId, $scheduledAt);

        // Atomic SET NX EX equivalent — only succeeds if key doesn't exist
        $acquired = Cache::add($redisKey, $bookingId, $this->lockTtl);

        if (! $acquired) {
            Log::warning('[CapacityEngine] Slot already Redis-locked', [
                'barber_id'    => $barberId,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'booking_id'   => $bookingId,
            ]);

            return false;
        }

        // Persist lock expiry to DB for durability
        Booking::where('id', $bookingId)->update([
            'lock_expires_at' => now()->addSeconds($this->lockTtl),
        ]);

        Log::info('[CapacityEngine] Slot locked', [
            'booking_id'   => $bookingId,
            'redis_key'    => $redisKey,
            'expires_in'   => $this->lockTtl . 's',
        ]);

        return true;
    }

    /**
     * Release a slot lock — called on cancel, expire, or completion.
     */
    public function releaseSlot(int $bookingId, int $barberId, Carbon $scheduledAt): void
    {
        $redisKey = $this->redisLockKey($barberId, $scheduledAt);
        Cache::forget($redisKey);

        Booking::where('id', $bookingId)->update(['lock_expires_at' => null]);

        Log::info('[CapacityEngine] Slot released', [
            'booking_id' => $bookingId,
            'redis_key'  => $redisKey,
        ]);
    }

    /**
     * 1-Click Walk-In registration for the Filament dashboard.
     * Blocks the slot immediately and synchronously.
     *
     * Returns false if slot is already taken.
     */
    public function registerWalkIn(int $barberId, Carbon $scheduledAt, int $serviceId, string $customerName): bool|Booking
    {
        return DB::transaction(function () use ($barberId, $scheduledAt, $serviceId, $customerName) {
            // Pessimistic lock to prevent race with concurrent online bookings
            $existing = Booking::where('barber_id', $barberId)
                ->where('scheduled_at', $scheduledAt)
                ->whereNotIn('status', [
                    BookingStatus::CANCELLED_BY_SYSTEM->value,
                    BookingStatus::NO_SHOW->value,
                ])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                Log::warning('[CapacityEngine] Walk-in blocked — slot occupied', [
                    'barber_id'    => $barberId,
                    'scheduled_at' => $scheduledAt->toDateTimeString(),
                ]);

                return false;
            }

            // Find or create walk-in customer record
            $customer = \App\Models\Customer::firstOrCreate(
                ['phone' => 'WALKIN-' . now()->format('YmdHis')],
                ['name' => $customerName]
            );

            $booking = Booking::create([
                'customer_id'  => $customer->id,
                'barber_id'    => $barberId,
                'service_id'   => $serviceId,
                'scheduled_at' => $scheduledAt,
                'status'       => BookingStatus::IN_SERVICE->value,
                'dp_amount'    => 0,
                'notes'        => 'Walk-in via dashboard',
            ]);

            // Also lock in Redis to block the online booking flow
            $redisKey = $this->redisLockKey($barberId, $scheduledAt);
            Cache::put($redisKey, $booking->id, $this->lockTtl);

            Log::info('[CapacityEngine] Walk-in registered', ['booking_id' => $booking->id]);

            return $booking;
        });
    }

    /**
     * Check if the slot lock TTL has expired and auto-cancel if so.
     */
    public function expireLockedBookings(): int
    {
        $expired = Booking::where('status', BookingStatus::TEMP_LOCKED->value)
            ->where('lock_expires_at', '<', now())
            ->get();

        foreach ($expired as $booking) {
            $booking->transitionTo(
                BookingStatus::CANCELLED_BY_SYSTEM,
                'Waktu pembayaran DP habis (slot lock expired).'
            );

            $this->releaseSlot($booking->id, $booking->barber_id, $booking->scheduled_at);
        }

        return $expired->count();
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function redisLockKey(int $barberId, Carbon $scheduledAt): string
    {
        return "sisir:slot_lock:{$barberId}:{$scheduledAt->format('Ymd_Hi')}";
    }

    private function isRedisLocked(int $barberId, Carbon $scheduledAt): bool
    {
        return Cache::has(
            $this->redisLockKey($barberId, $scheduledAt)
        );
    }
}
