@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Tambah Berita Baru</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Judul Berita</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Publikasi</label>
                    <input type="date" class="form-control" name="published_date">
                </div>
                <div class="mb-3">
                    <label class="form-label">Isi Berita (Content)</label>
                    <textarea class="form-control" name="content" rows="5" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Thumbnail</label>
                    <input type="file" class="form-control" name="image" id="imageInput" accept="image/*" required>
                    
                    <div class="mt-3">
                        <img id="imagePreview" src="#" alt="Preview" class="img-thumbnail" style="display: none; max-height: 200px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Berita</button>
                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">Batal</a>
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