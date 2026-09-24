@extends('Layout.main')
@section('content')
@php
    $tabs = [
        '' => 'Semua',
        'menunggu_verifikasi' => 'Perlu verifikasi',
        'menunggu_pembayaran' => 'Menunggu bayar',
        'diproses' => 'Diproses',
        'selesai' => 'Siap diambil',
        'diambil' => 'Diambil',
        'dibatalkan' => 'Dibatalkan',
    ];
    $keep = request()->except('page');                 // semua filter aktif
    $keepNoStatus = request()->except('page', 'status');
    $hasFilter = $filters['q'] !== '' || $filters['from'] || $filters['to'] || $filters['status'];
    $th = function (string $key, string $label) use ($sort, $dir, $keep) {
        $active = $sort === $key;
        $url = route('admin.orders.index', array_merge($keep, ['sort' => $key, 'dir' => ($active && $dir === 'asc') ? 'desc' : 'asc']));
        return new \Illuminate\Support\HtmlString(
            '<a href="' . e($url) . '" class="text-decoration-none text-reset">' . e($label)
            . ' <span class="small text-secondary">' . ($active ? ($dir === 'asc' ? '▲' : '▼') : '') . '</span></a>'
        );
    };
    $ariaSort = fn (string $key) => $sort === $key ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none';
@endphp

<style>
    .sheet-wrap { max-height: 68vh; overflow: auto; }
    .sheet-table { font-size: 13px; }
    .sheet-table thead th { position: sticky; top: 0; z-index: 2; background: #f1f5f9; white-space: nowrap; border-bottom: 2px solid #cbd5e1; }
    .sheet-table td, .sheet-table th { padding: .35rem .6rem; }
    .sheet-table tbody tr:hover { background: #eef4fa; }
    .sheet-table .num { text-align: right; font-variant-numeric: tabular-nums; }
</style>

<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 align-items-end">
                @if($filters['status'])<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ $sort }}"><input type="hidden" name="dir" value="{{ $dir }}">@endif
                <div class="col-lg-4">
                    <label class="form-label small mb-1" for="q">Cari</label>
                    <input id="q" type="search" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="Kode pesanan, nama, email, atau no. HP">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small mb-1" for="from">Dari tanggal</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from']?->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-lg-2">
                    <label class="form-label small mb-1" for="to">Sampai tanggal</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to']?->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">Terapkan</button>
                    @if($hasFilter)<a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>@endif
                    <a href="{{ route('admin.orders.export', $keep) }}" class="btn btn-sm btn-success ms-lg-auto">
                        <i class="fas fa-file-excel me-1"></i> Unduh Excel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header pb-0">
            <ul class="nav nav-pills mb-3 gap-1">
                @foreach($tabs as $key => $label)
                    @php $count = $key === '' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ (string) $filters['status'] === (string) $key ? 'active' : '' }}"
                           href="{{ route('admin.orders.index', $key === '' ? $keepNoStatus : array_merge($keepNoStatus, ['status' => $key])) }}">
                            {{ $label }} <span class="badge text-bg-light ms-1">{{ $count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="small text-secondary mb-2">
                {{ number_format($summary['count'], 0, ',', '.') }} pesanan
                &middot; nilai <strong>Rp {{ number_format($summary['value'], 0, ',', '.') }}</strong>
                &middot; {{ number_format($summary['pcs'], 0, ',', '.') }} pcs
                <span class="ms-1">(tidak menghitung yang dibatalkan)</span>
            </p>
        </div>
        <div class="card-body px-0 pt-0 pb-0">
            <div class="sheet-wrap">
                <table class="table table-striped table-sm sheet-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3" aria-sort="{{ $ariaSort('code') }}">{{ $th('code', 'Kode') }}</th>
                            <th aria-sort="{{ $ariaSort('date') }}">{{ $th('date', 'Tanggal') }}</th>
                            <th aria-sort="{{ $ariaSort('customer') }}">{{ $th('customer', 'Pelanggan') }}</th>
                            <th class="num" aria-sort="{{ $ariaSort('items') }}">{{ $th('items', 'Item') }}</th>
                            <th class="num" aria-sort="{{ $ariaSort('pcs') }}">{{ $th('pcs', 'Pcs') }}</th>
                            <th class="num" aria-sort="{{ $ariaSort('total') }}">{{ $th('total', 'Total') }}</th>
                            <th aria-sort="{{ $ariaSort('status') }}">{{ $th('status', 'Status') }}</th>
                            <th><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-3 fw-semibold text-nowrap">{{ $order->order_code }}</td>
                            <td class="text-nowrap text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $order->user->name }}<div class="text-secondary" style="font-size: 12px;">{{ $order->user->phone }}</div></td>
                            <td class="num">{{ $order->items_count }}</td>
                            <td class="num">{{ number_format((int) $order->pcs, 0, ',', '.') }}</td>
                            <td class="num text-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td><span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td class="text-end pe-3"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary py-0">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-4 text-secondary">Tidak ada pesanan yang cocok dengan filter ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-secondary">Menampilkan {{ $orders->firstItem() }}-{{ $orders->lastItem() }} dari {{ $orders->total() }}</span>
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
