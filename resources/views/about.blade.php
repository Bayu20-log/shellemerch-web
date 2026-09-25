<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>About Us - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; overflow-x: hidden; color: #333; background-color: #ffffff; }
        
        @keyframes slideDown { from { transform: translateY(-100%); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        @keyframes fadeInUp { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .animate-nav { animation: slideDown 0.8s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
        .animate-section { opacity: 0; animation: fadeInUp 1s cubic-bezier(0.25, 1, 0.5, 1) 0.3s forwards; }

        .bg-pattern-light { background-color: #f8fafc; background-image: radial-gradient(circle at 1px 1px, rgba(42, 108, 162, 0.12) 1px, transparent 0); background-size: 24px 24px; }

        .navbar { padding: 15px 0; background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); z-index: 1000; }
        
        /* --- ANIMASI GARIS NAVBAR MENGIKUTI KURSOR --- */
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

        .section-apa-itu { padding: 80px 0; }
        .apa-itu-title { font-size: 3.5rem; font-weight: 800; color: #2A6CA2; line-height: 1.1; text-transform: uppercase; }
        .apa-itu-desc { font-size: 1.05rem; line-height: 1.8; color: #4a5568; text-align: justify; }
        .apa-itu-img { width: 100%; height: 450px; object-fit: cover; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .apa-itu-caption { font-size: 0.85rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }

        .section-kisah { padding: 90px 0; background-color: #ffffff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .kisah-title-container { display: flex; height: 100%; align-items: stretch; }
        .kisah-title { writing-mode: vertical-rl; transform: rotate(180deg); font-size: 3.5rem; font-weight: 800; color: #2A6CA2; text-transform: uppercase; margin: 0; text-align: center; letter-spacing: 3px; }
        .kisah-img-wrapper { flex-grow: 1; padding-left: 20px; }
        .kisah-img { width: 100%; height: 100%; min-height: 450px; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .kisah-desc { font-size: 1.05rem; line-height: 1.8; color: #4a5568; text-align: justify; margin-bottom: 25px; }
        .kisah-chart { width: 100%; max-height: 250px; object-fit: contain; border-radius: 12px; margin-bottom: 25px; background-color: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; }

        .section-visi-misi { padding: 90px 0; background-color: #f8fafc; }
        .vm-title { font-size: 3.5rem; font-weight: 800; color: #2A6CA2; text-transform: uppercase; line-height: 1.1; position: sticky; top: 120px; }
        .vm-heading { font-size: 2rem; font-weight: 700; color: #2A6CA2; margin-bottom: 15px; text-transform: uppercase; border-bottom: 3px solid #cbd5e1; padding-bottom: 10px; display: inline-block; }
        .vm-desc { font-size: 1.05rem; line-height: 1.8; color: #4a5568; text-align: justify; margin-bottom: 40px; }
        .vm-list { padding-left: 1.2rem; font-size: 1.05rem; line-height: 1.8; color: #4a5568; text-align: justify; }
        .vm-list li { margin-bottom: 15px; }

        .section-values { padding: 90px 0; background-color: #ffffff; }
        .values-header { display: flex; align-items: center; margin-bottom: 60px; }
        .values-title { font-size: 2.5rem; font-weight: 800; color: #2A6CA2; margin: 0; text-transform: uppercase; white-space: nowrap; }
        .values-line { flex-grow: 1; height: 3px; background-color: #cbd5e1; margin-left: 30px; border-radius: 2px; }
        .value-card { display: flex; flex-direction: column; height: 100%; }
        .value-heading { font-size: 1.4rem; font-weight: 700; color: #2A6CA2; margin-bottom: 12px; text-transform: uppercase; text-align: center; }
        .value-desc { font-size: 1rem; color: #4a5568; line-height: 1.7; text-align: center; margin-bottom: 25px; flex-grow: 1; }
        .value-img { width: 100%; height: 220px; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.06); margin-top: auto; }

        .section-proses { padding: 90px 0; background-color: #f8fafc; border-top: 1px solid #f1f5f9; }
        .proses-row { align-items: start; }
        .proses-title-container { display: flex; align-items: stretch; justify-content: flex-end; height: 100%; width: 100%; }
        .proses-title { writing-mode: vertical-rl; transform: rotate(180deg); font-size: 3.5rem; font-weight: 800; color: #2A6CA2; text-transform: uppercase; margin: 0; text-align: center; letter-spacing: 3px; flex-shrink: 0; }
        .proses-img-wrapper { flex-grow: 1; padding-right: 20px; display: flex; justify-content: center; align-items: flex-start; }
        .proses-img { width: 100%; height: auto; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .proses-desc { font-size: 0.95rem; line-height: 1.7; color: #4a5568; text-align: justify; margin-bottom: 20px; }
        ol.proses-list { padding-left: 1.2rem; color: #4a5568; line-height: 1.7; font-size: 0.95rem; text-align: justify; margin-bottom: 0; }
        ol.proses-list li { margin-bottom: 12px; }

        .section-dampak { padding: 90px 0; background-color: #ffffff; }
        .dampak-header { display: flex; align-items: center; margin-bottom: 50px; }
        .dampak-title { font-size: 2.5rem; font-weight: 800; color: #2A6CA2; margin: 0; text-transform: uppercase; white-space: nowrap; }
        .dampak-line { flex-grow: 1; height: 3px; background-color: #cbd5e1; margin-left: 30px; border-radius: 2px; }
        .dampak-img { width: 100%; height: auto; max-height: 320px; object-fit: cover; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .dampak-desc { font-size: 1.05rem; line-height: 1.8; color: #4a5568; text-align: justify; margin-bottom: 20px; }

        .section-team { padding: 90px 0; background-color: #f8fafc; border-top: 1px solid #f1f5f9; overflow: hidden; }
        .team-header-container { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .team-line { flex: 1; height: 3px; background-color: #cbd5e1; border-radius: 2px; }
        .team-title { font-size: 2.5rem; font-weight: 800; color: #2A6CA2; margin: 0 30px; text-transform: uppercase; white-space: nowrap; }
        .team-desc { text-align: justify; color: #4a5568; margin-bottom: 50px; font-size: 1.05rem; line-height: 1.8; }
        .team-card { display: flex; flex-direction: column; background: transparent; border-radius: 0; overflow: hidden; box-shadow: none; text-align: center; padding-bottom: 0; transition: none; border: none; height: 100%; }
        .team-img-wrapper { width: 100%; overflow: visible; margin-bottom: 10px; background-color: transparent; }
        
        /* ================================================== */
        /* FIX UNTUK UKURAN GAMBAR TIM AGAR SERAGAM           */
        /* ================================================== */
        .team-img { 
            width: 100%; 
            height: 260px; /* Memaksa tinggi gambar seragam */
            object-fit: contain; /* Menjaga proporsi gambar tidak gepeng */
            object-position: center bottom; /* Rata bawah agar pundak sejajar */
            transition: none; 
        }
        /* ================================================== */
        
        .team-name { font-size: 1.3rem; font-weight: 700; color: #2d3748; margin-bottom: 5px; padding: 0; }
        .team-role { font-size: 0.9rem; color: #2A6CA2; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0; padding: 0; }
        .team-slider { padding-bottom: 20px !important; padding-top: 10px !important; }

        .section-gallery { padding: 90px 0; background-color: #ffffff; border-top: 1px solid #f1f5f9; }
        .gallery-header-container { display: flex; align-items: center; justify-content: center; margin-bottom: 50px; }
        .gallery-title { font-size: 2.5rem; font-weight: 800; color: #2A6CA2; text-transform: uppercase; margin: 0; }
        .gallery-link { display: block; width: 100%; height: 100%; cursor: zoom-in; }
        .gallery-img-vertical { width: 100%; height: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); transition: transform 0.3s ease; }
        .gallery-img-horizontal { width: 100%; height: 100%; aspect-ratio: 16/9; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); transition: transform 0.3s ease; }
        .gallery-link:hover .gallery-img-vertical, 
        .gallery-link:hover .gallery-img-horizontal { transform: scale(1.02); box-shadow: 0 12px 35px rgba(0,0,0,0.12); }
        .gallery-vertical-col { flex: 1 1 0; min-width: 0; }
        
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
                    <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="{{ route('about') }}">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('about') }}#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('news.index') }}">News</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}#sponsors">Sponsors & Partners</a></li>
                    <li class="nav-item"><a class="nav-link" href="#footer">Contact Us</a></li>
                    @include('Layout.Partial.nav_auth')
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 animate-section">
        
        <section class="section-apa-itu">
            <div class="container">
                <div class="row mb-5 align-items-center">
                    <div class="col-lg-5 mb-4 mb-lg-0">
                        <h1 class="apa-itu-title">APA ITU<br>SHELLEMERCH?</h1>
                    </div>
                    <div class="col-lg-7 pl-lg-4">
                        <p class="apa-itu-desc mb-0">Shellemerch adalah usaha kreatif berbasis industri seni yang berfokus pada pengolahan sampah menjadi produk merchandise bernilai estetika dan ekonomi. Berbasis di Kota Balikpapan, Shellemerch memanfaatkan material seperti sampah spanduk, botol plastik, logam, kertas, hingga kayu sisa untuk diolah kembali menjadi produk kreatif berkualitas tinggi.</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12"><img src="{{ asset('assets/img/about1.jpg') }}" alt="Shellemerch Overview" class="apa-itu-img"></div>
                </div>
                <div class="row">
                    <div class="col-12 d-flex justify-content-between">
                        <span class="apa-itu-caption">Est. 2026</span>
                        <span class="apa-itu-caption">Karya Anak Bangsa</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-kisah">
            <div class="container">
                <div class="row align-items-stretch">
                    <div class="col-lg-5 mb-5 mb-lg-0">
                        <div class="kisah-title-container">
                            <h2 class="kisah-title">KISAH KAMI</h2>
                            <div class="kisah-img-wrapper"><img src="{{ asset('assets/img/about2.jpg') }}" alt="Kisah Shellemerch" class="kisah-img"></div>
                        </div>
                    </div>
                    <div class="col-lg-7 ps-lg-5">
                        <p class="kisah-desc">Perjalanan Shellemerch berawal dari kepedulian mendalam terhadap jumlah sampah yang terus meningkat, khususnya limbah anorganik di lingkungan sekitar. Kami melihat potensi besar di balik barang-barang yang dianggap tidak berguna tersebut untuk diubah menjadi produk yang bernilai.</p>
                        <img src="{{ asset('assets/img/about3.jpg') }}" alt="Chart Pertumbuhan" class="kisah-chart shadow-sm">
                        <p class="kisah-desc">Melalui riset dan eksperimen yang panjang, kami menemukan metode efektif untuk mengolah limbah tersebut menjadi material dasar yang kuat dan fleksibel. Proses pengolahan ini tidak hanya mengurangi volume sampah secara signifikan, tetapi juga meminimalisir jejak karbon dari produksi material baru.</p>
                        <p class="kisah-desc mb-0">Kini, Shellemerch telah berkembang menjadi lebih dari sekadar proyek daur ulang. Kami adalah gerakan kreatif yang memberdayakan pengrajin lokal, mengedukasi masyarakat tentang pentingnya ekonomi sirkular, dan membuktikan bahwa produk ramah lingkungan bisa tampil sangat elegan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-visi-misi">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4 mb-5 mb-lg-0"><h2 class="vm-title">VISI &<br>MISI</h2></div>
                    <div class="col-lg-8 ps-lg-5">
                        <div class="mb-5">
                            <h3 class="vm-heading">VISI</h3>
                            <p class="vm-desc">Menjadi penyedia merchandise terdepan yang menginspirasi dan menghadirkan produk berkualitas tinggi, serta memberikan nilai tambah bagi pelanggan, komunitas, dan lingkungan sekitar melalui inovasi yang berkelanjutan.</p>
                        </div>
                        <div>
                            <h3 class="vm-heading">MISI</h3>
                            <ol class="vm-list">
                                <li>Menghadirkan produk merchandise dengan kualitas material terbaik dan desain yang selalu mengikuti perkembangan tren terkini.</li>
                                <li>Memberikan pelayanan pelanggan yang responsif, ramah, dan profesional untuk menciptakan pengalaman belanja yang berkesan.</li>
                                <li>Membangun kemitraan yang kuat dengan berbagai pihak untuk memperluas jangkauan dan memberikan dampak positif bagi komunitas lokal.</li>
                                <li>Mengedepankan praktik bisnis yang ramah lingkungan dalam setiap proses produksi dan pengemasan produk.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-values">
            <div class="container">
                <div class="values-header">
                    <h2 class="values-title">OUR VALUES</h2>
                    <div class="values-line"></div>
                </div>
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-5 pb-3 d-flex">
                        <div class="value-card w-100">
                            <h4 class="value-heading">Sustainability</h4>
                            <p class="value-desc">Kami berkomitmen pada pelestarian lingkungan dengan menerapkan prinsip daur ulang dalam setiap proses produksi.</p>
                            <img src="{{ asset('assets/img/about4.jpg') }}" alt="Sustainability" class="value-img">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-5 pb-3 d-flex">
                        <div class="value-card w-100">
                            <h4 class="value-heading">Creativity</h4>
                            <p class="value-desc">Inovasi tanpa henti untuk mengubah barang yang dianggap tidak bernilai menjadi karya merchandise yang estetik.</p>
                            <img src="{{ asset('assets/img/about5.jpg') }}" alt="Creativity" class="value-img">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-5 pb-3 d-flex">
                        <div class="value-card w-100">
                            <h4 class="value-heading">Empowerment</h4>
                            <p class="value-desc">Memberdayakan pengrajin lokal untuk tumbuh bersama dalam sebuah ekosistem ekonomi kreatif.</p>
                            <img src="{{ asset('assets/img/about6.jpg') }}" alt="Empowerment" class="value-img">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-5 pb-3 d-flex">
                        <div class="value-card w-100">
                            <h4 class="value-heading">Quality</h4>
                            <p class="value-desc">Menjaga standar kualitas terbaik dalam setiap detail produk untuk memberikan kepuasan maksimal bagi pelanggan.</p>
                            <img src="{{ asset('assets/img/about7.jpg') }}" alt="Quality" class="value-img">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-proses">
            <div class="container">
                <div class="row proses-row flex-column-reverse flex-lg-row">
                    <div class="col-lg-5 pe-lg-4 d-flex flex-column justify-content-center mt-5 mt-lg-0">
                        <p class="proses-desc">Proses pembuatan produk Shellemerch mengutamakan ketelitian dan pelestarian lingkungan. Komitmen kami menghadirkan seni dari barang sisa dilakukan melalui tahapan utama:</p>
                        <ol class="proses-list">
                            <li><strong>Pengumpulan Bahan Baku:</strong> Mengambil limbah plastik, kayu sisa, dan spanduk bekas dari mitra lokal dan bank sampah Balikpapan.</li>
                            <li><strong>Penyortiran & Pembersihan:</strong> Limbah disortir dan dibersihkan menyeluruh untuk menjamin keamanan produk.</li>
                            <li><strong>Desain & Pengolahan Fisik:</strong> Tim pengrajin menyulap material menjadi bentuk baru dengan teknik daur ulang inovatif (upcycling).</li>
                            <li><strong>Quality Control:</strong> Pengecekan kualitas ketat memastikan produk estetis, kuat, fungsional, dan siap digunakan.</li>
                        </ol>
                    </div>
                    <div class="col-lg-7 mb-4 mb-lg-0">
                        <div class="proses-title-container">
                            <div class="proses-img-wrapper">
                                <img src="{{ asset('assets/img/about8.png') }}" alt="Proses Shellemerch" class="proses-img">
                            </div>
                            <h2 class="proses-title">PROSES KAMI</h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-dampak">
            <div class="container">
                <div class="dampak-header">
                    <h2 class="dampak-title">OUR IMPACT</h2>
                    <div class="dampak-line"></div>
                </div>
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <img src="{{ asset('assets/img/about9.jpg') }}" alt="Dampak Shellemerch" class="dampak-img">
                    </div>
                    <div class="col-lg-6 ps-lg-5">
                        <p class="dampak-desc">Melalui pengolahan limbah menjadi produk merchandise, Shell e Merch berupaya memberikan kontribusi terhadap pengurangan sampah. Dengan memanfaatkan material seperti plastik, logam, kertas, dan kayu sisa sebagai bahan baku produk, Shell e Merch tidak hanya menciptakan produk kreatif tetapi juga membantu mengurangi potensi limbah yang berakhir di lingkungan.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-team">
            <div class="container">
                <div class="team-header-container">
                    <div class="team-line"></div>
                    <h2 class="team-title">OUR TEAM</h2>
                    <div class="team-line"></div>
                </div>
                <p class="team-desc">Di balik inovasi Shellemerch, terdapat individu-individu penuh dedikasi yang memiliki visi selaras untuk mewujudkan masa depan yang lebih hijau. Mari berkenalan dengan para pendiri yang menjadi penggerak utama kami.</p>
                <div class="swiper team-slider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide h-auto">
                            <div class="team-card">
                                <div class="team-img-wrapper">
                                    <img src="{{ asset('assets/img/about10.png') }}" alt="Dian Fatikha Rizki Aminoto" class="team-img">
                                </div>
                                <h4 class="team-name">Dian Fatikha Rizki Aminoto</h4>
                                <p class="team-role">CEO (Chief Executive Officer)</p>
                            </div>
                        </div>
                        <div class="swiper-slide h-auto">
                            <div class="team-card">
                                <div class="team-img-wrapper">
                                    <img src="{{ asset('assets/img/about11.png') }}" alt="Putri Titis Dewi" class="team-img">
                                </div>
                                <h4 class="team-name">Putri Titis Dewi</h4>
                                <p class="team-role">CFO (Chief Finance Officer)</p>
                            </div>
                        </div>
                        <div class="swiper-slide h-auto">
                            <div class="team-card">
                                <div class="team-img-wrapper">
                                    <img src="{{ asset('assets/img/about12.png') }}" alt="Muhammad Bayu Saputra" class="team-img">
                                </div>
                                <h4 class="team-name">Muhammad Bayu Saputra</h4>
                                <p class="team-role">CTO (Chief Technology Officer) </p>
                            </div>
                        </div>
                        <div class="swiper-slide h-auto">
                            <div class="team-card">
                                <div class="team-img-wrapper">
                                    <img src="{{ asset('assets/img/about13.png') }}" alt="Alice Brown" class="team-img">
                                </div>
                                <h4 class="team-name">Alya Juniar</h4>
                                <p class="team-role">CMO (Chief Marketing Officer)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="gallery" class="section-gallery">
            <div class="container">
                
                <div class="gallery-header-container">
                    <h2 class="gallery-title">GALERI KAMI</h2>
                </div>

                <div class="row g-3 mb-4 flex-nowrap overflow-auto pb-2" style="overflow-y: hidden;">
                    
                    <div class="gallery-vertical-col">
                        <a href="{{ asset('assets/img/about14.jpg') }}" data-fancybox="gallery" data-caption="Galeri Shellemerch 1" class="gallery-link">
                            <img src="{{ asset('assets/img/about14.jpg') }}" alt="Galeri" class="gallery-img-vertical">
                        </a>
                    </div>
                    
                    <div class="gallery-vertical-col">
                        <a href="{{ asset('assets/img/about15.jpg') }}" data-fancybox="gallery" data-caption="Galeri Shellemerch 2" class="gallery-link">
                            <img src="{{ asset('assets/img/about15.jpg') }}" alt="Galeri" class="gallery-img-vertical">
                        </a>
                    </div>
                    
                    <div class="gallery-vertical-col">
                        <a href="{{ asset('assets/img/about16.jpg') }}" data-fancybox="gallery" data-caption="Galeri Shellemerch 3" class="gallery-link">
                            <img src="{{ asset('assets/img/about16.jpg') }}" alt="Galeri" class="gallery-img-vertical">
                        </a>
                    </div>
                    
                    <div class="gallery-vertical-col">
                        <a href="{{ asset('assets/img/about17.jpg') }}" data-fancybox="gallery" data-caption="Galeri Shellemerch 4" class="gallery-link">
                            <img src="{{ asset('assets/img/about17.jpg') }}" alt="Galeri" class="gallery-img-vertical">
                        </a>
                    </div>
                    
                    <div class="gallery-vertical-col">
                        <a href="{{ asset('assets/img/about18.jpg') }}" data-fancybox="gallery" data-caption="Galeri Shellemerch 5" class="gallery-link">
                            <img src="{{ asset('assets/img/about18.jpg') }}" alt="Galeri" class="gallery-img-vertical">
                        </a>
                    </div>
                    
                </div>

                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <a href="{{ asset('assets/img/about19.jpg') }}" data-fancybox="gallery" data-caption="Koleksi Merchandise 1" class="gallery-link">
                            <img src="{{ asset('assets/img/about19.jpg') }}" alt="Galeri" class="gallery-img-horizontal">
                        </a>
                    </div>
                    
                    <div class="col-md-6">
                        <a href="{{ asset('assets/img/about20.jpg') }}" data-fancybox="gallery" data-caption="Koleksi Merchandise 2" class="gallery-link">
                            <img src="{{ asset('assets/img/about20.jpg') }}" alt="Galeri" class="gallery-img-horizontal">
                        </a>
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
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper('.team-slider', {
                slidesPerView: 1,           
                spaceBetween: 30,           
                loop: true,                 
                autoplay: { 
                    delay: 3500,            
                    disableOnInteraction: false 
                },
                breakpoints: {
                    576: { slidesPerView: 2 }, 
                    992: { slidesPerView: 3 }  
                }
            });
            
            Fancybox.bind('[data-fancybox="gallery"]', {
                infinite: true,
                Toolbar: {
                    display: [
                        "zoom",
                        "slideShow",
                        "fullScreen",
                        "download",
                        "close"
                    ],
                }
            });
        });
    </script>
</body>
</html>