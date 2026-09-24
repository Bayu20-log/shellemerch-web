@extends('Layout.main')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6>Daftar Products</h6>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Tambah Produk Baru</a>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gambar</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Produk</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Harga</th>
                                    <th class="text-secondary opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $item)
                                <tr>
                                    <td>
                                        <div class="px-3 py-2">
                                            <img src="{{ asset('storage/' . $item->image) }}" class="rounded shadow-sm" style="width: 70px; height: 70px; object-fit: cover;" alt="produk">
                                        </div>
                                    </td>
                                    <td class="align-middle"><p class="text-sm font-weight-bold mb-0">{{ $item->name }}</p></td>
                                    <td class="align-middle"><p class="text-sm font-weight-bold mb-0 text-primary">Rp {{ number_format($item->price, 0, ',', '.') }}</p></td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.products.edit', $item->id) }}" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit user">
                                            Edit
                                        </a> | 
                                        <form action="{{ route('admin.products.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-danger font-weight-bold text-xs border-0 bg-transparent p-0" onclick="return confirm('Yakin ingin menghapus produk ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-xs">Belum ada data produk.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection