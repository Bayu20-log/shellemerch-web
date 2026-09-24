<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Products - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        
        @keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeInUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .animate-nav { animation: slideDown 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        .animate-banner { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.2s forwards; }
        .animate-sidebar { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.4s forwards; }
        .animate-grid { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.6s forwards; }

        .bg-pattern-light { background-color: #f8fafc; background-image: radial-gradient(circle at 1px 1px, rgba(42, 108, 162, 0.12) 1px, transparent 0); background-size: 24px 24px; }
        
        /* ================================================== */
        /* NAVBAR & ANIMASI KURSOR (SAMA PERSIS DENGAN LAINNYA)*/
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
        
        .page-banner { width: 100%; height: 280px; position: relative; background-color: #2A6CA2; display: flex; align-items: center; overflow: hidden; margin-bottom: 50px; }
        .page-banner img { position: absolute; width: 100%; height: 100%; object-fit: cover; opacity: 0.3; }
        .banner-text { position: relative; z-index: 1; color: white; padding-left: 5%; }
        
        .sidebar-title { color: #2A6CA2; font-weight: 700; font-size: 1.3rem; margin-bottom: 0.5rem; }
        .sidebar-list { list-style: none; padding: 0; }
        .sidebar-list li { margin-bottom: 10px; }
        .sidebar-list a { color: #4a5568; text-decoration: none; font-size: 0.95rem; font-weight: 500; transition: 0.3s; }
        .sidebar-list a:hover, .sidebar-list a.active { color: #2A6CA2; }
        
        .product-grid-card { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s; background: #fff; }
        .product-grid-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .product-grid-img { width: 100%; height: 200px; object-fit: cover; }
        
        .btn-cart { display: block; width: 100%; text-align: center; border: 1px solid #cbd5e1; color: #2A6CA2; padding: 8px 0; border-radius: 5px; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-top: 15px; transition: 0.3s; }
        .btn-cart:hover { background-color: #f1f5f9; }
        
        .wa-float { position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; z-index: 100; transition: 0.3s; }
        .wa-float:hover { transform: scale(1.1); }
        .wa-float img { width: 100%; height: 100%; }
        
        input::placeholder { color: rgba(255, 255, 255, 0.7) !important; }
        input:focus { background-color: #2A6CA2 !important; color: white !important; box-shadow: none !important; border: 1px solid #1e4e78 !important; }
        
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
</head>
<body class="bg-pattern-light">

    <nav class="navbar navbar-expand-lg sticky-top animate-nav">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
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
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 animate-section">

    <div class="page-banner shadow-sm animate-banner">
        <img src="{{ asset('assets/img/grup.png') }}" alt="Banner">
        <div class="container banner-text">
            <h1 class="fw-bold mb-0" style="font-size: 3.5rem; letter-spacing: 2px;">CREATE YOUR</h1>
            <h2 class="fst-italic" style="font-size: 2rem; margin-left: 20px;">Ocean Memories</h2>
        </div>
    </div>

    <div class="container mb-5 pb-5">
        <div class="row">
            <div class="col-lg-3 col-md-4 mb-4 animate-sidebar">
                <h4 class="sidebar-title">Browse by</h4>
                <hr style="border-color: #cbd5e1; opacity: 1;">
                <ul class="sidebar-list">
                    <li><a href="{{ url('/products') }}" class="active">All products</a></li>
                    <li><a href="#">EcoCanvas</a></li>
                    <li><a href="#">Coming soon</a></li>
                </ul>
            </div>
            
            <div class="col-lg-9 col-md-8 animate-grid">
                
                <form action="{{ url('/products') }}" method="GET" id="filterForm">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold mb-2 mb-md-0" style="color: #2A6CA2;">All products</h3>
                        
                        <div class="input-group shadow-sm" style="width: 250px;">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 text-white" placeholder="Search products..." style="background-color: #2A6CA2;">
                            <button class="btn btn-primary" type="submit" style="background-color: #1e4e78; border: none;"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                        <span class="text-muted small fw-medium">Menampilkan {{ $products->count() }} produk</span>
                        
                        <div class="d-flex align-items-center">
                            <span class="text-muted small me-2">Sort by:</span>
                            <select name="sort" class="form-select form-select-sm border-0 text-primary fw-bold bg-transparent" style="width: auto;" onchange="document.getElementById('filterForm').submit();">
                                <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga (Terendah - Tertinggi)</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga (Tertinggi - Terendah)</option>
                            </select>
                        </div>
                    </div>
                </form>

                <div class="row">
                    @forelse($products as $product)
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <div class="product-grid-card p-3 h-100 d-flex flex-column">
                            <img src="{{ asset('storage/' . $product->image) }}" class="product-grid-img mb-3 rounded" alt="{{ $product->name }}">
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1">{{ $product->name }}</h6>
                                <p class="text-secondary small mb-2" style="line-height: 1.4;">{{ Str::limit($product->description, 60) }}</p>
                            </div>
                            <span class="fw-bold d-block mt-2" style="color: #2A6CA2; font-size: 0.95rem;">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                            <a href="https://wa.me/6282320493612?text=Halo%20admin%20Shell%20e%20Merch{{ rawurlencode($product->name) }}" target="_blank" class="btn-cart shadow-sm">Beli via WhatsApp</a>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-5">
                        <h5 class="text-muted"><i class="bi bi-box-seam mb-2 d-block" style="font-size: 2rem;"></i>Produk tidak ditemukan.</h5>
                        <a href="{{ url('/products') }}" class="btn btn-outline-primary btn-sm mt-2">Reset Pencarian</a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <a href="https://wa.me/6281234567890" target="_blank" class="wa-float">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp">
    </a>

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