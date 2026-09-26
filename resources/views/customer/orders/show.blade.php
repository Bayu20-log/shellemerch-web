@extends('Layout.customer')
@section('title', 'Pesanan ' . $order->order_code)

@push('styles')
<style>
    .tracker { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; }
    .tracker li { flex: 1; text-align: center; font-size: 12px; color: #94a3b8; position: relative; padding-top: 22px; }
    .tracker li::before { content: ''; position: absolute; top: 0; left: 50%; transform: translateX(-50%); width: 14px; height: 14px; border-radius: 50%; background: #e2e8f0; border: 2px solid #e2e8f0; z-index: 1; }
    .tracker li::after { content: ''; position: absolute; top: 6px; left: -50%; width: 100%; height: 2px; background: #e2e8f0; }
    .tracker li:first-child::after { display: none; }
    .tracker li.is-done { color: #2A6CA2; }
    .tracker li.is-done::before, .tracker li.is-current::before { background: #2A6CA2; border-color: #2A6CA2; }
    .tracker li.is-done::after, .tracker li.is-current::after { background: #2A6CA2; }
    .tracker li.is-current { color: #1e4e78; font-weight: 600; }
    .tracker li.is-current::before { box-shadow: 0 0 0 4px rgba(42, 108, 162, 0.2); }
    .qris-img { max-width: 260px; width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; padding: 8px; }
    .log-list { list-style: none; padding: 0; margin: 0; }
    .log-list li { padding: 8px 0 8px 16px; border-left: 2px solid #e2e8f0; position: relative; }
    .log-list li::before { content: ''; position: absolute; left: -6px; top: 14px; width: 10px; height: 10px; border-radius: 50%; background: #2A6CA2; }
    /* HP: daftar kartu untuk item pesanan. Layar lebih lebar: tabel biasa. */
    .item-cards { display: block; }
    .item-table { display: none; }
    @media (min-width: 576px) {
        .item-cards { display: none; }
        .item-table { display: block; }
    }
    .item-card { border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; background: #fff; display: flex; gap: 12px; align-items: flex-start; }
    .item-card + .item-card { margin-top: 10px; }
</style>
@endpush

@section('content')
@php $step = $order->progressStep(); @endphp

<a href="{{ route('customer.orders.index') }}" class="text-decoration-none small" style="color: #2A6CA2;">&larr; Pesanan saya</a>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-2 mb-4">
    <div>
        <h1 class="h3 page-title mb-1">{{ $order->order_code }}</h1>
        <span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
        <span class="text-muted small ms-2">Dibuat {{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
    </div>
    @if($order->isEditable())
        @php $isProductOnly = $order->items->isNotEmpty() && $order->items->every(fn ($i) => ! $i->isCustomPin()); @endphp
        <a href="{{ $isProductOnly ? route('customer.orders.index') : route('customer.orders.create') }}" class="btn btn-outline-brand">Tambah item lagi</a>
    @endif
</div>

@if($step !== null)
    <div class="panel p-4 mb-4">
        <ol class="tracker" aria-label="Tahapan pesanan">
            @foreach(\App\Models\Order::PROGRESS_STEPS as $i => $label)
                <li class="{{ $i < $step ? 'is-done' : ($i === $step ? 'is-current' : '') }}" @if($i === $step) aria-current="step" @endif>{{ $label }}</li>
            @endforeach
        </ol>
    </div>
@endif

{{-- Pesan sesuai status --}}
@if($order->status === 'menunggu_verifikasi')
    <div class="alert alert-info">Bukti pembayaran sudah kami terima. Admin sedang memeriksanya. Halaman ini akan menampilkan status terbaru.</div>
@elseif($order->status === 'diproses')
    <div class="alert alert-primary">Pembayaran terkonfirmasi. Pin Anda sedang dibuat.</div>
@elseif($order->status === 'selesai')
    <div class="alert alert-success">
        <strong>Pesanan sudah selesai dan siap diambil.</strong> Tunjukkan kode <strong>{{ $order->order_code }}</strong> saat pengambilan.
    </div>
@elseif($order->status === 'diambil')
    <div class="alert alert-secondary">Pesanan ini sudah diambil. Terima kasih sudah memesan!</div>
@elseif($order->status === 'dibatalkan')
    @php $cancelLog = $order->statusLogs->firstWhere('to_status', 'dibatalkan'); @endphp
    <div class="alert alert-danger">Pesanan ini dibatalkan.@if($cancelLog?->note) Alasan: {{ $cancelLog->note }}@endif</div>
@endif

@if($order->items->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Pesanan ini masih kosong</p>
        <p class="text-muted mb-4">Tambahkan item pin custom untuk mulai.</p>
        <div><a href="{{ route('customer.orders.create') }}" class="btn btn-brand">Tambah item</a></div>
    </div>
@else
    {{-- Tampilan HP: kartu --}}
    <div class="item-cards">
        @foreach($order->items as $item)
            <div class="item-card">
                    @if($item->isCustomPin())
                        <a href="{{ route('customer.orders.items.design', [$order, $item]) }}" target="_blank" rel="noopener" class="flex-shrink-0">
                            <img src="{{ route('customer.orders.items.design', [$order, $item]) }}" class="thumb" alt="Desain item {{ $loop->iteration }}" loading="lazy">
                        </a>
                    @elseif($item->product)
                        <img src="{{ asset('storage/' . $item->product->image) }}" class="thumb flex-shrink-0" alt="{{ $item->product->name }}" loading="lazy">
                    @else
                        <div class="thumb flex-shrink-0"></div>
                    @endif
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $item->size_name }}</div>
                    @if($item->notes)<div class="small text-muted">{{ $item->notes }}</div>@endif
                    <div class="small text-muted mt-1">{{ $item->quantity }} pcs &times; Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <span class="fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        @if($order->isEditable())
                            <form action="{{ route('customer.orders.items.destroy', [$order, $item]) }}" method="POST" onsubmit="return confirm('Hapus item ini dari pesanan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger text-decoration-none p-0">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
        <div class="d-flex justify-content-between align-items-center mt-3 px-1">
            <span class="fw-semibold">Total</span>
            <span class="fs-5 fw-bold" style="color: #2A6CA2;">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- Layar lebih lebar: tabel --}}
    <div class="panel item-table">
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
                            @if($item->isCustomPin())
                                <a href="{{ route('customer.orders.items.design', [$order, $item]) }}" target="_blank" rel="noopener">
                                    <img src="{{ route('customer.orders.items.design', [$order, $item]) }}" class="thumb" alt="Desain item {{ $loop->iteration }}" loading="lazy">
                                </a>
                            @elseif($item->product)
                                <img src="{{ asset('storage/' . $item->product->image) }}" class="thumb" alt="{{ $item->product->name }}" loading="lazy">
                            @else
                                <div class="thumb"></div>
                            @endif
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

{{-- Draft: lanjut ke pembayaran --}}
@if($order->status === 'draft' && $order->items->isNotEmpty())
    <div class="d-flex flex-wrap justify-content-end align-items-center gap-3 mt-4">
        <span class="text-muted small">Sudah lengkap? Item tidak bisa diubah setelah pembayaran dikirim.</span>
        <form action="{{ route('customer.orders.checkout', $order) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-brand">Lanjut ke pembayaran</button>
        </form>
    </div>
@endif

{{-- Menunggu pembayaran: QRIS + unggah bukti --}}
@if($order->status === 'menunggu_pembayaran')
    <div class="panel p-4 p-md-5 mt-4">
        <h2 class="h5 fw-bold mb-3">Bayar dengan QRIS</h2>

        @if($order->payment_note)
            <div class="alert alert-warning">
                <strong>Bukti pembayaran sebelumnya ditolak.</strong> {{ $order->payment_note }}
                <div class="small mt-1">Silakan bayar sesuai total, lalu unggah bukti yang benar.</div>
            </div>
        @endif

        <div class="row g-4 align-items-center">
            <div class="col-md-5 text-center">
                @if($qris['ready'])
                    <img src="{{ $qris['url'] }}" class="qris-img" alt="QRIS pembayaran Shellemerch">
                    @if($qris['merchant'])<div class="small text-muted mt-2">Atas nama {{ $qris['merchant'] }}</div>@endif
                @else
                    <div class="alert alert-warning mb-0">QRIS sedang tidak tersedia. Silakan hubungi kami lewat WhatsApp.</div>
                @endif
            </div>
            <div class="col-md-7">
                <p class="text-muted mb-1">Total yang harus dibayar</p>
                <p class="fs-2 fw-bold mb-3" style="color: #2A6CA2;">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                <ol class="text-muted ps-3 mb-0">
                    <li>Scan QRIS dengan aplikasi bank atau e-wallet.</li>
                    <li>Bayar tepat sesuai total di atas.</li>
                    <li>Simpan bukti, lalu unggah di bawah.</li>
                </ol>
            </div>
        </div>

        <hr class="my-4">

        <form action="{{ route('customer.orders.pay', $order) }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            <label for="proof" class="form-label fw-semibold">Bukti pembayaran</label>
            <div class="d-flex flex-wrap gap-2">
                <input type="file" id="proof" name="proof" class="form-control @error('proof') is-invalid @enderror" style="max-width: 420px;" accept="image/jpeg,image/png,image/webp" required>
                <button type="submit" class="btn btn-brand" @disabled(! $qris['ready'])>Kirim bukti pembayaran</button>
            </div>
            @error('proof')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            <div class="form-text">JPG, PNG, atau WEBP, maksimal 5 MB.</div>
        </form>

        <div class="d-flex flex-wrap gap-2 mt-4">
            <form action="{{ route('customer.orders.reopen', $order) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-brand">Ubah pesanan</button>
            </form>
            <form action="{{ route('customer.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Batalkan pesanan ini?')">
                @csrf
                <button type="submit" class="btn btn-link text-danger text-decoration-none">Batalkan pesanan</button>
            </form>
        </div>
    </div>
@endif

{{-- Bukti yang sudah dikirim --}}
@if($order->payment_proof && in_array($order->status, ['menunggu_verifikasi', 'diproses', 'selesai', 'diambil']))
    <div class="panel p-4 mt-4 d-flex align-items-center gap-3">
        <a href="{{ route('customer.orders.proof', $order) }}" target="_blank" rel="noopener">
            <img src="{{ route('customer.orders.proof', $order) }}" class="thumb" alt="Bukti pembayaran" loading="lazy">
        </a>
        <div>
            <div class="fw-semibold">Bukti pembayaran terkirim</div>
            <div class="small text-muted">{{ $order->paid_at?->translatedFormat('d M Y, H:i') }}</div>
        </div>
    </div>
@endif

{{-- Riwayat status --}}
<div class="panel p-4 mt-4">
    <h2 class="h6 fw-bold mb-3">Riwayat pesanan</h2>
    <ul class="log-list small">
        @foreach($order->statusLogs->reverse() as $log)
            <li>
                <div class="fw-semibold">{{ $log->label() }}</div>
                <div class="text-muted">
                    {{ $log->created_at->translatedFormat('d M Y, H:i') }}
                    @if($log->user_id && $log->user_id !== $order->user_id) &middot; oleh admin @endif
                </div>
                @if($log->note)<div>{{ $log->note }}</div>@endif
            </li>
        @endforeach
    </ul>
</div>
@endsection
