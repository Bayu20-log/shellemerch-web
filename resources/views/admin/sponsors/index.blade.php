@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h6>Daftar Sponsor / Partner</h6>
            <a href="{{ route('admin.sponsors.create') }}" class="btn btn-primary btn-sm">Tambah Sponsor</a>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Logo</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Sponsor</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Deskripsi</th>
                            <th class="text-secondary opacity-7">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sponsors as $item)
                        <tr>
                            <td>
                                <div class="px-3">
                                    <img src="{{ asset('storage/' . $item->logo) }}" style="height: 50px; width: auto; object-fit: contain;" alt="logo sponsor">
                                </div>
                            </td>
                            <td><p class="text-sm font-weight-bold mb-0">{{ $item->name }}</p></td>
                            <td>
                                <p class="text-xs text-secondary mb-0">{{ Str::limit($item->description, 40) }}</p>
                            </td>
                            <td class="align-middle">
                                <a href="{{ route('admin.sponsors.edit', $item->id) }}" class="text-secondary font-weight-bold text-xs">Edit</a> | 
                                <form action="{{ route('admin.sponsors.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger font-weight-bold text-xs border-0 bg-transparent p-0" onclick="return confirm('Hapus sponsor ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-4 text-xs">Belum ada data sponsor.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection