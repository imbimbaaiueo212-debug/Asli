<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
        <!-- 🔥 LOGO & TITLE HEADER -->
        <div class="sb-sidenav-header p-1 border-bottom text-center">
            <div class="d-flex align-items-center justify-content-center">
                <!-- Logo -->
                <img src="{{ asset('template/img/finaly.png') }}" 
                     alt="Logo" 
                     class="me-2" 
                     style="height: 32px; width: auto;">
                
                <!-- Title -->
                <!--<div>
                    <div class="fw-bold fs-6 mb-0">Infinite</div>
                    <div class="small opacity-90">Management</div>
                </div>-->
            </div>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav">

                <!-- Home -->
                <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                {{-- MENU SIDEBAR - MANAJEMEN USER & UNIT biMBA --}}
                @php
                    $user = auth()->user();

                    // Email khusus yang boleh akses Manajemen User meskipun role biasa
                    $emailKhusus = [
                        'oktaviandaaria@gmail.com',
                        'robiensyah22@gmail.com',
                        // tambah lagi di sini kalau nanti ada owner/pengawas lain
                    ];
                @endphp
                {{-- 2. UNIT biMBA --}}
                {{-- Muncul untuk hampir semua orang: admin, user biasa, guru, cabang, developer, dll --}}
                @if(
                        $user && in_array($user->role, [
                            'admin',
                            'user'
                        ])
                    )
                    <a class="nav-link {{ request()->routeIs('unit.*') ? 'active' : '' }}" href="{{ route('unit.index') }}">
                        <div class="sb-nav-link-icon"><i class="fas fa-school"></i></div>
                        Unit biMBA
                    </a>
                @endif
                
                {{-- Menu Relawan --}}
                @php
                    $isRelawanActive = request()->routeIs('profiles.*') ||
                        request()->routeIs('absensi-relawan.*') ||
                        request()->routeIs('relawan.*') ||
                        request()->routeIs('lembur.*') ||
                        request()->routeIs('rekap.*') ||
                        request()->routeIs('jadwal.*');
                @endphp
                <a class="nav-link collapsed {{ $isRelawanActive ? 'active' : '' }}" href="#" data-bs-toggle="collapse"
                    data-bs-target="#collapseRelawan" aria-expanded="{{ $isRelawanActive ? 'true' : 'false' }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                    Relawan
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse {{ $isRelawanActive ? 'show' : '' }}" id="collapseRelawan"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ request()->routeIs('profiles.index') ? 'active' : '' }}"
                            href="{{ route('profiles.index') }}">
                            <i class="fas fa-id-card me-2"></i> Profile
                        </a>
                        <a class="nav-link {{ request()->routeIs('relawan.index') ? 'active' : '' }}"
                            href="{{ route('relawan.index') }}">
                            <i class="fas fa-calendar-check me-2"></i> Absen Kehadiran
                        </a>
                        <a class="nav-link {{ request()->routeIs('absensi-relawan.index') ? 'active' : '' }}"
                            href="{{ route('absensi-relawan.index') }}">
                            <i class="fas fa-calendar-check me-2"></i> Absen
                        </a>
                        <a class="nav-link {{ request()->routeIs('lembur.index') ? 'active' : '' }}"
                            href="{{ route('lembur.index') }}">
                            <i class="fas fa-clock me-2"></i> Lembur
                        </a>

                        <a class="nav-link {{ request()->routeIs('rekap.index') ? 'active' : '' }}"
                            href="{{ route('rekap.index') }}">
                            <i class="fas fa-clipboard-list me-2"></i> Rekap Jadwal
                        </a>
                        <a class="nav-link {{ request()->routeIs('jadwal.index') ? 'active' : '' }}"
                            href="{{ route('jadwal.index') }}">
                            <i class="fas fa-calendar-alt me-2"></i> Jadwal Detail
                        </a>
                    </nav>
                </div>

                @php
    $isMuridActive = request()->routeIs('murid_trials.*') ||
        request()->routeIs('buku_induk.*') ||
        request()->routeIs('pindah-golongan.*') ||
        request()->routeIs('daftar_murid_deposit.*') ||
        request()->routeIs('kartu-spp.*') ||
        request()->routeIs('mbc-murid.*') ||
        request()->routeIs('sertifikat-beasiswa.*') ||
        request()->routeIs('garansi-bca.*') ||
        request()->routeIs('students.*') ||
        request()->routeIs('registrations.*') ||
        request()->routeIs('statistik.murid') ||
        request()->routeIs('humas.*');  // <-- DITAMBAHKAN: agar parent Data Murid aktif saat di Humas

    // Jika di grup Murid tapi tidak di route spesifik, default ke Statistik Murid
    $isDefaultStatistik = $isMuridActive && !(
        request()->routeIs('murid_trials.*') ||
        request()->routeIs('buku_induk.*') ||
        request()->routeIs('pindah-golongan.*') ||
        request()->routeIs('daftar_murid_deposit.*') ||
        request()->routeIs('kartu-spp.*') ||
        request()->routeIs('mbc-murid.*') ||
        request()->routeIs('sertifikat-beasiswa.*') ||
        request()->routeIs('garansi-bca.*') ||
        request()->routeIs('students.*') ||
        request()->routeIs('registrations.*') ||
        request()->routeIs('humas.*')  // <-- ditambahkan agar tidak conflict
    );
@endphp

<a class="nav-link {{ $isMuridActive ? 'active' : 'collapsed' }}" 
    href="#"
    data-bs-toggle="collapse" 
    data-bs-target="#collapseMurid"
    aria-expanded="{{ $isMuridActive ? 'true' : 'false' }}" 
    aria-controls="collapseMurid">
    <div class="sb-nav-link-icon"><i class="fas fa-user-graduate"></i></div>
    Data Murid
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isMuridActive ? 'show' : '' }}" id="collapseMurid"
    data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">

        

        {{-- Menu Registrasi Murid (sub-dropdown) --}}
        @php
            $isRegistrasiActive = request()->routeIs('students.*') ||
                request()->routeIs('registrations.*') ||
                request()->routeIs('murid_trials.*');
        @endphp
          {{-- Statistik Murid (opsional dikembalikan jika perlu) --}}
        <a class="nav-link {{ request()->routeIs('statistik.murid') || $isDefaultStatistik ? 'active' : '' }}"
            href="{{ route('statistik.murid') }}">
            <i class="fas fa-chart-bar me-2"></i> Statistik Murid
        </a>

        <a class="nav-link {{ $isRegistrasiActive ? 'active' : 'collapsed' }}"
            href="#"
            data-bs-toggle="collapse" 
            data-bs-target="#collapseRegistrasi"
            aria-expanded="{{ $isRegistrasiActive ? 'true' : 'false' }}"
            aria-controls="collapseRegistrasi">
            <i class="fas fa-child me-2"></i> Registrasi Murid
            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
        </a>

        <div class="collapse {{ $isRegistrasiActive ? 'show' : '' }}" id="collapseRegistrasi">
            <nav class="sb-sidenav-menu-nested nav">
                 <a class="nav-link {{ request()->routeIs('students.*') ? 'active' : '' }}"
                    href="{{ route('students.index') }}">
                    <i class="fas fa-user-pen me-2"></i> Pendaftaran Murid Baru
                </a>
                <a class="nav-link {{ request()->routeIs('murid_trials.*') ? 'active' : '' }}"
                    href="{{ route('murid_trials.index') }}">
                    <i class="fas fa-user-plus me-2"></i> Murid Trial
                </a>
                <a class="nav-link {{ request()->routeIs('registrations.*') ? 'active' : '' }}"
                    href="{{ route('registrations.index') }}">
                    <i class="fas fa-user-check me-2"></i> Murid Baru
                </a>
            </nav>
        </div>

        {{-- Menu-menu lainnya --}}
        <a class="nav-link {{ request()->routeIs('buku_induk.*') ? 'active' : '' }}"
            href="{{ route('buku_induk.index') }}">
            <i class="fas fa-book me-2"></i> Buku Induk
        </a>
        <a class="nav-link {{ request()->routeIs('kartu-spp.*') ? 'active' : '' }}"
            href="{{ route('kartu-spp.index') }}">
            <i class="fas fa-id-card me-2"></i> Kartu SPP
        </a>
        <a class="nav-link {{ request()->routeIs('daftar_murid_deposit.*') ? 'active' : '' }}"
            href="{{ route('daftar_murid_deposit.index') }}">
            <i class="fas fa-list me-2"></i> Daftar Murid Deposit
        </a>
        <a class="nav-link {{ request()->routeIs('pindah-golongan.*') ? 'active' : '' }}"
            href="{{ route('pindah-golongan.index') }}">
            <i class="fas fa-random me-2"></i> Pindah Golongan
        </a>
        <a class="nav-link {{ request()->routeIs('sertifikat-beasiswa.*') ? 'active' : '' }}"
            href="{{ route('sertifikat-beasiswa.index') }}">
            <i class="fas fa-certificate me-2"></i> Masa Aktif (DHUAFA & BNF)
        </a>
        <a class="nav-link {{ request()->routeIs('garansi-bca.*') ? 'active' : '' }}"
            href="{{ route('garansi-bca.index') }}">
            <i class="fas fa-shield-alt me-2"></i> Garansi BCA 372
        </a>
        <a class="nav-link {{ request()->routeIs('mbc-murid.*') ? 'active' : '' }}"
            href="{{ route('mbc-murid.index') }}">
            <i class="fas fa-users me-2"></i> MBC Murid
        </a>
{{-- HUMAS - DIPINDAHKAN KE SINI (paling atas) --}}
        <a class="nav-link {{ request()->routeIs('humas.*') ? 'active' : '' }}"
            href="{{ route('humas.index') }}">
            <i class="fas fa-bullhorn me-2"></i> Humas
        </a>
    </nav>
