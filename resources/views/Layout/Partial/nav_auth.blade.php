{{-- Menu akun di navbar publik: khusus pembeli. Admin masuk lewat /admin, tidak ditampilkan di sini. --}}
@guest
    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
        <a class="btn btn-sm px-4" href="{{ route('login') }}"
           style="--bs-btn-color:#fff; --bs-btn-bg:#2A6CA2; --bs-btn-border-color:#2A6CA2; --bs-btn-hover-color:#fff; --bs-btn-hover-bg:#1e4e78; --bs-btn-hover-border-color:#1e4e78; --bs-btn-active-color:#fff; --bs-btn-active-bg:#1e4e78; --bs-btn-active-border-color:#1e4e78; border-radius:50px; font-weight:600; font-size:14px; padding-top:7px; padding-bottom:7px;">Masuk</a>
    </li>
@endguest
@auth
    @unless(auth()->user()->isAdmin())
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('customer.orders.*') ? 'active' : '' }}" href="{{ route('customer.orders.index') }}">Pesanan Saya</a>
        </li>
    @endunless
@endauth
