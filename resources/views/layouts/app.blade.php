<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>@yield('title', 'Unit')</title>

    <!-- CSRF Token untuk AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    

    <!-- PWA Fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0d6efd">

    <!-- Json -->
    <link rel="manifest" href="/manifest.json">

    <!-- Favicon Utama -->
    <link rel="icon" href="{{ asset('template/img/favicon.ico') }}" type="image/x-icon">

    <!-- Ukuran kecil untuk tab browser -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('template/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('template/img/favicon-16x16.png') }}">

    <!-- Android/Chrome & PWA -->
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('template/img/android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('template/img/android-chrome-512x512.png') }}">

    <!-- iOS / Apple Touch Icon (Home Screen) -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('template/img/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('template/img/apple-touch-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{ asset('template/img/apple-touch-icon-167x167.png') }}">

    <!-- Windows Tiles (opsional) -->
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('template/img/ms-icon-144x144.png') }}">



    <!-- SB Admin CSS -->
    <link id="theme-style" href="/template/css/styles.css" rel="stylesheet" />
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>

    @stack('head')
</head>
<body class="sb-nav-fixed">

    {{-- Navbar --}}
    @include('partials.navbar')

    <div id="layoutSidenav">
        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Content --}}
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 mt-4">
                    @yield('content')
                </div>
            </main>

            {{-- Footer --}}
            @include('partials.footer')
        </div>
    </div>

    <!-- Bootstrap Bundle (sudah termasuk Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SB Admin JS -->
    <script src="/template/js/scripts.js"></script>

    <!-- jQuery (untuk Select2) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Theme Switcher -->
    <script>
        function toggleTheme() {
            let theme = document.getElementById('theme-style');
            if (theme.getAttribute('href').includes('styles.css')) {
                theme.setAttribute('href', "/template/css/dark-theme.css");
                localStorage.setItem('theme', 'dark');
            } else {
                theme.setAttribute('href', "/template/css/styles.css");
                localStorage.setItem('theme', 'light');
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            let savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.getElementById('theme-style').setAttribute('href', "/template/css/dark-theme.css");
            }
        });
    </script>

    <!-- Script Utama: Fix Collapse + Navigate -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select2 init
            $('#selectNIM').select2({
                placeholder: "-- Pilih NIM --",
                allowClear: true,
                width: '100%'
            });

            // Sidebar mobile toggle + overlay
            const sidebar = document.querySelector('.sb-sidenav');
            const toggleBtn = document.getElementById('sidebarToggle');

            let overlay = document.getElementById('sidebarOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'sidebarOverlay';
                overlay.style.position = 'fixed';
                overlay.style.inset = '0';
                overlay.style.background = 'rgba(0,0,0,0.4)';
                overlay.style.zIndex = '1040';
                overlay.style.display = 'none';
                document.body.appendChild(overlay);
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('open');
                    overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
                });
            }

            overlay.addEventListener('click', function () {
                sidebar.classList.remove('open');
                overlay.style.display = 'none';
            });

            // === YANG PALING PENTING: Fix link collapse yang punya href valid ===
            document.querySelectorAll('a[data-bs-toggle="collapse"][href]:not([href="#"])').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href && href !== '#' && href !== 'javascript:;') {
                        // Biarkan Bootstrap handle collapse dulu (animasi jalan)
                        // Lalu redirect setelah animasi selesai
                        setTimeout(function () {
                            window.location.href = href;
                        }, 200); // 200ms cukup smooth untuk animasi collapse
                    }
                });
            });

            // CSRF Token untuk AJAX jQuery
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (token) {
                $.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': token }
                });
            }
        });
    </script>

    @stack('scripts')
</body>
</html>