</div>

               {{-- ================= MENU KEUANGAN (PARENT UTAMA) ================= --}}
@php
    // Parent Keuangan – mencakup SEMUA route di bawahnya
    $isKeuanganActive = request()->routeIs([
        'summary.keuangan',
        'harga.*',
        'voucher.*',
        'spp.*',
        'penerimaan.*',               // ← penting: mencakup semua penerimaan (spp, produk, rbas, index)
        'laporan.*',
        'skim.*',
        'pendapatan-tunjangan.*',
        'potongan.*',
        'pembayaran.*',
        'slip-tunjangan.*',
        'rekap-progresif.*',
        'pembayaran-progresif.*',
        'slip-progresif.*',
        'cara-perhitungan.*',
        'ktr.*',
        'rb.*',
        'penyesuaian.*',
        'durasi.*',
        'komisi.*',
        'slip-komisi.*',
        'pembayaran-komisi.*',
        'adjustment.*',
        'adjustments.*',
        'cash-advance.installments.*',
        'imbalan_rekap.*',
        'pettycash.*',
        'pengajuan.*',
        'rekap.petty.*',
    ]);
@endphp

<a class="nav-link {{ $isKeuanganActive ? 'active' : 'collapsed' }}" 
    href="#"
    data-bs-toggle="collapse"
    data-bs-target="#collapseKeuangan" 
    aria-expanded="{{ $isKeuanganActive ? 'true' : 'false' }}"
    aria-controls="collapseKeuangan">
    <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
    Keuangan
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isKeuanganActive ? 'show' : '' }}" id="collapseKeuangan"
    data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">

        {{-- Summary Keuangan --}}
        <a class="nav-link {{ request()->routeIs('summary.keuangan') ? 'active' : '' }}"
           href="{{ route('summary.keuangan') }}">
            <i class="fas fa-chart-line me-2"></i> Summary Keuangan
        </a>

        {{-- Harga --}}
        <a class="nav-link {{ request()->routeIs('harga.*') ? 'active' : '' }}"
           href="{{ route('harga.index') }}">
            <i class="fas fa-tag me-2"></i> Harga
        </a>

        {{-- Voucher --}}
        <a class="nav-link {{ request()->routeIs('voucher.*') ? 'active' : '' }}"
           href="{{ route('voucher.index') }}">
            <i class="fas fa-ticket-alt me-2"></i> Voucher
        </a>

        {{-- Terima SPP | Atribut (sub dropdown) --}}
@php
    // Parent: tetap seperti ini (sudah benar, mencakup semua penerimaan)
    $isTerimaSppActive = request()->routeIs([
        'penerimaan.*',
        'penerimaan.spp.*',
        'penerimaan.produk.*',
        'penerimaan.rbas.*'
    ]);
@endphp

