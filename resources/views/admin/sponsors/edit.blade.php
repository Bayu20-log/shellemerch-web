@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Edit Sponsor: {{ $sponsor->name }}</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.sponsors.update', $sponsor->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Nama Sponsor</label>
                    <input type="text" class="form-control" name="name" value="{{ $sponsor->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi Sponsor</label>
                    <textarea class="form-control" name="description" rows="4">{{ $sponsor->description }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Logo Sponsor (Kosongkan jika tidak diganti)</label><br>
                    
                    <img id="imagePreview" src="{{ asset('storage/' . $sponsor->logo) }}" class="img-thumbnail mb-2" style="max-height: 150px;" alt="Preview Logo">
                    
                    <input type="file" class="form-control" name="logo" id="imageInput" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Update Sponsor</button>
                <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">Batal</a>
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