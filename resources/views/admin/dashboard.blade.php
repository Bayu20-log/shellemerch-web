@extends('Layout.main')

@section('content')
<main>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Dashboard</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active">Selamat Datang di Admin Panel Shellemerch</li>
        </ol>

        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-primary text-white h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small text-uppercase fw-bold">Total Produk</div>
                                <div class="fs-3 fw-bold">{{ $productCount }}</div>
                            </div>
                            <i class="fas fa-box fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between border-0" style="background-color: rgba(0,0,0,0.1);">
                        <a class="small text-white stretched-link text-decoration-none" href="{{ route('admin.products.index') }}">Lihat Produk</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-success text-white h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small text-uppercase fw-bold">Berita Aktif</div>
                                <div class="fs-3 fw-bold">{{ $newsCount }}</div>
                            </div>
                            <i class="fas fa-newspaper fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between border-0" style="background-color: rgba(0,0,0,0.1);">
                        <a class="small text-white stretched-link text-decoration-none" href="{{ route('admin.news.index') }}">Kelola Berita</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-warning text-white h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small text-uppercase fw-bold">Total Sponsor</div>
                                <div class="fs-3 fw-bold">{{ $sponsorCount }}</div>
                            </div>
                            <i class="fas fa-handshake fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between border-0" style="background-color: rgba(0,0,0,0.1);">
                        <a class="small text-white stretched-link text-decoration-none" href="{{ route('admin.sponsors.index') }}">Kelola Sponsor</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card bg-info text-white h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small text-uppercase fw-bold">Hero Slide</div>
                                <div class="fs-3 fw-bold">{{ $heroCount }}</div>
                            </div>
                            <i class="fas fa-images fa-2x text-white-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between border-0" style="background-color: rgba(0,0,0,0.1);">
                        <a class="small text-white stretched-link text-decoration-none" href="{{ route('admin.heroes.index') }}">Kelola Slide</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header bg-white pb-0 border-bottom-0 pt-4 px-4">
                        <h5 class="fw-bold text-primary"><i class="fas fa-info-circle me-2"></i>Informasi Sistem</h5>
                    </div>
                    <div class="card-body px-4">
                        <p class="mb-0">Selamat bekerja! Gunakan menu di sebelah kiri untuk mengelola konten landing page Shellemerch. Setiap perubahan yang Anda simpan akan langsung memperbarui tampilan di halaman depan pengunjung.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection