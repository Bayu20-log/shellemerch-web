{{-- Navbar HP: berubah jadi panel geser dari kiri (seperti RAKSAKTI), bukan dropdown penuh di tengah. --}}
<style>
    @media (max-width: 991.98px) {
        .navbar-collapse {
            display: block !important;
            position: fixed;
            top: 0;
            left: 0;
            width: 78vw;
            max-width: 300px;
            height: 100vh;
            background-color: #1b2430;
            padding: 90px 24px 24px;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            visibility: hidden;
            z-index: 1045;
        }
        .navbar-collapse.show { transform: translateX(0); visibility: visible; }
        .navbar-collapse .navbar-nav { align-items: flex-start !important; width: 100%; }
        .navbar-collapse .nav-item { width: 100%; }
        .navbar-collapse .nav-link {
            color: rgba(255,255,255,0.85) !important;
            text-transform: none !important;
            letter-spacing: normal !important;
            font-size: 1rem !important;
            padding: 12px 0 !important;
            margin: 0 !important;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .navbar-collapse .nav-link:hover, .navbar-collapse .nav-link.active { color: #fff !important; }
        .navbar-collapse .nav-link::after { display: none !important; }
        .navbar-collapse form { margin-top: 12px; }
        .nav-drawer-backdrop {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55);
            z-index: 1040; opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .nav-drawer-backdrop.show { opacity: 1; pointer-events: auto; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggler = document.querySelector('.navbar-toggler');
        var menu = document.querySelector('.navbar-collapse');
        if (!toggler || !menu || typeof bootstrap === 'undefined') return;

        var backdrop = document.createElement('div');
        backdrop.className = 'nav-drawer-backdrop';
        document.body.appendChild(backdrop);

        var collapse = bootstrap.Collapse.getOrCreateInstance(menu, { toggle: false });
        menu.addEventListener('shown.bs.collapse', function () { backdrop.classList.add('show'); });
        menu.addEventListener('hidden.bs.collapse', function () { backdrop.classList.remove('show'); });
        backdrop.addEventListener('click', function () { collapse.hide(); });
        menu.querySelectorAll('.nav-link').forEach(function (link) {
            link.addEventListener('click', function () { collapse.hide(); });
        });
    });
</script>