<a class="nav-link {{ $isTerimaSppActive ? 'active' : 'collapsed' }}"
   href="#"
   data-bs-toggle="collapse"
   data-bs-target="#collapseTerimaSpp"
   aria-expanded="{{ $isTerimaSppActive ? 'true' : 'false' }}"
   aria-controls="collapseTerimaSpp">
    <i class="fas fa-receipt me-2"></i> Data Penerimaan
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isTerimaSppActive ? 'show' : '' }}" id="collapseTerimaSpp">
    <nav class="sb-sidenav-menu-nested nav">
        <a class="nav-link {{ request()->routeIs('penerimaan.index') ? 'active' : '' }}"
           href="{{ route('penerimaan.index') }}">
            Entry SPP | Atribut
        </a>

        <!-- FIX UTAMA: hapus .* di akhir untuk match route 'penerimaan.spp' -->
        <a class="nav-link {{ request()->routeIs('penerimaan.spp') ? 'active' : '' }}"
           href="{{ route('penerimaan.spp') }}">
            Rekap SPP
        </a>

        <a class="nav-link {{ request()->routeIs('penerimaan.produk') ? 'active' : '' }}"
           href="{{ route('penerimaan.produk') }}">
            Rekap Atribut
        </a>

        <a class="nav-link {{ request()->routeIs('penerimaan.rbas') ? 'active' : '' }}"
           href="{{ route('penerimaan.rbas') }}">
            Rekap RBAS
        </a>
    </nav>
</div>

        {{-- Data SPP --}}
        <a class="nav-link {{ request()->routeIs('spp.*') ? 'active' : '' }}"
           href="{{ route('spp.index') }}">
            <i class="fas fa-database me-2"></i> Data SPP
        </a>

        {{-- Imbalan (sub dropdown besar) --}}
@php
    // Helper agar lebih mudah dibaca dan ditambah pola baru
    $routeIs = function ($patterns) {
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }
        return false;
    };

    $isImbalanActive = $routeIs([
        'skim.*',
        'pendapatan-tunjangan.*',
        'potongan.*',
        'pembayaran.*',
        'slip-tunjangan.*',
        'rekap-progresif.*',
        'pembayaran-progresif.*',
        'slip-progresif.*',
        'cara-perhitungan.*',
        'ktr.*',
        'rb.*',
        'penyesuaian.*',
        'durasi.*',
        'komisi.*',
        'pembayaran-komisi.*',
        'slip-komisi.*',
        'adjustment.*',
        'adjustments.*',
        'cash-advance.installments.*',
        'imbalan_rekap.*',                // mencakup index, refresh, pdf, generate, dll
        'imbalan_rekap.slip.*',           // khusus untuk slip.index
        'imbalan_rekap.slips',            // variasi nama route slips
        'slip.index',                     // fallback jika prefix hilang
    ]);
@endphp

