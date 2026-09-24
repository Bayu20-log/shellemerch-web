@auth
    <li class="nav-item">
        <a class="nav-link" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('customer.orders.index') }}">{{ auth()->user()->isAdmin() ? 'Dashboard' : 'Pesanan Saya' }}</a>
    </li>
@else
    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Masuk</a></li>
@endauth
