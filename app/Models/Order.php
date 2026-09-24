<?php

namespace App\Models;

use App\Exceptions\InvalidOrderTransition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    // Satu-satunya jalur perubahan status yang diizinkan. Status akhir: diambil, dibatalkan.
    public const TRANSITIONS = [
        self::STATUS_DRAFT => [self::STATUS_WAITING_PAYMENT],
        self::STATUS_WAITING_PAYMENT => [self::STATUS_DRAFT, self::STATUS_WAITING_VERIFICATION, self::STATUS_CANCELLED],
        self::STATUS_WAITING_VERIFICATION => [self::STATUS_PROCESSING, self::STATUS_WAITING_PAYMENT, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_DONE, self::STATUS_CANCELLED],
        self::STATUS_DONE => [self::STATUS_PICKED_UP],
    ];

    public const PROGRESS_STEPS = ['Pembayaran', 'Verifikasi', 'Diproses', 'Siap diambil', 'Diambil'];

    // Diisi dari kode server saja, tidak pernah dari request->all().
    protected $fillable = ['user_id', 'status', 'notes', 'total'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

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

        static::created(function (Order $order) {
            $order->statusLogs()->create([
                'from_status' => null,
                'to_status' => $order->status,
                'user_id' => $order->user_id,
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class)->orderBy('id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('id');
    }

    // Item hanya boleh ditambah/dihapus saat masih draft. Untuk mengubah pesanan yang
    // sudah menunggu pembayaran, pelanggan mengembalikannya ke draft lebih dulu.
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canTransitionTo(string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /**
     * Ubah status secara aman: dikunci di database, divalidasi terhadap TRANSITIONS,
     * dan selalu dicatat di riwayat. $attributes = kolom lain yang ikut berubah (bukti bayar, dll).
     *
     * @throws InvalidOrderTransition
     */
    public function transitionTo(string $to, ?User $actor = null, ?string $note = null, array $attributes = []): void
    {
        DB::transaction(function () use ($to, $actor, $note, $attributes) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->canTransitionTo($to)) {
                throw new InvalidOrderTransition($locked->status, $to);
            }

            $from = $locked->status;
            $locked->forceFill(array_merge($attributes, ['status' => $to]))->save();
            $locked->statusLogs()->create([
                'from_status' => $from,
                'to_status' => $to,
                'user_id' => $actor?->id,
                'note' => $note,
            ]);
        });

        $this->refresh();
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

    // Posisi di PROGRESS_STEPS; null untuk pesanan dibatalkan.
    public function progressStep(): ?int
    {
        return match ($this->status) {
            self::STATUS_DRAFT, self::STATUS_WAITING_PAYMENT => 0,
            self::STATUS_WAITING_VERIFICATION => 1,
            self::STATUS_PROCESSING => 2,
            self::STATUS_DONE => 3,
            self::STATUS_PICKED_UP => 4,
            default => null,
        };
    }

    public function recalculateTotal(): void
    {
        $this->total = (int) $this->items()->get()->sum(fn (OrderItem $i) => $i->unit_price * $i->quantity);
        $this->save();
    }
}