<a class="nav-link {{ $isImbalanActive ? 'active' : 'collapsed' }}"
   href="#"
   data-bs-toggle="collapse"
   data-bs-target="#collapseImbalan">
    <i class="fas fa-gift me-2"></i> Imbalan
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isImbalanActive ? 'show' : '' }}" id="collapseImbalan">
    <nav class="sb-sidenav-menu-nested nav">

        {{-- Perhitungan Lama --}}
        @php
            $isPerhitunganLamaActive = $routeIs([
                'skim.*',
                'pendapatan-tunjangan.*',
                'potongan.*',
                'pembayaran.*',               // non-progresif
                'slip-tunjangan.*',
                'rekap-progresif.*',
                'pembayaran-progresif.*',
                'slip-progresif.*',
                'cara-perhitungan.*',
            ]);
        @endphp

        <a class="nav-link {{ $isPerhitunganLamaActive ? 'active' : 'collapsed' }}"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#collapsePerhitunganLama"
           aria-expanded="{{ $isPerhitunganLamaActive ? 'true' : 'false' }}"
           aria-controls="collapsePerhitunganLama">
            Perhitungan Lama
            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
        </a>

        <div class="collapse {{ $isPerhitunganLamaActive ? 'show' : '' }}" id="collapsePerhitunganLama">
            <nav class="sb-sidenav-menu-nested nav">

                {{-- Tunjangan --}}
                @php
                    $isTunjanganActive = $routeIs([
                        'skim.*',
                        'pendapatan-tunjangan.*',
                        'potongan.*',
                        'pembayaran.*',
                        'slip-tunjangan.*',
                    ]);
                @endphp

                <a class="nav-link {{ $isTunjanganActive ? 'active' : 'collapsed' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#collapseTunjangan"
                   aria-expanded="{{ $isTunjanganActive ? 'true' : 'false' }}"
                   aria-controls="collapseTunjangan">
                    Tunjangan
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse {{ $isTunjanganActive ? 'show' : '' }}" id="collapseTunjangan">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ $routeIs('skim.*') ? 'active' : '' }}" href="{{ route('skim.index') }}">Skim</a>
                        <a class="nav-link {{ $routeIs('pendapatan-tunjangan.*') ? 'active' : '' }}" href="{{ route('pendapatan-tunjangan.index') }}">Pendapatan Tunjangan</a>
                        <a class="nav-link {{ $routeIs('potongan.*') ? 'active' : '' }}" href="{{ route('potongan.index') }}">Potongan</a>
                        <a class="nav-link {{ $routeIs('pembayaran.*') && !$routeIs('pembayaran-progresif.*') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">Pembayaran</a>
                        <a class="nav-link {{ $routeIs('slip-tunjangan.*') ? 'active' : '' }}" href="{{ route('slip-tunjangan.index') }}">Slip Tunjangan</a>
                    </nav>
                </div>

                {{-- Progressif --}}
                @php
                    $isProgressifActive = $routeIs([
                        'rekap-progresif.*',
                        'pembayaran-progresif.*',
                        'slip-progresif.*',
                        'cara-perhitungan.*',
                    ]);
                @endphp

                <a class="nav-link {{ $isProgressifActive ? 'active' : 'collapsed' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#collapseProgressif"
                   aria-expanded="{{ $isProgressifActive ? 'true' : 'false' }}"
                   aria-controls="collapseProgressif">
                    Progressif
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse {{ $isProgressifActive ? 'show' : '' }}" id="collapseProgressif">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ $routeIs('rekap-progresif.*') ? 'active' : '' }}" href="{{ route('rekap-progresif.index') }}">Rekap Progresif</a>
                        <a class="nav-link {{ $routeIs('pembayaran-progresif.*') ? 'active' : '' }}" href="{{ route('pembayaran-progresif.index') }}">Pembayaran Progresif</a>
                        <a class="nav-link {{ $routeIs('slip-progresif.*') ? 'active' : '' }}" href="{{ route('slip-progresif.index') }}">Slip Progresif</a>
                        <a class="nav-link {{ $routeIs('cara-perhitungan.*') ? 'active' : '' }}" href="{{ route('cara-perhitungan.index') }}">Cara Perhitungan</a>
                    </nav>
                </div>

            </nav>
        </div>

        {{-- Perhitungan RB | KTR --}}
        @php
            $isRbKtrActive = $routeIs([
                'ktr.*',
                'rb.*',
                'penyesuaian.*',
                'durasi.*',
                'komisi.*',
                'pembayaran-komisi.*',
                'slip-komisi.*',
                'adjustment.*',
                'adjustments.*',
                'cash-advance.installments.*',
                'imbalan_rekap.*',                // semua route di bawah prefix imbalan_rekap.
                'imbalan_rekap.slip.*',           // khusus slip
                'imbalan_rekap.slips',            // variasi nama
                'slip.index',                     // fallback
            ]);
        @endphp

        <a class="nav-link {{ $isRbKtrActive ? 'active' : 'collapsed' }}"
           href="#"
           data-bs-toggle="collapse"
           data-bs-target="#collapseRbKtr"
           aria-expanded="{{ $isRbKtrActive ? 'true' : 'false' }}"
           aria-controls="collapseRbKtr">
            Perhitungan RB | KTR
            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
        </a>

        <div class="collapse {{ $isRbKtrActive ? 'show' : '' }}" id="collapseRbKtr">
            <nav class="sb-sidenav-menu-nested nav">

                {{-- Data (sub dropdown) --}}
                @php
                    $isDataRbKtrActive = $routeIs([
                        'ktr.*',
                        'rb.*',
                        'penyesuaian.*',
                        'durasi.*',
                    ]);
                @endphp

                <a class="nav-link {{ $isDataRbKtrActive ? 'active' : 'collapsed' }}"
                   href="#"
                   data-bs-toggle="collapse"
                   data-bs-target="#collapseDataRbKtr"
                   aria-expanded="{{ $isDataRbKtrActive ? 'true' : 'false' }}"
                   aria-controls="collapseDataRbKtr">
                    Data
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>

                <div class="collapse {{ $isDataRbKtrActive ? 'show' : '' }}" id="collapseDataRbKtr">
                    <nav class="sb-sidenav-menu-nested nav nav-second-level">
                        <a class="nav-link {{ $routeIs('ktr.*') ? 'active' : '' }}"
                           href="{{ route('ktr.index') }}">KTR</a>
                        <a class="nav-link {{ $routeIs('rb.*') ? 'active' : '' }}"
                           href="{{ route('rb.index') }}">RB</a>
                        <a class="nav-link {{ $routeIs('penyesuaian.*') ? 'active' : '' }}"
                           href="{{ route('penyesuaian.index') }}">Penyesuaian</a>
                        <a class="nav-link {{ $routeIs('durasi.*') ? 'active' : '' }}"
                           href="{{ route('durasi.index') }}">Durasi</a>
                    </nav>
                </div>

                {{-- Potongan | Tambahan --}}
                <a class="nav-link {{ $routeIs(['adjustment.*', 'adjustments.*']) ? 'active' : '' }}"
                   href="{{ route('adjustments.index') }}">
                    Potongan | Tambahan
                </a>

                {{-- Cicilan Cash Advance --}}
                <a class="nav-link {{ $routeIs('cash-advance.installments.*') ? 'active' : '' }}"
                   href="{{ route('cash-advance.installments.index') }}">
                    Cicilan Cash Advance
                </a>

                {{-- Komisi --}}
                <a class="nav-link {{ $routeIs(['komisi.*']) ? 'active' : '' }}"
                   href="{{ route('komisi.index') }}">
                    Komisi
                </a>

                <a class="nav-link {{ $routeIs('pembayaran-komisi.*') ? 'active' : '' }}"
                   href="{{ route('pembayaran-komisi.index') }}">
                    Pembayaran Komisi
                </a>

                {{-- Slip Komisi --}}
                <a class="nav-link {{ $routeIs(['slip-komisi.*', 'slipkomisi.*', 'slip-komisi.index']) ? 'active' : '' }}"
                   href="{{ route('slip-komisi.index') ?? url('/slip-komisi') }}">
                    Slip Komisi
                </a>

                {{-- Imbalan Relawan --}}
                <a class="nav-link {{ $routeIs('imbalan_rekap.*') ? 'active' : '' }}"
                   href="{{ route('imbalan_rekap.index') }}">
                    Imbalan Relawan
                </a>

                {{-- Slip Imbalan --}}
                <a class="nav-link {{ $routeIs(['imbalan_rekap.slip.*', 'imbalan_rekap.slips', 'slip.index']) ? 'active' : '' }}"
                   href="{{ route('imbalan_rekap.slip.index') ?? url('/imbalan-rekap/slips') }}">
                    Slip Imbalan
                </a>
            </nav>
        </div>
    </nav>
</div>

{{-- Petty Cash --}}
@php
    $isPettyCashActive = $routeIs([
        'pettycash.*',
        'pengajuan.*',
        'rekap.petty.*',
    ]);
@endphp

<a class="nav-link {{ $isPettyCashActive ? 'active' : 'collapsed' }}"
   href="#"
   data-bs-toggle="collapse"
   data-bs-target="#collapsePettyCash"
   aria-expanded="{{ $isPettyCashActive ? 'true' : 'false' }}"
   aria-controls="collapsePettyCash">
    <i class="fas fa-wallet me-2"></i> Petty Cash
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>

<div class="collapse {{ $isPettyCashActive ? 'show' : '' }}" id="collapsePettyCash">
    <nav class="sb-sidenav-menu-nested nav">
        <a class="nav-link {{ $routeIs('pettycash.*') ? 'active' : '' }}"
           href="{{ route('pettycash.index') }}">
            Daftar Petty Cash
        </a>
        <a class="nav-link {{ $routeIs('pengajuan.*') ? 'active' : '' }}"
           href="{{ route('pengajuan.index') }}">
            Pengajuan
        </a>
        <a class="nav-link {{ $routeIs('rekap.petty.*') ? 'active' : '' }}"
           href="{{ route('rekap.petty.index') }}">
            Rekap
        </a>
    </nav>
</div>
    </nav>
</div>

{{-- AKHIR MENU KEUANGAN --}}
                




                <!-- Menu DPU -->
                <a class="nav-link {{ request()->routeIs('perkembangan_units.*') ? 'active' : '' }}"
                    href="{{ route('perkembangan_units.index') }}">
                    <div class="sb-nav-link-icon"><i class="fas fa-building"></i></div>
                    DPU
                </a>

                {{-- MENU MODUL --}}
                @php
                    // Gunakan NAMA ROUTE biar akurat
                    $isModulActive = request()->routeIs(
                        'produk.*',
                        'penerimaan_produk.*',
                        'pemakaian_produk.*',
                        'data_produk.*'
                    );
                @endphp

                <a class="nav-link {{ $isModulActive ? 'active' : 'collapsed' }}"
            href="#"
            data-bs-toggle="collapse"
            data-bs-target="#collapseModul"
            aria-expanded="{{ $isModulActive ? 'true' : 'false' }}"
            aria-controls="collapseModul">
            <i class="fas fa-wallet me-2"></i> Modul
            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
        </a>

                <div class="collapse {{ $isModulActive ? 'show' : '' }}" id="collapseModul">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link {{ request()->routeIs('produk.*') ? 'active' : '' }}"
                            href="{{ route('produk.index') }}">Daftar Produk</a>


                        {{-- Penerimaan (Modul Produk) --}}
                        <a class="nav-link {{ request()->routeIs('penerimaan_produk.*') ? 'active' : '' }}"
                            href="{{ route('penerimaan_produk.index') }}">
                            Penerimaan (Produk)
                        </a>

                        <a class="nav-link {{ request()->routeIs('pemakaian_produk.*') ? 'active' : '' }}"
                            href="{{ route('pemakaian_produk.index') }}">Pemakaian</a>

                        <a class="nav-link {{ request()->routeIs('data_produk.*') ? 'active' : '' }}"
                            href="{{ route('data_produk.index') }}">Rekap Stok</a>
                    </nav>
                </div>


                {{-- Menu Order --}}
                @php
    $isOrderActive = request()->routeIs([
        'order_modul.*',
        'pemesanan_kaos.*',
        'pemesanan_sertifikat.*',
        'pemesanan_stpb.*',
        'pemesanan_perlengkapan_unit.*'
    ]);
@endphp

<a class="nav-link collapsed {{ $isOrderActive ? 'active' : '' }}" href="#" data-bs-toggle="collapse"
    data-bs-target="#collapseOrder" aria-expanded="{{ $isOrderActive ? 'true' : 'false' }}">
    <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
    Order
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>
<div class="collapse {{ $isOrderActive ? 'show' : '' }}" id="collapseOrder"
    data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        <a class="nav-link {{ request()->routeIs('order_modul.*') ? 'active' : '' }}"
            href="{{ route('order_modul.index') }}">Order Modul</a>
        <a class="nav-link {{ request()->routeIs('pemesanan_kaos.*') ? 'active' : '' }}"
            href="{{ route('pemesanan_kaos.index') }}">KA | KPK | Tas | Kaos | RBAS dan lainnya</a>
        <a class="nav-link {{ request()->routeIs('pemesanan_sertifikat.*') ? 'active' : '' }}"
            href="{{ route('pemesanan_sertifikat.index') }}">Pemesanan Sertifikat</a>
        <a class="nav-link {{ request()->routeIs('pemesanan_stpb.*') ? 'active' : '' }}"
            href="{{ route('pemesanan_stpb.index') }}">STPB</a>
        <a class="nav-link {{ request()->routeIs('pemesanan_perlengkapan_unit.*') ? 'active' : '' }}"
            href="{{ route('pemesanan_perlengkapan_unit.index') }}">ATK | Perlengkapan Unit</a>
    </nav>
</div>
                @if(auth()->check() && auth()->user()->isAdminUser())

    {{-- Heading seperti menu-menu lain (contoh: Keuangan, Murid, dll) --}}
    <div class="sb-sidenav-menu-heading text-black">ADMIN ONLY</div>

    {{-- Menu utama — sama persis formatnya dengan yang lain --}}
    <a class="nav-link {{ request()->routeIs('admin.perkembangan-units.*') ? 'active' : '' }}"
   href="{{ route('admin.perkembangan-units.index') }}">
    <div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>
    Rekap Perkembangan Semua Unit
</a>

