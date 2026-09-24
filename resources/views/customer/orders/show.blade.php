@extends('Layout.customer')
@section('title', 'Pesanan ' . $order->order_code)
@section('content')
<a href="{{ route('customer.orders.index') }}" class="text-decoration-none small" style="color: #2A6CA2;">&larr; Pesanan saya</a>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-2 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">{{ $order->order_code }}</h1>
        <span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
        <span class="text-muted small ms-2">Dibuat {{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
    </div>
    @if($order->isEditable())
        <a href="{{ route('customer.orders.create') }}" class="btn btn-outline-brand">Tambah item lagi</a>
    @endif
</div>

@if($order->items->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Pesanan ini masih kosong</p>
        <p class="text-muted mb-4">Tambahkan item pin custom untuk mulai.</p>
        <div><a href="{{ route('customer.orders.create') }}" class="btn btn-brand">Tambah item</a></div>
    </div>
@else
    <div class="panel">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th class="ps-4">Desain</th>
                        <th>Ukuran</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Subtotal</th>
                        @if($order->isEditable())<th class="pe-4"><span class="visually-hidden">Aksi</span></th>@endif
                    </tr>
                </thead>
                <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('customer.orders.items.design', [$order, $item]) }}" target="_blank" rel="noopener">
                                <img src="{{ route('customer.orders.items.design', [$order, $item]) }}" class="thumb" alt="Desain item {{ $loop->iteration }}" loading="lazy">
                            </a>
                        </td>
                        <td>
                            {{ $item->size_name }}
                            @if($item->notes)<div class="small text-muted">{{ $item->notes }}</div>@endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        @if($order->isEditable())
                        <td class="pe-4 text-end">
                            <form action="{{ route('customer.orders.items.destroy', [$order, $item]) }}" method="POST" onsubmit="return confirm('Hapus item ini dari pesanan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none">Hapus</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="ps-4 text-end fw-semibold border-0">Total</td>
                        <td class="text-end fs-5 fw-bold border-0" style="color: #2A6CA2;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        @if($order->isEditable())<td class="border-0"></td>@endif
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif
@endsection
