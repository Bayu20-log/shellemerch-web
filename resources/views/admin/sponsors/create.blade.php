@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Tambah Sponsor Baru</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.sponsors.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Sponsor</label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi Sponsor</label>
                    <textarea class="form-control" name="description" rows="4"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo Sponsor (Maksimal 2MB)</label>
                    <input type="file" class="form-control" name="logo" id="imageInput" accept="image/*" required>
                    
                    <div class="mt-3">
                        <img id="imagePreview" src="#" alt="Preview Logo" class="img-thumbnail" style="display: none; max-height: 150px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Sponsor</button>
                <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const preview = document.getElementById('imagePreview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        }
    });
</script>
@endsection