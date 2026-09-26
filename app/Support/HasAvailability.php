<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

/**
 * Status stok yang dipakai bersama oleh Product dan PinSize:
 * tersedia (bisa dipesan), habis (tidak bisa dipesan), segera (belum bisa dipesan, tampil sebagai pratinjau).
 *
 * Juga menyediakan pelacakan jumlah stok (kolom `stock`, nullable):
 * - stock = null  -> tidak dilacak, status sepenuhnya diatur manual oleh admin (perilaku lama).
 * - stock = angka -> berkurang otomatis tiap kali dipesan, bertambah lagi bila item dihapus/dibatalkan.
 *   Begitu stok mencapai 0, status otomatis dipaksa jadi "habis". Begitu stok kembali di atas 0
 *   SETELAH sebelumnya habis karena stok, status otomatis kembali "tersedia".
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

    public function tracksStock(): bool
    {
        return $this->stock !== null;
    }

    // Dipanggil saat admin menyimpan data lewat form: kalau stok diisi 0, status
    // dipaksa "habis" apa pun yang dipilih admin di dropdown (stok 0 tidak bisa dijual).
    public function applyManualAvailability(?int $stock, string $availability): array
    {
        if ($stock === 0) {
            $availability = self::SOLD_OUT;
        }

        return ['stock' => $stock, 'availability' => $availability];
    }

    // Mengurangi stok saat item ditambahkan ke pesanan. Dikunci baris (lockForUpdate)
    // supaya aman dari dua pesanan yang masuk bersamaan. Tidak berpengaruh jika stok
    // tidak dilacak (null).
    public function decrementStock(int $qty): void
    {
        if (! $this->tracksStock()) {
            return;
        }

        $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();
        $newStock = max(0, $fresh->stock - $qty);
        $fresh->stock = $newStock;
        if ($newStock <= 0) {
            $fresh->availability = self::SOLD_OUT;
        }
        $fresh->save();

        $this->stock = $fresh->stock;
        $this->availability = $fresh->availability;
    }

    // Mengembalikan stok saat item dihapus dari draft atau pesanan dibatalkan.
    // Kalau status sebelumnya "habis" gara-gara stok ludes, otomatis kembali "tersedia".
    public function restoreStock(int $qty): void
    {
        if (! $this->tracksStock()) {
            return;
        }

        $fresh = static::whereKey($this->getKey())->lockForUpdate()->first();
        $fresh->stock = $fresh->stock + $qty;
        if ($fresh->stock > 0 && $fresh->availability === self::SOLD_OUT) {
            $fresh->availability = self::AVAILABLE;
        }
        $fresh->save();

        $this->stock = $fresh->stock;
        $this->availability = $fresh->availability;
    }
}
