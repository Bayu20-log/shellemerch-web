@extends('Layout.customer')
@section('title', 'Tambah item pesanan')
@section('content')
<a href="{{ route('customer.orders.index') }}" class="text-decoration-none small" style="color: #2A6CA2;">&larr; Pesanan saya</a>
<h1 class="h3 page-title mt-2 mb-4">Tambah item pin custom</h1>

@if($waitingOrder)
    <div class="alert alert-info">
        Pesanan <strong>{{ $waitingOrder->order_code }}</strong> sedang menunggu pembayaran. Jika Anda menambah item, pesanan itu kembali ke draft dan Anda perlu melanjutkan ke pembayaran lagi.
    </div>
@endif

@if($sizes->isEmpty())
    <div class="panel p-5 text-center">
        <p class="fw-semibold mb-1">Ukuran pin belum tersedia</p>
        <p class="text-muted mb-0">Pemesanan lewat web belum bisa dilakukan. Silakan hubungi kami lewat WhatsApp.</p>
    </div>
@else
    <form action="{{ route('customer.orders.items.store') }}" method="POST" enctype="multipart/form-data" class="panel p-4 p-md-5" novalidate>
        @csrf

        <fieldset class="mb-4">
            <legend class="fs-6 fw-semibold">Ukuran</legend>
            @error('pin_size_id')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            <div class="d-grid gap-2">
                @foreach($sizes as $size)
                    <div>
                        <input type="radio" class="btn-check" name="pin_size_id" id="size-{{ $size->id }}" value="{{ $size->id }}" @checked(old('pin_size_id') == $size->id) required>
                        <label class="size-option" for="size-{{ $size->id }}">
                            <span class="fw-medium">{{ $size->name }}</span>
                            <span class="text-muted">Rp {{ number_format($size->price, 0, ',', '.') }} / pcs</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </fieldset>

        <div class="mb-4">
            <label for="design" class="form-label fw-semibold">Foto desain</label>
            <input type="file" id="design" name="design" class="form-control @error('design') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" required>
            @error('design')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">JPG, PNG, atau WEBP, maksimal 5 MB.</div>
            <img id="design-preview" class="mt-3 rounded d-none" alt="Pratinjau foto desain" style="max-height: 200px; max-width: 100%; border: 1px solid #e2e8f0;">
        </div>

        <div class="mb-4" style="max-width: 200px;">
            <label for="quantity" class="form-label fw-semibold">Jumlah (pcs)</label>
            <input type="number" id="quantity" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" max="1000" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-4">
            <label for="notes" class="form-label fw-semibold">Catatan untuk kami <span class="text-muted fw-normal">(opsional)</span></label>
            <textarea id="notes" name="notes" rows="3" maxlength="500" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $reference ? 'Referensi produk: ' . $reference->name : '') }}</textarea>
            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-brand">Simpan ke pesanan</button>
    </form>
@endif
@endsection

@push('scripts')
<script>
    const input = document.getElementById('design');
    const preview = document.getElementById('design-preview');
    if (input && preview) {
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (file && file.type.startsWith('image/')) {
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('d-none');
            } else {
                preview.classList.add('d-none');
            }
        });
    }
</script>
@endpush
