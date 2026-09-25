<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Detail Berita - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; color: #333; background-color: #f8fafc; }
        .bg-pattern-light { background-color: #f8fafc; background-image: radial-gradient(circle at 1px 1px, rgba(42, 108, 162, 0.12) 1px, transparent 0); background-size: 24px 24px; }
        
        /* ================================================== */
        /* NAVBAR & ANIMASI KURSOR (SAMA PERSIS DENGAN WELCOME)*/
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

        .article-section { padding: 60px 0 90px; }
        .article-container { background: #ffffff; border-radius: 20px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; }
        
        .breadcrumb-custom { font-size: 0.9rem; margin-bottom: 30px; font-weight: 500; }
        .breadcrumb-custom a { color: #2A6CA2; text-decoration: none; }
        .breadcrumb-custom a:hover { text-decoration: underline; }
        .breadcrumb-custom span { color: #94a3b8; margin: 0 8px; }
        .breadcrumb-active { color: #64748b; }

        .article-title { font-size: 2.5rem; font-weight: 800; color: #2d3748; line-height: 1.3; margin-bottom: 20px; }
        .article-meta { font-size: 0.95rem; color: #94a3b8; margin-bottom: 40px; display: flex; align-items: center; gap: 15px; font-weight: 500; }
        .article-meta i { color: #2A6CA2; }
        
        .article-img { width: 100%; max-height: 500px; object-fit: cover; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        
        .article-body { font-size: 1.1rem; line-height: 1.9; color: #4a5568; text-align: justify; }
        .article-body p { margin-bottom: 25px; }

        .btn-back { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; color: #ffffff; background-color: #2A6CA2; padding: 12px 25px; border-radius: 50px; text-decoration: none; transition: background-color 0.3s ease; margin-top: 30px; }
        .btn-back:hover { background-color: #1e4e76; color: #ffffff; }
        
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
<body class="bg-pattern-light d-flex flex-column min-vh-100">

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

    <main class="flex-grow-1">
        
        <section class="article-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        
                        <div class="article-container">
                            
                            @if(isset($newsItem))
                                <div class="breadcrumb-custom">
                                    <a href="{{ route('beranda') }}">Home</a> <span>/</span>
                                    <a href="{{ route('news.index') }}">News</a> <span>/</span>
                                    <span class="breadcrumb-active">{{ Str::limit($newsItem->title, 30) }}</span>
                                </div>

                                <h1 class="article-title">{{ $newsItem->title }}</h1>
                                
                                <div class="article-meta">
                                    <span><i class="bi bi-calendar-event"></i> {{ \Carbon\Carbon::parse($newsItem->published_date ?? $newsItem->created_at)->translatedFormat('d F Y') }}</span>
                                    <span><i class="bi bi-person-circle"></i> Admin Shellemerch</span>
                                </div>

                                @if ($newsItem->image)
                                    <img src="{{ asset('storage/' . $newsItem->image) }}" class="img-fluid" style="width: 100% !important; height: auto !important; object-fit: contain !important; max-height: none !important; border-radius: 10px;" alt="{{ $newsItem->title }}" class="article-img">
                                @endif
                                
                                <div class="article-body">
                                    {!! nl2br(e($newsItem->content)) !!}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <h3 class="text-muted">Data berita tidak ditemukan.</h3>
                                </div>
                            @endif

                            <div class="text-center mt-5"> 
                                <a href="{{ route('news.index') }}" class="btn-back">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Berita
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

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
</body>
</html>