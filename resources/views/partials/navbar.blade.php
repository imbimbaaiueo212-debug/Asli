<nav class="sb-topnav navbar navbar-expand navbar-light bg-light fixed-top">

    <!-- Hamburger Button - Muncul di semua layar (Mobile + Desktop) -->
    <button class="btn btn-link text-dark p-3 me-2" 
            id="sidebarToggle" 
            type="button" 
            aria-label="Toggle Sidebar">
        <i class="fas fa-bars fs-4"></i>
    </button>

    <!-- Logo -->
    <a class="navbar-brand mx-auto mx-lg-0 px-2 px-lg-3" href="{{ route('unit.index') }}">
        <img src="{{ asset('template/img/finaly.png') }}"
             alt="Infinite Management"
             class="d-block"
             height="40">
    </a>

    <!-- Spacer hanya untuk mobile -->
    <div class="d-lg-none flex-grow-1"></div>

    <!-- User Dropdown di kanan -->
    <ul class="navbar-nav ms-auto me-2 me-lg-4">
        @auth
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center py-1 text-black"
                   id="navbarDropdown"
                   href="#"
                   role="button"
                   data-bs-toggle="dropdown"
                   aria-expanded="false">
                    @if (Auth::user()->photo)
                        <img src="{{ asset('public/storage/' . Auth::user()->photo) }}"
                             class="rounded-circle border border-2 border-white me-2"
                             width="34" height="34" alt="Profile">
                    @else
                        <img src="{{ asset('public/template/img/user.png') }}"
                             class="rounded-circle border border-2 border-white me-2"
                             width="34" height="34" alt="Profile">
                    @endif
                    <span class="d-none d-lg-block fw-medium">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow mt-2">
                    <li><a class="dropdown-item" href="{{ route('users.show', Auth::user()->id) }}"><i class="fas fa-id-card me-2"></i> Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
        @endauth
    </ul>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidebar   = document.querySelector('.sb-sidenav');
    const toggleBtn = document.getElementById('sidebarToggle');

    if (!sidebar || !toggleBtn) return;

    let overlay = document.getElementById('sidebarOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 1040;
            opacity: 0; visibility: hidden; transition: all 0.3s ease;
        `;
        document.body.appendChild(overlay);
    }

    const openSidebar = () => {
        sidebar.classList.add('open');
        overlay.style.opacity = '1';
        overlay.style.visibility = 'visible';
        document.body.classList.add('sidebar-open');
    };

    const closeSidebar = () => {
        sidebar.classList.remove('open');
        overlay.style.opacity = '0';
        overlay.style.visibility = 'hidden';
        document.body.classList.remove('sidebar-open');
    };

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    // Tutup sidebar saat klik link (hanya mobile)
    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });

    // ESC key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });
});
</script>