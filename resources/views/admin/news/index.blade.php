@extends('Layout.main')
@section('content')
<div class="container-fluid py-4">
    <div class="card mb-4">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
            <h6>Daftar News (Berita)</h6>
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary btn-sm">Tambah Berita</a>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thumbnail</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Judul & Tanggal</th>
                            <th class="text-secondary opacity-7">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($news as $item)
                        <tr>
                            <td>
                                <div class="px-3">
                                    <img src="{{ asset('storage/' . $item->image) }}" class="rounded" style="width: 80px; height: 80px; object-fit: cover;" alt="news">
                                </div>
                            </td>
                            <td>
                                <p class="text-sm font-weight-bold mb-0">{{ $item->title }}</p>
                                <p class="text-xs text-secondary mb-0">{{ $item->published_date ? \Carbon\Carbon::parse($item->published_date)->format('d M Y') : '-' }}</p>
                            </td>
                            <td class="align-middle">
                                <a href="{{ route('admin.news.edit', $item->id) }}" class="text-secondary font-weight-bold text-xs">Edit</a> | 
                                <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger font-weight-bold text-xs border-0 bg-transparent p-0" onclick="return confirm('Hapus berita ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="text-center py-4 text-xs">Belum ada data berita.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection