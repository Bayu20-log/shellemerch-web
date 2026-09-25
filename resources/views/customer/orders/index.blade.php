@extends('Layout.customer')
@section('title', 'Pesanan Saya')

@push('styles')
<style>
    /* HP: daftar kartu. Layar lebih lebar (>=576px): tabel biasa. Tidak ada yang perlu digeser ke samping. */
    .order-cards { display: block; }
    .order-table { display: none; }
    @media (min-width: 576px) {
        .order-cards { display: none; }
        .order-table { display: block; }
    }
    .order-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; background: #fff; }
    .order-card + .order-card { margin-top: 10px; }
</style>
@endpush

@section('content')
<h1 class="h3 page-title mb-3">Pesanan Saya</h1>

<div class="row g-2 mb-4">
    <div class="col-6">
        <a href="{{ route('customer.orders.create') }}" class="btn btn-brand w-100 h-100 py-3">
            <div>Pesan Pin Custom</div>
            <div class="small fw-normal opacity-75">Unggah desain &amp; pilih ukuran</div>
        </a>
    </div>
    <div class="col-6">
        <a href="{{ route('customer.orders.products') }}" class="btn btn-outline-brand w-100 h-100 py-3">
            <div>Pesan Produk Lainnya</div>
            <div class="small fw-normal opacity-75">Dari katalog produk kami</div>
        </a>
    </div>
</div>

@if($orders->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Belum ada pesanan</p>
        <p class="text-muted mb-4">Unggah foto desain, pilih ukuran, dan pesanan Anda akan tercatat di sini.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="{{ route('customer.orders.create') }}" class="btn btn-brand">Pesan Pin Custom</a>
            <a href="{{ route('customer.orders.products') }}" class="btn btn-outline-brand">Pesan Produk Lainnya</a>
        </div>
    </div>
@else
    @php $actionLabel = fn ($o) => match ($o->status) { 'draft' => 'Lanjutkan', 'menunggu_pembayaran' => 'Bayar', default => 'Lihat' }; @endphp

    {{-- Tampilan HP: kartu bertumpuk --}}
    <div class="order-cards">
        @foreach($orders as $order)
            <div class="order-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-semibold">{{ $order->order_code }}</div>
                        <div class="small text-muted">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</div>
                    </div>
                    <span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <div class="small text-muted">{{ $order->items_count }} item &middot; Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                    <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-brand">{{ $actionLabel($order) }}</a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Layar lebih lebar: tabel --}}
    <div class="panel order-table">
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
                    <td class="pe-4 text-end"><a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-brand">{{ $actionLabel($order) }}</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
