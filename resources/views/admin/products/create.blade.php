@extends('Layout.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Tambah Produk Baru</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga (Angka saja, misal: 150000)</label>
                            <input type="number" class="form-control" id="price" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="availability" class="form-label">Status stok</label>
                            <select class="form-select" id="availability" name="availability">
                                @foreach(\App\Models\Product::AVAILABILITY_LABELS as $val => $label)
                                    <option value="{{ $val }}" @selected(old('availability', 'tersedia') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Produk</label>
                            <input type="file" class="form-control" id="imageInput" name="image" accept="image/*" required>
                            
                            <div class="mt-3">
                                <img id="imagePreview" src="#" alt="Preview Gambar" class="img-thumbnail" style="display: none; max-height: 200px;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan Produk</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
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