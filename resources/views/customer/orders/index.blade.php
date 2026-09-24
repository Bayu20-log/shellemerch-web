@extends('Layout.customer')
@section('title', 'Pesanan Saya')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 page-title mb-0">Pesanan Saya</h1>
    <a href="{{ route('customer.orders.create') }}" class="btn btn-brand">Buat pesanan</a>
</div>

@if($orders->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Belum ada pesanan</p>
        <p class="text-muted mb-4">Unggah foto desain, pilih ukuran, dan pesanan Anda akan tercatat di sini.</p>
        <div><a href="{{ route('customer.orders.create') }}" class="btn btn-brand">Buat pesanan pertama</a></div>
    </div>
@else
    <div class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th class="ps-4">Kode pesanan</th>
                        <th>Tanggal</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th class="pe-4"><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $order->order_code }}</td>
                        <td class="text-muted small">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                        <td class="text-center">{{ $order->items_count }}</td>
                        <td class="text-end">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="pe-4 text-end"><a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-brand">{{ match($order->status) { 'draft' => 'Lanjutkan', 'menunggu_pembayaran' => 'Bayar', default => 'Lihat' } }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
