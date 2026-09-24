@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Pengaturan Kontak & Footer</h6></div>
        <div class="card-body">
            
            @if(session('success'))
                <div class="alert alert-success text-white fw-bold">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.kontak.update', $kontak->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-primary fw-bold">Deskripsi Singkat (Muncul di Kiri Footer)</label>
                        <textarea class="form-control" name="deskripsi" rows="3">{{ $kontak->deskripsi }}</textarea>
                    </div>
                    
                    <hr class="my-4">
                    <h6 class="text-primary fw-bold mb-3">Informasi Kontak</h6>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat Lengkap</label>
                        <input type="text" class="form-control" name="alamat" value="{{ $kontak->alamat }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Utama</label>
                        <input type="email" class="form-control" name="email" value="{{ $kontak->email }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No Telepon / WhatsApp</label>
                        <input type="text" class="form-control" name="telepon" value="{{ $kontak->telepon }}">
                    </div>

                    <hr class="my-4">
                    <h6 class="text-primary fw-bold mb-3">Sosial Media (Opsional)</h6>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Link Facebook</label>
                        <input type="url" class="form-control" name="facebook" value="{{ $kontak->facebook }}" placeholder="https://facebook.com/...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Link Instagram</label>
                        <input type="url" class="form-control" name="instagram" value="{{ $kontak->instagram }}" placeholder="https://instagram.com/...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Link YouTube</label>
                        <input type="url" class="form-control" name="youtube" value="{{ $kontak->youtube }}" placeholder="https://youtube.com/...">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
@endsection