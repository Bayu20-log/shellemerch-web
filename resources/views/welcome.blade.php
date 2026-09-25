<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <title>Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        
        @keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeInUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .animate-nav { animation: slideDown 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        .animate-hero { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.3s forwards; }
        .animate-content { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.6s forwards; }

        .bg-pattern-light { background-color: #f8fafc; background-image: radial-gradient(circle at 1px 1px, rgba(42, 108, 162, 0.12) 1px, transparent 0); background-size: 24px 24px; }
        .bg-pattern-dark { background-color: #2A6CA2; background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.15) 1px, transparent 0); background-size: 24px 24px; }

        /* ================================================== */
        /* NAVBAR & ANIMASI KURSOR (SERAGAM DI SEMUA HALAMAN) */
        /* ================================================== */
        .navbar { padding: 15px 0; background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); z-index: 1000; }
        
        .nav-link { 
            font-weight: 500; 
            color: #4a5568 !important; 
            font-size: 14px; 
            margin: 0 8px; 
            padding-bottom: 5px; 
            transition: color 0.3s ease; 
            text-transform: uppercase; 
            letter-spacing: 0.5px; 
            position: relative; 
        }
        
        .nav-link:hover, .nav-link.active { color: #2A6CA2 !important; }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #2A6CA2;
            border-radius: 2px;
            transform: scaleX(0);
            transform-origin: bottom right;
            transition: transform 0.3s ease-out;
        }

        .nav-link:hover::after, .nav-link.active::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }
        /* ================================================== */

        #hero { padding: 30px 0 60px 0; }
        .hero-img { width: 100%; height: 55vh; min-height: 350px; max-height: 550px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); object-fit: cover; object-position: center; }
        .carousel-indicators { bottom: -50px; }
        .carousel-indicators [data-bs-target] { width: 12px; height: 12px; border-radius: 50%; background-color: #cbd5e1; border: none; margin: 0 6px; transition: 0.3s ease; }
        .carousel-indicators .active { background-color: #0d6efd; transform: scale(1.2); }

        #about { color: #ffffff; padding: 90px 0; }
        .about-title { font-size: 4rem; letter-spacing: 2px; line-height: 1.1; color: #ffffff !important; } 
        .about-subtitle { font-size: 1.5rem; color: #e2e8f0 !important; }
        #about .text-muted { color: #f8fafc !important; opacity: 0.9; }

        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .section-title { font-weight: 700; color: #2d3748; text-transform: uppercase; margin-bottom: 5px; font-size: 2.2rem; }
        .section-title-underline { width: 80px; height: 4px; background-color: #2A6CA2; border-radius: 2px; }
        .slider-nav { display: flex; flex-direction: row; align-items: center; gap: 10px; flex-shrink: 0; }
        .slider-nav button { background: none; border: 2px solid #2A6CA2; color: #2A6CA2; border-radius: 50%; width: 45px; height: 45px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; cursor: pointer; flex-shrink: 0; }
        .slider-nav button:hover { background: #2A6CA2; color: #fff; }
        @media (max-width: 576px) {
            .slider-nav button { width: 38px; height: 38px; }
        }

        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 12px; border: 1px solid #e2e8f0; background: #ffffff; }
        .hover-lift:hover { transform: translateY(-8px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important; border-color: transparent; }

        .produk-card { border-radius: 14px; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06); transition: transform 0.25s ease, box-shadow 0.25s ease; background: #fff; }
        .produk-card:hover { transform: translateY(-6px); box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12); }
        .produk-img-container { width: 100%; height: 180px; background-color: #f8fafc; overflow: hidden; display: flex; justify-content: center; align-items: center; }
        .produk-card-img { width: 100%; height: 100%; object-fit: cover; } 

        .mitra-sponsor-card { overflow: hidden; border-radius: 16px; border: 1px solid #e2e8f0 !important; box-shadow: 0 4px 15px rgba(0,0,0,0.03) !important; background-color: #ffffff; }
        .mitra-sponsor-img-container { height: 180px; background-color: #ffffff; display: flex; justify-content: center; align-items: center; padding: 30px; }
        .mitra-sponsor-img { max-height: 100%; max-width: 100%; object-fit: contain; } 

        #news { padding: 80px 0; } 
        #news .section-title { color: #ffffff; }
        #news .section-title-underline { background-color: #ffffff; }
        .news-img { width: 100%; height: 220px; object-fit: cover; border-radius: 12px 12px 0 0; }

        .swiper { padding-bottom: 40px !important; padding-top: 10px !important; }
        
        /* ==========================================
       POSISI TOMBOL PANAH BANNER (DILUAR GAMBAR)
       ========================================== */
    
        /* 1. Menarik tombol ke kiri luar */
        #heroCarousel .carousel-control-prev {
            left: -60px !important; 
            width: 50px !important;
            opacity: 0.8 !important; /* Membuat panah sedikit transparan */
        }
    
        /* 2. Menarik tombol ke kanan luar */
        #heroCarousel .carousel-control-next {
            right: -60px !important; 
            width: 50px !important;
            opacity: 0.8 !important;
        }
    
        /* 3. Mengubah panah menjadi simbol yang diwarnai biru tema */
        #heroCarousel .carousel-control-prev-icon,
        #heroCarousel .carousel-control-next-icon {
            background-image: none !important; /* Menghapus gambar ikon bawaan putih */
            filter: none !important; /* Menghapus efek hitam sebelumnya */
            color: #2b6cb0 !important; /* Ini adalah warna birunya! Silakan ganti jika kurang pas (bisa pakai #0d6efd atau #1c5b8e) */
            font-size: 3.5rem !important; /* Memperbesar ukuran panahnya */
            font-weight: bold !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    
        /* Memunculkan simbol panah kiri */
        #heroCarousel .carousel-control-prev-icon::after {
            content: "❮"; 
        }
    
        /* Memunculkan simbol panah kanan */
        #heroCarousel .carousel-control-next-icon::after {
            content: "❯"; 
        }
    
        /* 4. KHUSUS HP: Sembunyikan panah agar layar tidak melebar/error 
           (Karena di HP, pengunjung otomatis akan menggeser gambar menggunakan jari/swipe) */
        @media (max-width: 768px) {
            #heroCarousel .carousel-control-prev,
            #heroCarousel .carousel-control-next {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            /* Ganti 'img.banner-class' dengan class gambar banner Anda */
            .carousel-item img, 
            .hero-slider img,
            .swiper-slide img {
                height: auto !important; /* Biarkan tinggi gambar menyesuaikan otomatis */
                max-height: 250px; /* Batasi tinggi maksimal agar tidak terlalu memakan tempat */
                object-fit: contain !important; /* Memaksa seluruh gambar tampil tanpa dipotong */
                width: 100% !important;
            }
        }

        /* Hero (gambar yang diunggah admin) harus tampil UTUH di HP, tidak terpotong. */
        #heroCarousel .carousel-inner { background-color: #eaf1f8; border-radius: 20px; }
        @media (max-width: 768px) {
            #heroCarousel .hero-img {
                height: 300px !important;
                max-height: 300px !important;
                object-fit: contain !important;
            }
            #about img {
                width: 100% !important;
                height: 260px !important;
                max-height: 260px !important;
                object-fit: cover !important;
            }
            #about { padding: 40px 0 !important; }
            .about-title { font-size: 2.4rem !important; }
            .about-subtitle { font-size: 1.15rem !important; }

            /* Jarak antar section dirapatkan supaya pelanggan tak perlu scroll jauh */
            #hero { padding: 16px 0 30px 0 !important; }
            #products, #news { padding: 36px 0 !important; }
            .section-header { margin-bottom: 20px !important; }
            .swiper { padding-bottom: 30px !important; padding-top: 6px !important; }
        }
        
        /* 1. Kunci layar agar tidak bisa digeser ke kanan (hilangkan ruang putih) */
        html, body {
            overflow-x: hidden !important;
            width: 100%;
        }
        
        /* 2. Mengecilkan teks judul panjang khusus di HP */
        @media (max-width: 768px) {
            h1, h2, h3, .text-uppercase { 
                word-wrap: break-word; /* Paksa teks turun ke bawah jika terlalu panjang */
                font-size: 1.8rem !important; /* Sesuaikan ukuran ini jika masih kebesaran */
            }
        }
        
        /* 1. Mengurangi ruang kosong (padding) atas dan bawah pada latar biru */
        footer, .footer, section.bg-primary { 
            padding-top: 30px !important;  /* Bawaannya biasa 80px, kita kecilkan */
            padding-bottom: 10px !important; 
        }
    
        /* 2. Mengecilkan teks paragraf dan merapatkan baris */
        footer p, footer li, footer a, footer .text-white {
            font-size: 0.9rem !important;
            line-height: 1.5 !important;
            margin-bottom: 8px !important;
        }
    
        /* 3. Mengecilkan ukuran Judul Menu (TAUTAN MENU, HUBUNGI KAMI) */
        footer h3, footer h4, footer h5, footer .font-weight-bold {
            font-size: 1.1rem !important;
            margin-bottom: 15px !important;
            margin-top: 10px !important;
        }
    
        /* 4. Khusus layar HP: Merapatkan jarak antar kelompok menu */
        @media (max-width: 768px) {
            footer .col-md-4, footer .col-lg-4, footer .col-12 {
                margin-bottom: 20px !important; /* Mengurangi jarak vertikal di HP */
            }
            
            /* Mengecilkan logo di HP agar tidak mendominasi */
            footer img {
                max-width: 120px !important;
                margin-bottom: 10px !important;
            }
        }
        
    </style>
    @include('Layout.Partial.mobile-nav')
</head>

<body class="bg-pattern-light">

    <nav class="navbar navbar-expand-lg sticky-top animate-nav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('beranda') }}">
                <img src="{{ asset('assets/img/Group.png') }}" alt="Shellemerch Logo" style="height: 45px; object-fit: contain;">
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('beranda') ? 'active' : '' }}" href="{{ route('beranda') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}" href="{{ route('products') }}">Products</a></li>
                    
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">News</a></li>
                    
                    <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#sponsors">Sponsors & Partners</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer">Contact Us</a></li>
                    @include('Layout.Partial.nav_auth')
                </ul>
            </div>
        </div>
    </nav>

    <section id="hero" class="animate-hero" style="margin-top: -25px;">
    <div class="container"> 
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            
            @if(isset($heroes) && $heroes->count() > 1)
            <!-- Titik Indikator Bawah -->
            <div class="carousel-indicators">
                @foreach($heroes as $key => $hero)
                    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $key }}" class="{{ $key == 0 ? 'active' : '' }}"></button>
                @endforeach
            </div>
            @endif

            <!-- Isi Gambar Banner -->
            <div class="carousel-inner" style="border-radius: 20px;">
                @if(isset($heroes) && $heroes->count() > 0)
                    @foreach($heroes as $key => $hero)
                        <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                            <img src="{{ asset('storage/' . $hero->image) }}" class="d-block w-100 hero-img" alt="Hero Slide">
                        </div>
                    @endforeach
                @else
                    <div class="carousel-item active">
                        <div class="bg-white d-flex align-items-center justify-content-center hero-img shadow-sm">
                            <h3 class="text-muted">Belum ada Hero Slide.</h3>
                        </div>
                    </div>
                @endif
            </div>

            @if(isset($heroes) && $heroes->count() > 1)
            <!-- Tombol Geser Kiri -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            
            <!-- Tombol Geser Kanan -->
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
            @endif
        </div>
    </div>
    </section>

    <section id="about" class="bg-pattern-dark animate-content">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0 pe-md-5">
                    <img src="{{ asset('assets/img/Mask.png') }}" class="w-100 shadow-lg" alt="About Us Shellemerch" style="object-fit: cover; border-radius: 20px; max-height: 480px;">
                </div>
                <div class="col-md-6">
                    <h2 class="fw-bold mb-0 about-title" style="text-transform: uppercase;">About Us</h2>
                    <div class="bg-white mb-4 mt-2" style="height: 5px; width: 80px; border-radius: 3px;"></div>
                    
                    <h4 class="fw-semibold mb-4 about-subtitle">Mengenal Lebih Dekat Shellemerch</h4>
                    
                    <p class="text-muted mb-4" style="line-height: 1.8; font-size: 16px;">
                        Shellemerch hadir sebagai platform terpercaya yang menyediakan berbagai produk berkualitas dengan harga kompetitif, didukung tim yang berdedikasi untuk pelayanan terbaik.
                    </p>
                    
                    <a href="{{ route('about') }}" class="btn btn-light px-4 py-2 rounded-pill shadow-sm fw-bold mt-4" style="color: #2A6CA2;">
                        Selanjutnya <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="py-5 bg-pattern-light">
        <div class="container mt-4 mb-4">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Our Products</h2>
                    <div class="section-title-underline"></div>
                </div>
                <div class="slider-nav">
                    <button class="produk-prev"><i class="bi bi-arrow-left"></i></button>
                    <button class="produk-next"><i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            
            <div class="swiper produk-slider">
                <div class="swiper-wrapper">
                    @if(isset($products) && $products->count() > 0)
                        @foreach($products as $product)
                        <div class="swiper-slide h-auto">
                            <div class="card h-100 produk-card border-0">
                                <div class="produk-img-container position-relative">
                                    @if($product->availability !== 'tersedia')
                                        <span class="badge rounded-pill {{ $product->availabilityBadgeClass() }} position-absolute" style="top: 10px; right: 10px; z-index: 2;">{{ $product->availabilityLabel() }}</span>
                                    @endif
                                    <img src="{{ asset('storage/' . $product->image) }}" class="produk-card-img" alt="{{ $product->name }}">
                                </div>
                                <div class="card-body p-3 d-flex flex-column text-start">
                                    <h6 class="card-title fw-bold text-dark mb-1" style="min-height: 2.6em;">{{ Str::limit($product->name, 45) }}</h6>
                                    <span class="fw-bold mb-3" style="color: #2A6CA2; font-size: 1.05rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <div class="mt-auto">
                                        @if($product->isOrderable())
                                            <a href="{{ route('customer.orders.create.product', $product) }}" class="btn btn-sm w-100 fw-semibold" style="background-color: #2A6CA2; color: #fff; border-radius: 8px;">Pesan</a>
                                        @else
                                            <span class="btn btn-sm w-100 fw-semibold disabled" style="background-color: #e2e8f0; color: #64748b; border-radius: 8px;">{{ $product->availabilityLabel() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide"><p class="text-muted">Belum ada produk.</p></div>
                    @endif
                </div>
            </div>

            @if(isset($products) && $products->count() > 0)
            <div class="text-center mt-4">
                <a href="{{ route('products') }}" class="fw-bold text-decoration-none" style="color: #2A6CA2;">
                    Lihat semua produk <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            @endif
        </div>
    </section>

    <section id="news" class="bg-pattern-dark">
        <div class="container">
            <div class="section-header">
                <div>
                    <h2 class="section-title text-white">News</h2>
                    <div class="section-title-underline" style="background-color: #ffffff;"></div>
                </div>
                @if(isset($news) && $news->count() > 1)
                <div class="slider-nav">
                    <button class="news-prev"><i class="bi bi-arrow-left"></i></button>
                    <button class="news-next"><i class="bi bi-arrow-right"></i></button>
                </div>
                @endif
            </div>

            <div class="swiper news-slider">
                <div class="swiper-wrapper">
                    @if(isset($news) && $news->count() > 0)
                        @foreach($news as $item)
                        <div class="swiper-slide h-auto">
                            <a href="{{ route('news.detail', $item->id) }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm hover-lift">
                                    <img src="{{ asset('storage/' . $item->image) }}" class="card-img-top news-img" alt="{{ $item->title }}">
                                    <div class="card-body p-4 d-flex flex-column text-start">
                                        <span class="text-primary fw-bold mb-2" style="font-size: 0.85rem;"><i class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::parse($item->published_date ?? $item->created_at)->translatedFormat('d M Y') }}</span>
                                        <h5 class="fw-bold text-dark mb-3" style="font-size: 1.2rem;">{{ Str::limit($item->title, 50) }}</h5>
                                        <p class="text-muted flex-grow-1" style="font-size: 0.95rem;">{!! Str::limit(strip_tags($item->content), 90) !!}</p>
                                        <span class="mt-auto text-primary fw-bold" style="font-size: 0.95rem;">Baca Selengkapnya <i class="bi bi-arrow-right"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide"><p class="text-white text-center">Belum ada berita.</p></div>
                    @endif
                </div>
            </div>

            @if(isset($news) && $news->count() > 0)
            <div class="text-center mt-4">
                <a href="{{ route('news.index') }}" class="btn btn-outline-light px-5 py-2 rounded-pill fw-bold" style="border-width: 2px;">
                    Berita Lainnya <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            @endif
        </div>
    </section>

    <section id="sponsors" class="py-5 bg-pattern-light border-top">
        <div class="container mt-4 mb-4">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Our Sponsors & Partners</h2>
                    <div class="section-title-underline"></div>
                </div>
                <div class="slider-nav">
                    <button class="sponsor-prev"><i class="bi bi-arrow-left"></i></button>
                    <button class="sponsor-next"><i class="bi bi-arrow-right"></i></button>
                </div>
            </div>
            
            <div class="swiper sponsor-slider">
                <div class="swiper-wrapper">
                    @if(isset($sponsors) && $sponsors->count() > 0)
                        @foreach($sponsors as $sponsor)
                        <div class="swiper-slide h-auto">
                            <div class="card mitra-sponsor-card h-100 hover-lift">
                                <div class="mitra-sponsor-img-container">
                                    <img src="{{ asset('storage/' . $sponsor->logo) }}" class="mitra-sponsor-img" alt="{{ $sponsor->name }}">
                                </div>
                                <div class="card-body text-center p-4 pt-0 d-flex flex-column">
                                    <h5 class="card-title fw-bold text-dark m-0 mb-3" style="font-size: 1.15rem;">{{ $sponsor->name }}</h5>
                                    <p class="card-text text-muted m-0 flex-grow-1" style="line-height: 1.6; font-size: 0.9rem;">
                                        {{ Str::limit($sponsor->description, 150) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="swiper-slide"><p class="text-muted text-center">Belum ada sponsor.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <footer id="footer" class="text-white pt-5 pb-4 mt-auto" style="background-color: #2A6CA2; background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, 0.15) 1px, transparent 0); background-size: 24px 24px;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4 pe-lg-5">
                    <img src="{{ asset('assets/img/Group.png') }}" alt="Shellemerch Logo" style="height: 55px; object-fit: contain; filter: brightness(0) invert(1);" class="mb-4">
                    
                    <p class="text-white-50" style="font-size: 0.95rem; line-height: 1.8; text-align: justify;">
                        {{ $kontak_footer->deskripsi ?? 'Shellemerch hadir sebagai platform terpercaya yang menyediakan berbagai macam produk berkualitas dengan harga yang kompetitif.' }}
                    </p>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4 text-white" style="letter-spacing: 1px;">Tautan Menu</h5>
                    <ul class="list-unstyled" style="line-height: 2;">
                        <li><a href="{{ url('/') }}" class="text-white-50 text-decoration-none hover-text-light">Home</a></li>
                        <li><a href="{{ url('/about') }}" class="text-white-50 text-decoration-none hover-text-light">About Us</a></li>
                        <li><a href="{{ url('/products') }}" class="text-white-50 text-decoration-none hover-text-light">Our Products</a></li>
                        <li><a href="{{ url('/berita') }}" class="text-white-50 text-decoration-none hover-text-light">News</a></li>
                    </ul>
                </div>

                <div class="col-lg-5 col-md-12 mb-4">
                    <h5 class="text-uppercase fw-bold mb-4 text-white" style="letter-spacing: 1px;">Hubungi Kami</h5>
                    <p class="text-white-50 mb-3 d-flex align-items-start">
                        <i class="bi bi-geo-alt-fill me-3 mt-1 text-white" style="font-size: 1.2rem;"></i> 
                        <span>{{ $kontak_footer->alamat ?? 'Kota Balikpapan, Kalimantan Timur' }}</span>
                    </p>
                    <p class="text-white-50 mb-3 d-flex align-items-center">
                        <i class="bi bi-envelope-fill me-3 text-white" style="font-size: 1.2rem;"></i> 
                        <span>{{ $kontak_footer->email ?? 'hello@shellemerch.com' }}</span>
                    </p>
                    <p class="text-white-50 mb-4 d-flex align-items-center">
                        <i class="bi bi-telephone-fill me-3 text-white" style="font-size: 1.2rem;"></i> 
                        <span>{{ $kontak_footer->telepon ?? '+62 812 3456 7890' }}</span>
                    </p>
                    
                    <div class="d-flex gap-2 mt-4">
                        @if(isset($kontak_footer) && $kontak_footer->facebook)
                            <a href="{{ $kontak_footer->facebook }}" target="_blank" class="btn btn-outline-light rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="bi bi-facebook"></i></a>
                        @endif
                        @if(isset($kontak_footer) && $kontak_footer->instagram)
                            <a href="{{ $kontak_footer->instagram }}" target="_blank" class="btn btn-outline-light rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="bi bi-instagram"></i></a>
                        @endif
                        @if(isset($kontak_footer) && $kontak_footer->youtube)
                            <a href="{{ $kontak_footer->youtube }}" target="_blank" class="btn btn-outline-light rounded-circle d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;"><i class="bi bi-youtube"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            
            <hr class="border-light mt-2 mb-4" style="opacity: 0.2;">
            
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-white-50" style="font-size: 0.9rem;">&copy; {{ date('Y') }} Shellemerch. All Rights Reserved.</p>
                </div>
            </div>
        </div>
        <style>
            /* Efek hover diubah menjadi putih menyala agar terlihat jelas */
            .hover-text-light:hover { color: #ffffff !important; transition: 0.3s; text-decoration: underline !important; }
        </style>
    </footer>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper('.produk-slider', {
                slidesPerView: 1, spaceBetween: 30, loop: true, 
                autoplay: { delay: 3500, disableOnInteraction: false },
                navigation: { nextEl: '.produk-next', prevEl: '.produk-prev' },
                breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 }, 1200: { slidesPerView: 4 } }
            });

            new Swiper('.sponsor-slider', {
                slidesPerView: 1, spaceBetween: 30, loop: true, 
                autoplay: { delay: 3500, disableOnInteraction: false },
                navigation: { nextEl: '.sponsor-next', prevEl: '.sponsor-prev' },
                breakpoints: { 576: { slidesPerView: 1 }, 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 }, 1200: { slidesPerView: 4 } }
            });

            new Swiper('.news-slider', {
                slidesPerView: 1, spaceBetween: 30, loop: {{ isset($news) && $news->count() > 1 ? 'true' : 'false' }},
                autoplay: { delay: 4000, disableOnInteraction: false },
                navigation: { nextEl: '.news-next', prevEl: '.news-prev' },
                breakpoints: { 768: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
            });
        });
    </script>
</body>
</html>