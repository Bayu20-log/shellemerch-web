@extends('Layout.main')
@section('content')
@php $status = $order->status; @endphp
<div class="container-fluid py-4">
    <a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">&larr; Semua pesanan</a>

    @if(session('success'))<div class="alert alert-success mt-3">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger mt-3">{{ $errors->first() }}</div>@endif

    <div class="d-flex flex-wrap align-items-center gap-3 my-3">
        <h4 class="mb-0">{{ $order->order_code }}</h4>
        <span class="badge rounded-pill {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
        <span class="text-secondary small">Dibuat {{ $order->created_at->format('d/m/Y H:i') }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header pb-0"><h6>Item pesanan</h6></div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead><tr><th class="ps-3">Desain</th><th>Ukuran</th><th class="text-center">Jumlah</th><th class="text-end">Harga</th><th class="text-end pe-3">Subtotal</th></tr></thead>
                            <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('customer.orders.items.design', [$order, $item]) }}" target="_blank" rel="noopener" title="Buka ukuran penuh">
                                            <img src="{{ route('customer.orders.items.design', [$order, $item]) }}" alt="Desain {{ $loop->iteration }}" style="width: 72px; height: 72px; object-fit: cover; border-radius: 8px; border: 1px solid #dee2e6;" loading="lazy">
                                        </a>
                                    </td>
                                    <td>{{ $item->size_name }}@if($item->notes)<div class="small text-secondary">{{ $item->notes }}</div>@endif</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end pe-3 fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot><tr><td colspan="4" class="text-end fw-semibold">Total</td><td class="text-end pe-3 fw-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0"><h6>Bukti pembayaran</h6></div>
                <div class="card-body">
                    @if($order->payment_proof)
                        <a href="{{ route('customer.orders.proof', $order) }}" target="_blank" rel="noopener">
                            <img src="{{ route('customer.orders.proof', $order) }}" alt="Bukti pembayaran" style="max-height: 320px; max-width: 100%; border: 1px solid #dee2e6; border-radius: 8px;" loading="lazy">
                        </a>
                        <div class="small text-secondary mt-2">Dikirim {{ $order->paid_at?->format('d/m/Y H:i') }}. Cocokkan nominal Rp {{ number_format($order->total, 0, ',', '.') }} dengan mutasi rekening/e-wallet toko.</div>
                    @else
                        <p class="text-secondary mb-0">Belum ada bukti pembayaran.</p>
                    @endif
                    @if($order->payment_note)
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">Alasan penolakan terakhir: {{ $order->payment_note }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header pb-0"><h6>Pelanggan</h6></div>
                <div class="card-body">
                    <div class="fw-semibold">{{ $order->user->name }}</div>
                    <div class="small text-secondary">{{ $order->user->email }}</div>
                    @if($order->user->phone)
                        <div class="small mt-1">{{ $order->user->phone }}
                            @if($wa = $order->user->whatsappUrl())
                                &middot; <a href="{{ $wa }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0"><h6>Tindakan</h6></div>
                <div class="card-body d-grid gap-3">
                    @if($status === 'menunggu_verifikasi')
                        <form action="{{ route('admin.orders.transition', $order) }}" method="POST" onsubmit="return confirm('Konfirmasi pembayaran pesanan ini?')">
                            @csrf<input type="hidden" name="action" value="confirm">
                            <button class="btn btn-success w-100">Konfirmasi pembayaran</button>
                        </form>
                        <form action="{{ route('admin.orders.transition', $order) }}" method="POST">
                            @csrf<input type="hidden" name="action" value="reject">
                            <label class="form-label small mb-1" for="reject-note">Tolak bukti (alasan dilihat pelanggan)</label>
                            <textarea id="reject-note" name="note" rows="2" maxlength="500" class="form-control mb-2" required></textarea>
                            <button class="btn btn-outline-danger w-100">Tolak bukti</button>
                        </form>
                    @elseif($status === 'diproses')
                        <form action="{{ route('admin.orders.transition', $order) }}" method="POST" onsubmit="return confirm('Tandai pesanan selesai? Pelanggan akan melihat pesanan siap diambil.')">
                            @csrf<input type="hidden" name="action" value="done">
                            <button class="btn btn-success w-100">Tandai selesai</button>
                        </form>
                    @elseif($status === 'selesai')
                        <form action="{{ route('admin.orders.transition', $order) }}" method="POST" onsubmit="return confirm('Tandai pesanan sudah diambil pelanggan?')">
                            @csrf<input type="hidden" name="action" value="picked_up">
                            <button class="btn btn-dark w-100">Tandai sudah diambil</button>
                        </form>
                    @elseif($status === 'menunggu_pembayaran')
                        <p class="small text-secondary mb-0">Menunggu pelanggan membayar dan mengunggah bukti.</p>
                    @else
                        <p class="small text-secondary mb-0">Tidak ada tindakan lagi untuk status ini.</p>
                    @endif

                    @if(in_array($status, ['menunggu_pembayaran', 'menunggu_verifikasi', 'diproses']))
                        <hr class="my-1">
                        <form action="{{ route('admin.orders.transition', $order) }}" method="POST">
                            @csrf<input type="hidden" name="action" value="cancel">
                            <label class="form-label small mb-1" for="cancel-note">Batalkan pesanan (alasan wajib)</label>
                            <textarea id="cancel-note" name="note" rows="2" maxlength="500" class="form-control mb-2" required></textarea>
                            <button class="btn btn-outline-danger w-100" onclick="return confirm('Batalkan pesanan ini?')">Batalkan pesanan</button>
                            <div class="form-text">Pengembalian dana, jika ada, dilakukan di luar sistem.</div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0"><h6>Riwayat status</h6></div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-0">
                        @foreach($order->statusLogs->reverse() as $log)
                            <li class="mb-2">
                                <div class="fw-semibold">{{ $log->label() }}</div>
                                <div class="text-secondary">{{ $log->created_at->format('d/m/Y H:i') }} &middot; {{ $log->user_id === null ? 'sistem' : ($log->user_id === $order->user_id ? 'pelanggan' : 'admin ' . $log->user?->name) }}</div>
                                @if($log->note)<div>{{ $log->note }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
