@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h6>Daftar Hero Slide</h6>
            <a href="{{ route('admin.heroes.create') }}" class="btn btn-primary btn-sm">Tambah Slide</a>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Gambar Slide</th>
                            <th class="text-secondary opacity-7">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($heroes as $item)
                        <tr>
                            <td>
                                <div class="px-3 py-2">
                                    <img src="{{ asset('storage/' . $item->image) }}" class="rounded shadow-sm" style="height: 100px; width: auto; object-fit: cover;" alt="hero">
                                </div>
                            </td>
                            <td class="align-middle">
                                <a href="{{ route('admin.heroes.edit', $item->id) }}" class="text-secondary font-weight-bold text-xs me-2">Edit</a> 
                                <form action="{{ route('admin.heroes.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger font-weight-bold text-xs border-0 bg-transparent p-0" onclick="return confirm('Hapus slide ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-center py-4 text-xs">Belum ada data slide.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection