@extends('Layout.main')
@section('content')
@php $opts = \App\Models\PinSize::AVAILABILITY_LABELS; @endphp
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header pb-0"><h6>Tambah ukuran pin</h6></div>
        <div class="card-body">
            <form action="{{ route('admin.pin-sizes.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label small" for="new-name">Nama ukuran</label>
                    <input id="new-name" type="text" name="name" class="form-control" maxlength="100" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="new-price">Harga per pcs (Rp)</label>
                    <input id="new-price" type="number" name="price" class="form-control" min="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small" for="new-stock">Stok</label>
                    <input id="new-stock" type="number" name="stock" class="form-control" min="0" placeholder="Tak terbatas">
                </div>
                <div class="col-md-3">
                    <label class="form-label small" for="new-availability">Status stok</label>
                    <select id="new-availability" name="availability" class="form-select">
                        @foreach($opts as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6>Ukuran & harga pin custom</h6>
            <p class="text-xs text-secondary mb-0">Pelanggan hanya bisa memesan ukuran berstatus "Tersedia". "Segera hadir" tetap tampil sebagai pratinjau tapi tidak bisa dipesan. Kolom Stok dikosongkan berarti tak terbatas (tidak dilacak); diisi angka akan berkurang otomatis tiap dipesan, dan otomatis jadi "Habis" saat mencapai 0.</p>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Nama ukuran</th>
                            <th>Harga per pcs (Rp)</th>
                            <th>Stok</th>
                            <th>Status stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($sizes as $size)
                        <tr>
                            <td class="ps-3">
                                <form action="{{ route('admin.pin-sizes.update', $size) }}" method="POST" id="upd-{{ $size->id }}">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <input form="upd-{{ $size->id }}" type="text" name="name" value="{{ $size->name }}" class="form-control form-control-sm" maxlength="100" required aria-label="Nama ukuran">
                            </td>
                            <td><input form="upd-{{ $size->id }}" type="number" name="price" value="{{ $size->price }}" class="form-control form-control-sm" min="0" required aria-label="Harga per pcs"></td>
                            <td style="max-width: 110px;"><input form="upd-{{ $size->id }}" type="number" name="stock" value="{{ $size->stock }}" class="form-control form-control-sm" min="0" placeholder="Tak terbatas" aria-label="Stok"></td>
                            <td>
                                <select form="upd-{{ $size->id }}" name="availability" class="form-select form-select-sm" aria-label="Status stok">
                                    @foreach($opts as $val => $label)
                                        <option value="{{ $val }}" @selected($size->availability === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-nowrap">
                                <button form="upd-{{ $size->id }}" type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                <form action="{{ route('admin.pin-sizes.destroy', $size) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ukuran ini? Pesanan lama tidak terpengaruh.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-xs">Belum ada ukuran. Tambahkan ukuran agar pelanggan bisa memesan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
