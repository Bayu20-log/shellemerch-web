<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pesanan Saya') - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; color: #1f2937; }
        .navbar { padding: 15px 0; background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); }
        .nav-link { font-weight: 500; color: #4a5568 !important; font-size: 14px; margin: 0 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .nav-link:hover, .nav-link.active { color: #2A6CA2 !important; }
        .btn-brand { background-color: #2A6CA2; border-color: #2A6CA2; color: #fff; border-radius: 50px; padding: 9px 22px; font-weight: 600; transition: 0.3s; }
        .btn-brand:hover, .btn-brand:focus { background-color: #1e4e78; border-color: #1e4e78; color: #fff; }
        .btn-outline-brand { border: 1px solid #2A6CA2; color: #2A6CA2; border-radius: 50px; padding: 9px 22px; font-weight: 600; background: #fff; transition: 0.3s; }
        .btn-outline-brand:hover { background-color: #eef4fa; color: #1e4e78; }
        .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; }
        .form-control, .form-select { border-radius: 12px; padding: 10px 16px; }
        .form-control:focus, .form-select:focus { border-color: #2A6CA2; box-shadow: 0 0 0 0.2rem rgba(42, 108, 162, 0.2); }
        .page-title { color: #2A6CA2; font-weight: 700; }
        .thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; background: #f1f5f9; }
        .size-option { border: 1px solid #cbd5e1; border-radius: 12px; padding: 12px 16px; cursor: pointer; transition: 0.2s; display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .size-option:hover { border-color: #2A6CA2; }
        .btn-check:checked + .size-option { border-color: #2A6CA2; background: #eef4fa; box-shadow: 0 0 0 1px #2A6CA2; }
        .btn-check:focus-visible + .size-option { outline: 3px solid rgba(42, 108, 162, 0.4); outline-offset: 2px; }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('assets/img/Group.png') }}" alt="Shellemerch" style="height: 45px; object-fit: contain;">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#customerNav" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="customerNav">
                <ul class="navbar-nav align-items-lg-center">
                    <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('products') }}">Products</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">Pesanan Saya</a></li>
                    <li class="nav-item ms-lg-3 d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ auth()->user()->name }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-brand">Keluar</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-5 flex-grow-1" style="max-width: 980px;">
        @if(session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->has('order'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('order') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="py-4 text-center small text-muted">Copyright &copy; Shellemerch 2026</footer>

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
