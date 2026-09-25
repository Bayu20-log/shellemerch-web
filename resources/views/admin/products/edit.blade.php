@extends('Layout.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Edit Produk: {{ $product->name }}</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') 
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga (Angka saja)</label>
                            <input type="number" class="form-control" id="price" name="price" value="{{ $product->price }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="availability" class="form-label">Status stok</label>
                            <select class="form-select" id="availability" name="availability">
                                @foreach(\App\Models\Product::AVAILABILITY_LABELS as $val => $label)
                                    <option value="{{ $val }}" @selected(old('availability', $product->availability) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar Produk Baru (Kosongkan jika tidak ingin mengganti gambar)</label>
                            <br>
                            <img id="imagePreview" src="{{ asset('storage/' . $product->image) }}" alt="Gambar Produk" class="img-thumbnail mb-2" style="max-height: 200px;">
                            <input type="file" class="form-control" id="imageInput" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Produk</button>
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
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
        }
    });
</script>
@endsection