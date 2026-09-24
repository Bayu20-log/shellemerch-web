<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_WAITING_PAYMENT = 'menunggu_pembayaran';
    public const STATUS_WAITING_VERIFICATION = 'menunggu_verifikasi';
    public const STATUS_PROCESSING = 'diproses';
    public const STATUS_DONE = 'selesai';
    public const STATUS_PICKED_UP = 'diambil';
    public const STATUS_CANCELLED = 'dibatalkan';

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_WAITING_PAYMENT => 'Menunggu pembayaran',
        self::STATUS_WAITING_VERIFICATION => 'Menunggu verifikasi',
        self::STATUS_PROCESSING => 'Diproses',
        self::STATUS_DONE => 'Selesai, siap diambil',
        self::STATUS_PICKED_UP => 'Sudah diambil',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    // Diisi dari kode server saja, tidak pernah dari request->all().
    protected $fillable = ['user_id', 'status', 'notes', 'total'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_code) {
                do {
                    $code = 'SM-' . now()->format('ymd') . '-' . strtoupper(Str::random(4));
                } while (static::where('order_code', $code)->exists());
                $order->order_code = $code;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Item hanya boleh ditambah/dihapus sebelum pembayaran dikirim.
    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_WAITING_PAYMENT], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'text-bg-secondary',
            self::STATUS_WAITING_PAYMENT, self::STATUS_WAITING_VERIFICATION => 'text-bg-warning',
            self::STATUS_PROCESSING => 'text-bg-primary',
            self::STATUS_DONE => 'text-bg-success',
            self::STATUS_CANCELLED => 'text-bg-danger',
            default => 'text-bg-dark',
        };
    }

    public function recalculateTotal(): void
    {
        $this->total = (int) $this->items()->get()->sum(fn (OrderItem $i) => $i->unit_price * $i->quantity);
        $this->save();
    }
}
