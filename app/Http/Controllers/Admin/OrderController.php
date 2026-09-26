<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidOrderTransition;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Support\SimpleXlsx;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const PER_PAGE = 25;

    // Kolom yang boleh dipakai untuk mengurutkan (kunci di URL => kolom database).
    private const SORTS = [
        'date' => 'orders.created_at',
        'code' => 'orders.order_code',
        'customer' => null, // diurutkan lewat subquery nama pelanggan
        'items' => 'items_count',
        'pcs' => 'pcs',
        'total' => 'orders.total',
        'status' => 'orders.status',
    ];

    // Tindakan admin: status asal yang wajib, status tujuan, apakah alasan wajib.
    private const ACTIONS = [
        'confirm' => ['from' => Order::STATUS_WAITING_VERIFICATION, 'to' => Order::STATUS_PROCESSING, 'note' => false,
            'message' => 'Pembayaran dikonfirmasi. Pesanan masuk ke proses.'],
        'reject' => ['from' => Order::STATUS_WAITING_VERIFICATION, 'to' => Order::STATUS_WAITING_PAYMENT, 'note' => true,
            'message' => 'Bukti ditolak. Pelanggan diminta mengunggah ulang.'],
        'done' => ['from' => Order::STATUS_PROCESSING, 'to' => Order::STATUS_DONE, 'note' => false,
            'message' => 'Pesanan ditandai selesai. Pelanggan bisa mengambilnya.'],
        'picked_up' => ['from' => Order::STATUS_DONE, 'to' => Order::STATUS_PICKED_UP, 'note' => false,
            'message' => 'Pesanan ditandai sudah diambil.'],
        'cancel' => ['from' => [Order::STATUS_WAITING_PAYMENT, Order::STATUS_WAITING_VERIFICATION, Order::STATUS_PROCESSING],
            'to' => Order::STATUS_CANCELLED, 'note' => true, 'message' => 'Pesanan dibatalkan.'],
    ];

    public function index(Request $request)
    {
        $f = $this->filters($request);

        $sort = is_string($request->query('sort')) && array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'date';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $list = $this->applyFilters(Order::query(), $f)
            ->with('user')->withCount('items')->withSum('items as pcs', 'quantity');
        if ($sort === 'customer') {
            $list->orderBy(User::select('name')->whereColumn('users.id', 'orders.user_id'), $dir);
        } else {
            $list->orderBy(self::SORTS[$sort], $dir);
        }
        $orders = $list->orderByDesc('orders.id')->paginate(self::PER_PAGE)->withQueryString();

        // Ringkasan untuk seluruh hasil filter (bukan hanya halaman ini); pesanan dibatalkan tidak dihitung nilainya.
        $base = $this->applyFilters(Order::query(), $f);
        $valid = (clone $base)->where('orders.status', '!=', Order::STATUS_CANCELLED);
        $summary = [
            'count' => (clone $base)->count(),
            'value' => (int) (clone $valid)->sum('orders.total'),
            'pcs' => (int) OrderItem::whereIn('order_id', (clone $valid)->select('orders.id'))->sum('quantity'),
        ];

        $counts = $this->applyFilters(Order::query(), $f, false)
            ->selectRaw('orders.status, count(*) as total')->groupBy('orders.status')->pluck('total', 'status');

        return view('admin.orders.index', [
            'orders' => $orders, 'counts' => $counts, 'summary' => $summary,
            'filters' => $f, 'status' => $f['status'], 'sort' => $sort, 'dir' => $dir,
        ]);
    }

    // Unduh Excel (.xlsx) sesuai filter yang sedang aktif: sheet Pesanan, Detail Item, dan Info.
    public function export(Request $request)
    {
        if (! SimpleXlsx::isAvailable()) {
            return back()->withErrors(['order' => 'Ekstensi PHP "zip" belum aktif di server ini, jadi file Excel belum bisa dibuat.']);
        }

        $f = $this->filters($request);
        $orders = $this->applyFilters(Order::query(), $f)
            ->with(['user', 'items'])->orderBy('orders.created_at')->orderBy('orders.id')->get();

        $orderRows = [];
        $itemRows = [];
        foreach ($orders as $o) {
            $orderRows[] = [
                $o->order_code, $o->created_at, $o->paid_at, $o->user->name, $o->user->email, $o->user->phone,
                $o->items->count(), $o->items->sum('quantity'), $o->total, $o->statusLabel(),
            ];
            foreach ($o->items as $i) {
                $itemRows[] = [
                    $o->order_code, $o->created_at, $o->user->name, $i->size_name,
                    $i->quantity, $i->unit_price, $i->subtotal, $i->notes, $o->statusLabel(),
                ];
            }
        }

        $info = [
            ['Diekspor pada', now()->format('Y-m-d H:i') . ' (' . config('app.timezone') . ')'],
            ['Filter status', $f['status'] ? Order::STATUS_LABELS[$f['status']] : 'Semua (kecuali draft)'],
            ['Tanggal pesan dari', $f['from']?->format('Y-m-d') ?? '-'],
            ['Tanggal pesan sampai', $f['to']?->format('Y-m-d') ?? '-'],
            ['Kata kunci', $f['q'] !== '' ? $f['q'] : '-'],
            ['Jumlah pesanan', (string) count($orderRows)],
            ['Catatan', 'Kolom Total mencakup semua status, termasuk Dibatalkan. Filter kolom Status di Excel untuk menghitung omzet.'],
        ];

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        SimpleXlsx::write($path, [
            ['name' => 'Pesanan', 'columns' => [
                ['Kode pesanan', 'text', 20], ['Tanggal pesan', 'datetime', 18], ['Tanggal bayar', 'datetime', 18],
                ['Pelanggan', 'text', 24], ['Email', 'text', 28], ['No. HP', 'text', 16],
                ['Jumlah item', 'int', 12], ['Total pcs', 'int', 11], ['Total (Rp)', 'money', 14], ['Status', 'text', 22],
            ], 'rows' => $orderRows],
            ['name' => 'Detail Item', 'columns' => [
                ['Kode pesanan', 'text', 20], ['Tanggal pesan', 'datetime', 18], ['Pelanggan', 'text', 24], ['Ukuran', 'text', 22],
                ['Jumlah (pcs)', 'int', 13], ['Harga satuan (Rp)', 'money', 17], ['Subtotal (Rp)', 'money', 14],
                ['Catatan item', 'text', 32], ['Status pesanan', 'text', 22],
            ], 'rows' => $itemRows],
            ['name' => 'Info', 'columns' => [['Keterangan', 'text', 24], ['Nilai', 'text', 70]], 'rows' => $info],
        ]);

        return response()->download($path, 'pesanan-shellemerch-' . now()->format('Ymd-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function show(Order $order)
    {
        abort_if($order->status === Order::STATUS_DRAFT, 404);
        $order->load(['user', 'items.product', 'statusLogs.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function transition(Request $request, Order $order)
    {
        abort_if($order->status === Order::STATUS_DRAFT, 404);

        $action = $request->validate(['action' => ['required', Rule::in(array_keys(self::ACTIONS))]])['action'];
        $rule = self::ACTIONS[$action];

        $data = $request->validate([
            'note' => [Rule::requiredIf($rule['note']), 'nullable', 'string', 'max:500'],
        ], ['note.required' => 'Alasan wajib diisi.']);

        if (! in_array($order->status, (array) $rule['from'], true)) {
            return back()->withErrors(['order' => 'Tindakan ini tidak bisa dilakukan pada status pesanan sekarang.']);
        }

        $attributes = match ($action) {
            'reject' => ['payment_note' => $data['note']],
            'confirm' => ['payment_note' => null],
            default => [],
        };

        if ($action === 'cancel') {
            $order->load('items');
            foreach ($order->items as $item) {
                $this->restoreItemStock($item);
            }
        }

        try {
            $order->transitionTo($rule['to'], $request->user(), $data['note'] ?? null, $attributes);
        } catch (InvalidOrderTransition $e) {
            return back()->withErrors(['order' => 'Tindakan ini tidak bisa dilakukan pada status pesanan sekarang.']);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', $rule['message']);
    }

    // Mengembalikan stok satu item (pin custom atau produk katalog) ke sumbernya, jika stoknya dilacak.
    private function restoreItemStock(OrderItem $item): void
    {
        if ($item->pin_size_id) {
            \App\Models\PinSize::find($item->pin_size_id)?->restoreStock($item->quantity);
        } elseif ($item->product_id) {
            \App\Models\Product::find($item->product_id)?->restoreStock($item->quantity);
        }
    }

    /** Filter dari URL. Nilai yang tidak valid diabaikan, bukan menyebabkan error. */
    private function filters(Request $request): array
    {
        $from = $this->parseDate($request->query('from'));
        $to = $this->parseDate($request->query('to'));
        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $status = $request->query('status');
        if (! is_string($status) || ! isset(Order::STATUS_LABELS[$status]) || $status === Order::STATUS_DRAFT) {
            $status = null;
        }

        $q = $request->query('q');

        return ['from' => $from, 'to' => $to, 'status' => $status, 'q' => is_string($q) ? mb_substr(trim($q), 0, 100) : ''];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }

    // Draft (keranjang pelanggan) tidak pernah ditampilkan ke admin.
    private function applyFilters(Builder $query, array $f, bool $withStatus = true): Builder
    {
        $query->where('orders.status', '!=', Order::STATUS_DRAFT);

        if ($withStatus && $f['status']) {
            $query->where('orders.status', $f['status']);
        }
        if ($f['from']) {
            $query->where('orders.created_at', '>=', $f['from']->copy()->startOfDay());
        }
        if ($f['to']) {
            $query->where('orders.created_at', '<=', $f['to']->copy()->endOfDay());
        }
        if ($f['q'] !== '') {
            $like = '%' . $f['q'] . '%';
            $query->where(fn ($w) => $w->where('orders.order_code', 'like', $like)
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like)));
        }

        return $query;
    }
}
