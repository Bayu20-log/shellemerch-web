<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <a class="navbar-brand ps-3" href="{{ url('/admin/dashboard') }}">Admin Shellemerch</a>
    
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>
    
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><hr class="dropdown-divider" /></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    
                    <div class="sb-sidenav-menu-heading">Utama</div>
                    <a class="nav-link" href="{{ url('/admin/dashboard') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                        Dashboard
                    </a>
                    
                    <div class="sb-sidenav-menu-heading">Manajemen Konten</div>
                    
                    <a class="nav-link" href="{{ route('admin.heroes.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-images"></i></div>
                        Hero Slide
                    </a>
                    
                    <a class="nav-link" href="{{ route('admin.products.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-box"></i></div>
                        Products
                    </a>
                    
                    <a class="nav-link" href="{{ route('admin.news.index') }}">
                       <div class="sb-nav-link-icon"><i class="fas fa-newspaper"></i></div>
                        News
                    </a>
                    
                    <a class="nav-link" href="{{ route('admin.sponsors.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-handshake"></i></div>
                        Sponsors
                    </a>

                    <a class="nav-link" href="{{ route('admin.kontak.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-address-book"></i></div>
                        Kontak & Footer
                    </a>
                    
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                Admin Shellemerch
            </div>
        </nav>
    </div>