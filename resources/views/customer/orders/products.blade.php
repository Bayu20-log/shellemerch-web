@extends('Layout.customer')
@section('title', 'Pesan Produk Lainnya')
@section('content')
<a href="{{ route('customer.orders.index') }}" class="text-decoration-none small" style="color: #2A6CA2;">&larr; Pesanan saya</a>
<h1 class="h3 page-title mt-2 mb-4">Pesan Produk Lainnya</h1>

@if($products->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Belum ada produk yang bisa dipesan</p>
        <p class="text-muted mb-0">Untuk pesanan pin custom, gunakan <a href="{{ route('customer.orders.create') }}">Pesan Pin Custom</a>.</p>
    </div>
@else
    <div class="row g-3">
        @foreach($products as $product)
            <div class="col-6 col-lg-4">
                <div class="panel h-100 p-3 d-flex flex-column">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded mb-2" style="width: 100%; aspect-ratio: 1 / 1; object-fit: cover;">
                    <div class="fw-semibold small">{{ $product->name }}</div>
                    <div class="small text-muted mb-2">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                    <a href="{{ route('customer.orders.create.product', $product) }}" class="btn btn-sm btn-brand mt-auto">Pesan</a>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
