<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Status stok yang dipakai bersama oleh Product dan PinSize:
 * tersedia (bisa dipesan), habis (tidak bisa dipesan), segera (belum bisa dipesan, tampil sebagai pratinjau).
 */
trait HasAvailability
{
    public const AVAILABLE = 'tersedia';
    public const SOLD_OUT = 'habis';
    public const COMING_SOON = 'segera';

    public const AVAILABILITY_LABELS = [
        self::AVAILABLE => 'Tersedia',
        self::SOLD_OUT => 'Habis',
        self::COMING_SOON => 'Segera hadir',
    ];

    public function scopeOrderable(Builder $query): Builder
    {
        return $query->where('availability', self::AVAILABLE);
    }

    public function isOrderable(): bool
    {
        return $this->availability === self::AVAILABLE;
    }

    public function availabilityLabel(): string
    {
        return self::AVAILABILITY_LABELS[$this->availability] ?? $this->availability;
    }

    public function availabilityBadgeClass(): string
    {
        return match ($this->availability) {
            self::SOLD_OUT => 'text-bg-danger',
            self::COMING_SOON => 'text-bg-info',
            default => 'text-bg-success',
        };
    }
}
