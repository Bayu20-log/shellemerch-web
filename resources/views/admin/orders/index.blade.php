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
@endphp
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6 class="mb-3">Pesanan masuk</h6>
            <ul class="nav nav-pills mb-3 gap-1">
                @foreach($tabs as $key => $label)
                    @php $count = $key === '' ? $counts->sum() : ($counts[$key] ?? 0); @endphp
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ (string) $status === (string) $key ? 'active' : '' }}"
                           href="{{ route('admin.orders.index', $key === '' ? [] : ['status' => $key]) }}">
                            {{ $label }} <span class="badge text-bg-light ms-1">{{ $count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Kode</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Item</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th><span class="visually-hidden">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $order->order_code }}</td>
                            <td class="small text-secondary">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="text-center">{{ $order->items_count }}</td>
                            <td class="text-end">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td><span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                            <td class="text-end pe-3"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-xs">Belum ada pesanan pada kategori ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