<!-- BARU: Rekap Pengeluaran Admin (tambahkan di sini agar berada di grup rekap/keuangan) -->
<!--<a class="nav-link {{ request()->routeIs('admin.rekap-pengeluaran.*') ? 'active' : '' }}"
   href="{{ route('admin.rekap-pengeluaran.index') }}">
    <div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
    Rekap Pengeluaran Admin
</a>-->

<a class="nav-link {{ request()->routeIs('cash-advance.*') ? 'active' : '' }}"
   href="{{ route('cash-advance.index') }}">
    <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
    Cash Advance
</a>

    {{-- TAMBAHAN: MANAJEMEN USER & UNIT biMBA --}}
    @php
        $user = auth()->user();

        // Email khusus yang boleh akses Manajemen User meskipun role biasa
        $emailKhusus = [
            'oktaviandaaria@gmail.com',
            'robiensyah22@gmail.com',
            // tambah lagi di sini kalau nanti ada owner/pengawas lain
        ];
    @endphp

    {{-- MANAJEMEN USER --}}
    {{-- Muncul jika: admin pusat (karena sudah di dalam if isAdminUser), 
         ATAU role developer/superadmin/owner/direktur, 
         ATAU email khusus --}}
    @if(
            $user &&
            (
                in_array($user->role, ['developer', 'superadmin', 'owner', 'direktur']) ||
                in_array($user->email, $emailKhusus)
            )
        )
        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"
            href="{{ route('users.index') }}">
            <div class="sb-nav-link-icon"><i class="fas fa-user-shield"></i></div>
            Manajemen User
        </a>
        
    @endif

@endif

            </div>
        </div>

    </nav>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidenav = document.getElementById('layoutSidenav_nav');
    if (!sidenav) return;

    // Cari tombol toggle (biasanya ada id sidebarToggle atau sidebarToggleTop di SB Admin)
    const toggleButtons = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');

    // Fungsi untuk menutup sidebar
    function closeSidebar() {
        if (document.body.classList.contains('sb-sidenav-toggled')) {
            document.body.classList.remove('sb-sidenav-toggled');
            
            // Optional: tutup semua submenu yang terbuka
            const openCollapses = sidenav.querySelectorAll('.collapse.show');
            openCollapses.forEach(el => {
                bootstrap.Collapse.getInstance(el)?.hide();
            });
        }
    }

    // Event klik di seluruh document
    document.addEventListener('click', function (event) {
        // Jangan tutup jika:
        // - Klik di dalam sidebar
        // - Klik di tombol toggle (biar bisa buka/tutup normal)
        let isInsideSidebar  = sidenav.contains(event.target);
        let isToggleButton   = false;

        toggleButtons.forEach(btn => {
            if (btn && btn.contains(event.target)) {
                isToggleButton = true;
            }
        });

        if (isInsideSidebar || isToggleButton) {
            return; // jangan tutup
        }

        // Jika sidebar terbuka → tutup
        closeSidebar();
    });

    // BONUS: Di mobile (lebar < 992px), tutup sidebar setelah klik salah satu link menu
    if (window.innerWidth < 992) {
        const navLinks = sidenav.querySelectorAll('.nav-link[href]:not([href="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function () {
                setTimeout(closeSidebar, 150); // beri sedikit delay agar navigasi selesai dulu
            });
        });
    }

    // Optional: resize listener (jika window di-resize ke mobile, tutup collapse)
    window.addEventListener('resize', function () {
        if (window.innerWidth < 992) {
            const collapses = sidenav.querySelectorAll('.collapse.show');
            collapses.forEach(el => bootstrap.Collapse.getInstance(el)?.hide());
        }
    });
});

// Saat sidebar dibuka → nonaktifkan scroll body
function toggleBodyScroll() {
  if (document.body.classList.contains('sb-sidenav-toggled')) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
}

// Panggil saat toggle
const toggleButtons = document.querySelectorAll('#sidebarToggle, #sidebarToggleTop');
toggleButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    setTimeout(toggleBodyScroll, 10); // delay kecil biar class sudah update
  });
});

// Panggil awal (jika sidebar open dari awal)
toggleBodyScroll();

// Saat resize ke desktop → kembalikan scroll body
window.addEventListener('resize', () => {
  if (window.innerWidth >= 992) {
    document.body.style.overflow = '';
  }
});
</script>