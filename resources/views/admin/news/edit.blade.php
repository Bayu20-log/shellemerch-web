@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Edit Berita</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Judul Berita</label>
                    <input type="text" class="form-control" name="title" value="{{ $news->title }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal Publikasi</label>
                    <input type="date" class="form-control" name="published_date" value="{{ $news->published_date }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Isi Berita (Content)</label>
                    <textarea class="form-control" name="content" rows="5" required>{{ $news->content }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ganti Gambar Thumbnail (Kosongkan jika tidak diganti)</label><br>
                    
                    <img id="imagePreview" src="{{ asset('storage/' . $news->image) }}" class="img-thumbnail mb-2" style="max-height: 200px;" alt="Preview">
                    <input type="file" class="form-control" name="image" id="imageInput" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Update Berita</button>
                <a href="{{ route('admin.news.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
        }
    });
</script>
@endsection