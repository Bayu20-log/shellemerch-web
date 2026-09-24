@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card mb-4" style="max-width: 640px;">
        <div class="card-header pb-0"><h6>Pembayaran QRIS</h6></div>
        <div class="card-body">
            <p class="small text-secondary">Gambar ini ditampilkan kepada pelanggan saat membayar. Pelanggan belum bisa lanjut ke pembayaran sebelum QRIS diunggah.</p>

            @if($qrisImage)
                <img src="{{ asset('storage/' . $qrisImage) }}" alt="QRIS saat ini" style="max-width: 220px; border: 1px solid #dee2e6; border-radius: 8px; padding: 6px;" class="mb-3 d-block">
            @else
                <div class="alert alert-warning py-2 small">QRIS belum diunggah.</div>
            @endif

            <form action="{{ route('admin.payment.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" for="qris">{{ $qrisImage ? 'Ganti gambar QRIS' : 'Gambar QRIS' }}</label>
                    <input id="qris" type="file" name="qris" class="form-control @error('qris') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" {{ $qrisImage ? '' : 'required' }}>
                    @error('qris')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">JPG, PNG, atau WEBP, maksimal 4 MB.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="merchant">Nama yang tampil di QRIS <span class="text-secondary">(opsional)</span></label>
                    <input id="merchant" type="text" name="merchant" class="form-control @error('merchant') is-invalid @enderror" value="{{ old('merchant', $merchant) }}" maxlength="100">
                    @error('merchant')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Membantu pelanggan memastikan tujuan pembayaran benar.</div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>
        </div>
    </div>
</div>
@endsection
