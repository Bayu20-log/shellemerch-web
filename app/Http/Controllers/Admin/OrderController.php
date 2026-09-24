<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidOrderTransition;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    // Tindakan admin: status asal yang wajib, status tujuan, apakah alasan wajib, kolom tambahan.
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

    // Draft (keranjang pelanggan) sengaja tidak ditampilkan ke admin.
    public function index(Request $request)
    {
        $status = $request->query('status');
        $counts = Order::where('status', '!=', Order::STATUS_DRAFT)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $orders = Order::with('user')->withCount('items')
            ->where('status', '!=', Order::STATUS_DRAFT)
            ->when($status && isset(Order::STATUS_LABELS[$status]) && $status !== Order::STATUS_DRAFT,
                fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders', 'counts', 'status'));
    }

    public function show(Order $order)
    {
        abort_if($order->status === Order::STATUS_DRAFT, 404);
        $order->load(['user', 'items', 'statusLogs.user']);

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

        try {
            $order->transitionTo($rule['to'], $request->user(), $data['note'] ?? null, $attributes);
        } catch (InvalidOrderTransition $e) {
            return back()->withErrors(['order' => 'Tindakan ini tidak bisa dilakukan pada status pesanan sekarang.']);
        }

        return redirect()->route('admin.orders.show', $order)->with('success', $rule['message']);
    }
}
