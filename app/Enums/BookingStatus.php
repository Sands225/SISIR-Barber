<?php

namespace App\Enums;

enum BookingStatus: string
{
    case TEMP_LOCKED        = 'TEMP_LOCKED';
    case BOOKED             = 'BOOKED';
    case CONFIRMED          = 'CONFIRMED';
    case IN_SERVICE         = 'IN_SERVICE';
    case COMPLETED          = 'COMPLETED';
    case CANCELLED_BY_SYSTEM = 'CANCELLED_BY_SYSTEM';
    case NO_SHOW            = 'NO_SHOW';

    /**
     * Human-readable label (Indonesian).
     */
    public function label(): string
    {
        return match ($this) {
            self::TEMP_LOCKED         => 'Menunggu Pembayaran DP',
            self::BOOKED              => 'Terkonfirmasi (DP Lunas)',
            self::CONFIRMED           => 'Dikonfirmasi Kedatangan',
            self::IN_SERVICE          => 'Sedang Dilayani',
            self::COMPLETED           => 'Selesai',
            self::CANCELLED_BY_SYSTEM => 'Dibatalkan Sistem',
            self::NO_SHOW             => 'Tidak Hadir',
        };
    }

    /**
     * Badge color for Filament.
     */
    public function color(): string
    {
        return match ($this) {
            self::TEMP_LOCKED         => 'warning',
            self::BOOKED              => 'info',
            self::CONFIRMED           => 'primary',
            self::IN_SERVICE          => 'success',
            self::COMPLETED           => 'gray',
            self::CANCELLED_BY_SYSTEM => 'danger',
            self::NO_SHOW             => 'danger',
        };
    }

    /**
     * Defines which state transitions are allowed.
     * Returns array of valid next states.
     *
     * @return array<BookingStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::TEMP_LOCKED => [
                self::BOOKED,
                self::CANCELLED_BY_SYSTEM,
            ],
            self::BOOKED => [
                self::CONFIRMED,
                self::CANCELLED_BY_SYSTEM,
                self::NO_SHOW,
            ],
            self::CONFIRMED => [
                self::IN_SERVICE,
                self::NO_SHOW,
                self::CANCELLED_BY_SYSTEM,
            ],
            self::IN_SERVICE => [
                self::COMPLETED,
                self::NO_SHOW,
            ],
            // Terminal states — no transitions allowed
            self::COMPLETED,
            self::CANCELLED_BY_SYSTEM,
            self::NO_SHOW => [],
        };
    }

    /**
     * Returns true if transitioning to $target from $this is valid.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), strict: true);
    }

    /**
     * Returns true if this is a terminal (final) state.
     */
    public function isTerminal(): bool
    {
        return empty($this->allowedTransitions());
    }

    /**
     * Returns true if the slot should be unlocked/released.
     */
    public function releasesSlot(): bool
    {
        return in_array($this, [
            self::CANCELLED_BY_SYSTEM,
            self::NO_SHOW,
        ], strict: true);
    }
}
