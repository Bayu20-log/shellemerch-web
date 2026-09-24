@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Edit Hero Slide</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.heroes.update', $hero->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Ganti Gambar Slide (Kosongkan jika tidak diganti)</label>
                    <input type="file" class="form-control" name="image" id="imageInput" accept="image/*">
                    
                    <div class="mt-3">
                        <img id="imagePreview" src="{{ asset('storage/' . $hero->image) }}" class="img-thumbnail" style="max-height: 250px;" alt="Preview">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update Slide</button>
                <a href="{{ route('admin.heroes.index') }}" class="btn btn-secondary">Batal</a>
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