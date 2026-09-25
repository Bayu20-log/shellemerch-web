@extends('Layout.customer')
@section('title', 'Pesan ' . $product->name)
@section('content')
<a href="{{ route('customer.orders.products') }}" class="text-decoration-none small" style="color: #2A6CA2;">&larr; Produk lainnya</a>
<h1 class="h3 page-title mt-2 mb-4">Pesan {{ $product->name }}</h1>

@if($waitingOrder)
    <div class="alert alert-info">
        Pesanan <strong>{{ $waitingOrder->order_code }}</strong> sedang menunggu pembayaran. Jika Anda menambah item, pesanan itu kembali ke draft dan Anda perlu melanjutkan ke pembayaran lagi.
    </div>
@endif

<div class="panel p-4 p-md-5">
    <div class="d-flex align-items-center gap-3 mb-4">
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded" style="width: 84px; height: 84px; object-fit: cover;">
        <div>
            <div class="fw-semibold">{{ $product->name }}</div>
            <div class="text-muted">Rp {{ number_format($product->price, 0, ',', '.') }} / pcs</div>
        </div>
    </div>

    <form action="{{ route('customer.orders.products.store', $product) }}" method="POST" novalidate>
        @csrf
        <div class="mb-4" style="max-width: 200px;">
            <label for="quantity" class="form-label fw-semibold">Jumlah (pcs)</label>
            <input type="number" id="quantity" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" max="1000" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="notes" class="form-label fw-semibold">Catatan untuk kami <span class="text-muted fw-normal">(opsional)</span></label>
            <textarea id="notes" name="notes" rows="3" maxlength="500" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-brand">Tambahkan ke pesanan</button>
    </form>
</div>
@endsection
