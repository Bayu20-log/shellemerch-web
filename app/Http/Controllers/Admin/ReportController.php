<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Omzet = pesanan yang pembayarannya sudah dikonfirmasi admin. Dibatalkan / belum terverifikasi tidak dihitung.
    public const VALID_STATUSES = [Order::STATUS_PROCESSING, Order::STATUS_DONE, Order::STATUS_PICKED_UP];

    private const MAX_DAYS = 731;
    private const DEFAULT_DAYS = 30;
    private const MONTHLY_ABOVE_DAYS = 62;

    public function index(Request $request)
    {
        $to = $this->parseDate($request->query('to')) ?? now()->startOfDay();
        $from = $this->parseDate($request->query('from')) ?? $to->copy()->subDays(self::DEFAULT_DAYS - 1);
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }
        if ((int) $from->diffInDays($to, true) >= self::MAX_DAYS) {
            $from = $to->copy()->subDays(self::MAX_DAYS - 1);
        }

        $start = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();
        $days = (int) $from->diffInDays($to, true) + 1;
        $prevStart = $start->copy()->subDays($days);
        $prevEnd = $start->copy()->subSecond();

        $orders = $this->validOrders(Order::query(), $start, $end)->get(['orders.id', 'orders.user_id', 'orders.total', 'orders.paid_at', 'orders.created_at']);
        $revenue = (int) $orders->sum('total');
        $count = $orders->count();

        $prev = $this->validOrders(Order::query(), $prevStart, $prevEnd);
        $prevRevenue = (int) (clone $prev)->sum('orders.total');
        $prevCount = (clone $prev)->count();

        // Rincian per ukuran
        $sizes = $this->validOrders(DB::table('orders'), $start, $end)
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->groupBy('order_items.size_name')
            ->selectRaw('order_items.size_name as name, sum(order_items.quantity) as pcs, sum(order_items.unit_price * order_items.quantity) as revenue, count(distinct orders.id) as orders')
            ->orderByDesc('pcs')->orderBy('name')->get();
        $pcs = (int) $sizes->sum('pcs');

        // Pelanggan
        $byCustomer = $orders->groupBy('user_id')->map(fn ($g) => ['orders' => $g->count(), 'revenue' => (int) $g->sum('total')]);
        $names = User::whereIn('id', $byCustomer->keys())->pluck('name', 'id');
        $topCustomers = $byCustomer->sortByDesc('revenue')->take(5)
            ->map(fn ($row, $id) => $row + ['name' => $names[$id] ?? '-'])->values();

        // Grafik: harian, atau bulanan bila rentang panjang
        $monthly = $days > self::MONTHLY_ABOVE_DAYS;
        $key = $monthly ? 'Y-m' : 'Y-m-d';
        $buckets = $orders->groupBy(fn ($o) => ($o->paid_at ?? $o->created_at)->format($key));
        $labels = $seriesRevenue = $seriesCount = [];
        for ($c = $monthly ? $start->copy()->startOfMonth() : $start->copy(); $c->lte($end); $monthly ? $c->addMonthNoOverflow() : $c->addDay()) {
            $bucket = $buckets->get($c->format($key));
            $labels[] = $monthly ? $c->format('M Y') : $c->format('d M');
            $seriesRevenue[] = (int) ($bucket?->sum('total') ?? 0);
            $seriesCount[] = $bucket?->count() ?? 0;
        }

        $pending = Order::whereIn('status', [Order::STATUS_WAITING_VERIFICATION, Order::STATUS_PROCESSING, Order::STATUS_DONE])
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.reports.index', [
            'from' => $from, 'to' => $to, 'days' => $days, 'monthly' => $monthly,
            'revenue' => $revenue, 'count' => $count, 'avg' => $count ? intdiv($revenue, $count) : 0, 'pcs' => $pcs,
            'revenueDelta' => $this->delta($revenue, $prevRevenue), 'countDelta' => $this->delta($count, $prevCount),
            'customers' => $byCustomer->count(), 'repeatCustomers' => $byCustomer->filter(fn ($r) => $r['orders'] >= 2)->count(),
            'sizes' => $sizes, 'topCustomers' => $topCustomers, 'pending' => $pending,
            'chart' => ['labels' => $labels, 'revenue' => $seriesRevenue, 'count' => $seriesCount],
            'presets' => collect([7, 30, 90, 365])->mapWithKeys(fn ($d) => [$d => [
                'from' => now()->startOfDay()->subDays($d - 1)->format('Y-m-d'), 'to' => now()->format('Y-m-d'),
            ]]),
        ]);
    }

    /** Pesanan bernilai: status valid, dan tanggalnya (saat bukti bayar dikirim) berada dalam rentang. */
    private function validOrders($query, Carbon $start, Carbon $end)
    {
        return $query->whereIn('orders.status', self::VALID_STATUSES)
            ->where(fn ($q) => $q->whereBetween('orders.paid_at', [$start, $end])
                ->orWhere(fn ($q2) => $q2->whereNull('orders.paid_at')->whereBetween('orders.created_at', [$start, $end])));
    }

    // Persentase perubahan dibanding periode sebelumnya; null jika periode sebelumnya kosong.
    private function delta(int $current, int $previous): ?int
    {
        return $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : null;
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
}
