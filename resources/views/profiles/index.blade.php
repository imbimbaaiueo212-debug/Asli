@extends('layouts.app')

@section('title', 'Data Relawan')

@section('content')
<div class="min-h-screen from-cyan-50 to-blue-50 native-container native-scroll flex flex-col">

    <!-- App Bar Header (Mobile) -->
    <div class="d-block d-lg-none app-bar">
        <div class="px-6 py-7">
            <h1 class="text-4xl font-black mb-2 text-center">Data Relawan</h1>
            <p class="text-cyan-100 text-lg opacity-90">Kelola relawan biMBA-AIUEO</p>
        </div>
    </div>

    <!-- FILTER - DIPINDAH KE ATAS (tepat di bawah navbar mobile) -->
    <div class="d-block d-lg-none filter-wrapper">
        <div class="bg-white rounded-4 shadow-sm border p-4">
            <form method="GET" action="{{ route('profiles.index') }}" id="filterForm">
                <div class="space-y-6">
                    <div>
                        <label class="block text-xl font-bold text-gray-800 mb-3">Nama / NIK Relawan</label>
                        <select name="search" id="searchProfile" class="w-full">
                            <option value="">-- Pilih Relawan --</option>
                            @foreach($profileOptions as $p)
                                <option value="{{ $p->id }}" {{ request('search') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nik }} | {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Unit biMBA – HANYA UNTUK ADMIN -->
                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                        <div>
                            <label class="block text-xl font-bold text-gray-800 mb-3">Unit biMBA</label>
                            <select name="unit" id="unitFilter" class="w-full">
                                <option value="">-- Semua Unit --</option>
                                @foreach($unitOptions as $unit)
                                    <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-5">
                        <button type="submit" class="btn btn-primary btn-native font-bold">Terapkan Filter</button>
                        @if(request()->hasAny(['search', 'unit']))
                            <a href="{{ route('profiles.index') }}" class="btn btn-outline-secondary btn-native text-center font-bold">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Tambah & Alert (tetap di bawah filter) -->
    <div class="d-block d-lg-none px-4 py-4">
        <a href="{{ route('profiles.create') }}"
           class="btn btn-primary btn-native w-full shadow-2xl hover:shadow-3xl transition-all block text-center mb-5">
            <i class="fas fa-user-plus text-3xl me-4"></i>
            <span class="font-bold text-xl">Tambah Data</span>
        </a>

        @if(session('success'))
            <div class="bg-green-100 border-2 border-green-400 text-green-800 px-8 py-6 rounded-3xl text-center font-bold text-xl shadow-xl mb-5">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <!-- DESKTOP: TABEL LENGKAP dengan Modal Info Relawan (Ringkas & Diperbaiki) -->
<div class="d-none d-lg-block container-fluid px-4 py-4">
    <h2 class="mt-2 mb-4 fw-bold text-center">Data Relawan</h2>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-semibold">Filter & Pencarian</h5>
                <a href="{{ route('profiles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Tambah Data
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- FILTER DESKTOP -->
            <form method="GET" action="{{ route('profiles.index') }}" id="filterFormDesktop" class="mb-4">
                <div class="row g-3 align-items-end">
                    @if (auth()->check() && (auth()->user()->is_admin ?? false))
                        <div class="col-lg-3">
                            <label class="form-label fw-bold small">Unit biMBA</label>
                            <select name="unit" id="unitFilterDesktop" class="form-select form-select-sm">
                                <option value="">-- Semua Unit --</option>
                                @foreach($unitOptions as $unit)
                                    <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '5' : '8' }}">
                        <label class="form-label fw-bold small">Nama / NIK Relawan</label>
                        <select name="search" id="searchProfileDesktop" class="form-select form-select-sm">
                            <option value="">-- Cari atau pilih relawan --</option>
                            @foreach($profileOptions as $p)
                                <option value="{{ $p->id }}" {{ request('search') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nik }} | {{ $p->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '4' : '4' }} d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Terapkan Filter</button>
                        @if(request()->hasAny(['search', 'unit']))
                            <a href="{{ route('profiles.index') }}" class="btn btn-outline-secondary btn-sm flex-grow-1">Reset</a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- TABEL -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2" class="text-center align-middle">No</th>
                            <th rowspan="2" class="text-center">NIK</th>
                            <th rowspan="2" class="text-center">Nama</th>
                            <th rowspan="2" class="text-center" style="min-width: 160px;">Info Relawan</th>

                            <th colspan="3" class="text-center">JUMLAH MURID</th>
                            <th colspan="2" class="text-center">PENYESUAIAN RB MTV</th>
                            <th colspan="5" class="text-center">PERHITUNGAN RB & KTR</th>
                            <th colspan="3" class="text-center">DATA MAGANG</th>
                            <th colspan="5" class="text-center">PENDATAAN SERAGAM</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                        <tr>
                            <th class="text-center">MBA</th>
                            <th class="text-center">ENG</th>
                            <th class="text-center">TTL</th>

                            <th class="text-center">J. Murid (JDWL)</th>
                            <th class="text-center">Rombim</th>

                            <th class="text-center">RB</th>
                            <th class="text-center">RB !</th>
                            <th class="text-center">KTR</th>
                            <th class="text-center">KTR !</th>
                            <th class="text-center">RP</th>

                            <th class="text-center">Mentor</th>
                            <th class="text-center">Periode</th>
                            <th class="text-center">Tgl Selesai</th>

                            <th>Kaos K-H</th>
                            <th>Kaos M-K-B</th>
                            <th>Kemeja</th>
                            <th>Blazer Merah</th>
                            <th>Blazer Biru</th>
                        </tr>
                    </thead>
                    <tbody>
                       @foreach ($profiles as $profile)
    @php
        $formatDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '-';
        
        $tglMasuk       = $formatDate($profile->tgl_masuk);
        $tglLahir       = $formatDate($profile->tgl_lahir);
        $tglMutasi      = $formatDate($profile->tgl_mutasi_jabatan);
        $tglSelesaiMagang = $formatDate($profile->tgl_selesai_magang);
        $tglAmbilSeragam  = $formatDate($profile->tgl_ambil_seragam);
        $tglKeluar        = $formatDate($profile->tgl_keluar);

        $masaKerja = $profile->masa_kerja !== null
            ? intdiv($profile->masa_kerja, 12) . ' th ' . ($profile->masa_kerja % 12) . ' bl'
            : '-';
        $tempatLahir = $profile->tempat_lahir ?? '-';

        // Fungsi untuk menentukan warna baris
        $rowClass = 'table-light'; // default

        $status = strtolower(trim($profile->status_karyawan ?? ''));
        if (in_array($status, ['keluar', 'resign'])) {
            $rowClass = 'table-danger';           // Merah muda (seperti contohmu)
        } elseif (in_array($status, ['non-aktif', 'non-aktif sementara', 'nonaktif'])) {
            $rowClass = 'table-warning text-dark';  // Hitam
        } elseif (str_contains($status, 'sementara')) {
            $rowClass = 'table-warning';          // Kuning
        }
    @endphp

    <!-- Baris dengan warna sesuai status -->
    <tr data-id="{{ $profile->id }}" class="{{ $rowClass }}">
        <td class="text-center">{{ $loop->iteration }}</td>
        <td class="text-center">{{ $profile->nik }}</td>
        <td class="text-start fw-medium">{{ $profile->nama }}</td>

        <!-- KOLOM INFO RELAWAN -->
        <td class="text-center">
            <button class="btn btn-outline-primary btn-sm info-relawan-btn"
                data-bs-toggle="modal" 
                data-bs-target="#infoModal"
                data-nama="{{ $profile->nama }}"
                data-nik="{{ $profile->nik }}"
                data-jabatan="{{ $profile->jabatan ?? '-' }}"
                data-status="{{ $profile->status_karyawan ?? '-' }}"
                data-tgl-keluar="{{ $tglKeluar }}"
                data-keterangan-keluar="{{ $profile->keterangan_keluar ?? '-' }}"
                data-departemen="{{ $profile->departemen ?? '-' }}"
                data-unit="{{ $profile->bimba_unit ?? '-' }}"
                data-cabang="{{ $profile->no_cabang ?? '-' }}"
                data-tgl-masuk="{{ $tglMasuk }}"
                data-tgl_selesai_magang="{{ $tglSelesaiMagang }}"
                data-masa-kerja="{{ $masaKerja }}"
                data-tempat-lahir="{{ $tempatLahir }}"
                data-tgl-lahir="{{ $tglLahir }}"
                data-usia="{{ $profile->usia_format ?? '0 tahun 0 bulan' }}"
                data-telp="{{ $profile->no_telp ?? '-' }}"
                data-email="{{ $profile->email ?? '-' }}"
                data-jenis-mutasi="{{ $profile->jenis_mutasi ?? '-' }}"
                data-tgl-mutasi="{{ $tglMutasi }}"
                data-masa-jabatan="{{ $profile->masa_kerja_jabatan ?? '-' }}"
                data-no-rek="{{ $profile->no_rekening ?? '-' }}"
                data-bank="{{ $profile->bank ?? '-' }}"
                data-atas-nama="{{ $profile->atas_nama ?? '-' }}">
                
                <i class="fas fa-info-circle me-1"></i> Detail
            </button>
        </td>


                                <!-- JUMLAH MURID -->
                                <td class="text-center">{{ $profile->jumlah_murid_mba ?? 0 }}</td>
                                <td class="text-center">{{ $profile->jumlah_murid_eng ?? 0 }}</td>
                                <td class="text-center">{{ $profile->total_murid ?? 0 }}</td>

                                <td class="text-center">
                                    {{ $profile->jumlah_murid_jadwal ? $profile->jumlah_murid_jadwal . ($profile->rb ? ' (RB'.$profile->rb.')' : '') : '-' }}
                                </td>
                                <td class="text-center">{{ $profile->jumlah_rombim ?? '-' }}</td>

                                <!-- RB & KTR -->
                                <td class="text-center">
                                    @if(auth()->user()->is_admin ?? false)
                                        <select class="inline-rb-select form-select form-select-sm" data-id="{{ $profile->id }}">
                                            <option value="auto" {{ empty($profile->rb) ? 'selected' : '' }}>Auto</option>
                                            @foreach($rbOptions as $rb)
                                                <option value="{{ $rb }}" {{ $profile->rb == $rb ? 'selected' : '' }}>{{ $rb }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $profile->rb ?? '-' }}
                                    @endif
                                </td>
                                <td class="kolom-rb-calc text-primary fw-bold text-center">{{ $profile->rb_tambahan ?? '-' }}</td>

                               <!-- KOLOM KTR -->
                            <td class="text-center">
                                @if(auth()->user()->is_admin ?? false)
                                    <select class="inline-ktr-select form-select form-select-sm" data-id="{{ $profile->id }}">
                                        <option value="Otomatis" {{ empty($profile->ktr_tambahan) ? 'selected' : '' }}>Auto</option>
                                        @foreach($ktrOptions as $ktr)
                                            <option value="{{ $ktr }}" {{ $profile->ktr_tambahan == $ktr ? 'selected' : '' }}>
                                                {{ $ktr }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <!-- Tampilan untuk non-admin + fallback Auto -->
                                    {{ $profile->ktr_tambahan ?? $profile->ktr ?? '-' }}
                                @endif
                            </td>

                            <!-- KOLOM KTR ! (tambahan / hasil perhitungan) -->
                            <td class="text-center text-primary fw-bold">
                                {{ $profile->ktr_tambahan ?? $profile->ktr ?? '-' }}
                            </td>

                                <td class="text-center fw-bold text-success">
                                    {{ $profile->rp ? number_format($profile->rp, 0, ',', '.') : '-' }}
                                </td>

                                <!-- DATA MAGANG -->
                                <td class="text-center">
                                    @if(auth()->user()->is_admin ?? false)
                                        <select class="inline-edit form-select form-select-sm" data-id="{{ $profile->id }}" data-field="mentor_magang">
                                            <option value="">-</option>
                                            @foreach($mentors as $m)
                                                <option value="{{ $m->nama }}" {{ $profile->mentor_magang == $m->nama ? 'selected' : '' }}>{{ $m->nama }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{ $profile->mentor_magang ?? '-' }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(auth()->user()->is_admin ?? false)
                                        <select class="inline-edit form-select form-select-sm" data-id="{{ $profile->id }}" data-field="periode">
                                            <option value="">-</option>
                                            @for($i=1; $i<=12; $i++)
                                                <option value="Ke - {{ $i }}" {{ $profile->periode == "Ke - $i" ? 'selected' : '' }}>Ke - {{ $i }}</option>
                                            @endfor
                                        </select>
                                    @else
                                        {{ $profile->periode ?? '-' }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(auth()->user()->is_admin ?? false)
                                        <input type="date" class="inline-edit form-control form-control-sm" data-id="{{ $profile->id }}" data-field="tgl_selesai_magang" value="{{ $profile->tgl_selesai_magang?->format('Y-m-d') }}">
                                        <small class="d-block text-muted mt-1">{{ $tglSelesaiMagang }}</small>
                                    @else
                                        {{ $tglSelesaiMagang }}
                                    @endif
                                </td>

                                <!-- SERAGAM -->
                                <td class="text-center p-1"><input type="text" class="form-control form-control-sm text-center seragam-direct" value="{{ $profile->kaos_kuning_hitam ?? '' }}" data-field="kaos_kuning_hitam" {{ !(auth()->user()->is_admin ?? false) ? 'readonly' : '' }}></td>
                                <td class="text-center p-1"><input type="text" class="form-control form-control-sm text-center seragam-direct" value="{{ $profile->kaos_merah_kuning_biru ?? '' }}" data-field="kaos_merah_kuning_biru" {{ !(auth()->user()->is_admin ?? false) ? 'readonly' : '' }}></td>
                                <td class="text-center p-1"><input type="text" class="form-control form-control-sm text-center seragam-direct" value="{{ $profile->kemeja_kuning_hitam ?? '' }}" data-field="kemeja_kuning_hitam" {{ !(auth()->user()->is_admin ?? false) ? 'readonly' : '' }}></td>
                                <td class="text-center p-1 text-danger"><input type="text" class="form-control form-control-sm text-center seragam-direct fw-bold" value="{{ $profile->blazer_merah ?? '' }}" data-field="blazer_merah" {{ !(auth()->user()->is_admin ?? false) ? 'readonly' : '' }}></td>
                                <td class="text-center p-1 text-primary"><input type="text" class="form-control form-control-sm text-center seragam-direct fw-bold" value="{{ $profile->blazer_biru ?? '' }}" data-field="blazer_biru" {{ !(auth()->user()->is_admin ?? false) ? 'readonly' : '' }}></td>

                                <!-- Aksi -->
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('profiles.edit', $profile->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                        @if(auth()->user()->is_admin ?? false)
                                            <form action="{{ route('profiles.destroy', $profile->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus {{ addslashes($profile->nama) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                            <!-- Tombol Histori -->
                                            <button class="btn btn-info btn-sm text-white histori-btn ms-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#historiModal"
                                                    data-id="{{ $profile->id }}"
                                                    data-nama="{{ addslashes($profile->nama) }}">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!-- MOBILE: CARD VIEW dengan DETAIL LENGKAP (Expandable) -->
<div class="d-block d-lg-none w-100 max-w-md mx-auto border-t border-gray-200 shadow-sm my-6 px-3">
    <div class="mobile-list-container py-3">

        @forelse($profiles as $profile)
            @php
                $formatDate = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d M Y') : '-';
                $masaKerja = $profile->masa_kerja !== null
                    ? intdiv($profile->masa_kerja, 12).' th '.($profile->masa_kerja % 12).' bln'
                    : '-';
            @endphp

            <div class="relawan-card-item mb-4 pb-4 border-b border-gray-200" data-id="{{ $profile->id }}">
                
                <!-- HEADER - SELALU TERLIHAT -->
                <div class="card-header pb-3 border-b border-gray-100 position-relative" onclick="toggleDetail({{ $profile->id }})">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="fw-bold fs-5 mb-1 lh-sm">{{ $profile->nama }}</div>
                            <div class="text-muted small mb-1">
                                <i class="fas fa-id-card me-1"></i>{{ $profile->nik }}
                            </div>
                            <div class="badge bg-primary bg-opacity-20 text-white px-3 py-1 rounded-pill small fw-semibold">
                                {{ $profile->jabatan ?? '-' }}
                            </div>
                        </div>
                        <div class="text-end ms-2">
                            <span class="badge bg-secondary bg-opacity-25 px-3 py-2 rounded-pill fs-6 fw-bold">
                                #{{ $loop->iteration }}
                            </span>
                            <i class="fas fa-chevron-down detail-toggle fs-5 text-muted ms-2" data-id="{{ $profile->id }}"></i>
                        </div>
                    </div>

                    <!-- Quick Stats - SELALU TERLIHAT -->
                    <div class="row row-cols-3 g-2 mt-3 pt-2 border-top border-gray-100">
                        <div class="col text-center">
                            <div class="fw-bold text-success fs-6">{{ $profile->total_murid ?? 0 }}</div>
                            <small class="text-muted">Total Murid</small>
                        </div>
                        <div class="col text-center">
                            <div class="fw-bold text-primary fs-6">{{ $profile->rb ?? '-' }}</div>
                            <small class="text-muted">RB</small>
                        </div>
                        <div class="col text-center">
                            <div class="fw-bold text-info fs-6">{{ $profile->ktr ?? '-' }}</div>
                            <small class="text-muted">KTR</small>
                        </div>
                    </div>
                </div>

                <!-- DETAIL LENGKAP - HIDDEN by default -->
                <div class="detail-content" id="detail-{{ $profile->id }}" style="display: none;">
                    
                    <!-- INFO UTAMA -->
                    <div class="mobile-section py-4 border-t border-gray-100">
                        <div class="mobile-title fw-bold mb-3 fs-6 border-start border-primary border-4 ps-3">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Informasi Utama
                        </div>
                        <div class="row row-cols-2 g-3 small">
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Status</span>
                                <span class="mobile-value fw-semibold badge {{ $profile->status_karyawan == 'Aktif' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $profile->status_karyawan ?? '-' }}
                                </span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Departemen</span>
                                <span class="mobile-value fw-medium">{{ $profile->departemen ?? '-' }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Unit biMBA</span>
                                <span class="mobile-value fw-medium">{{ $profile->bimba_unit ?? '-' }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Cabang</span>
                                <span class="mobile-value fw-medium">{{ $profile->no_cabang ?? '-' }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Tgl Masuk</span>
                                <span class="mobile-value fw-medium">{{ $formatDate($profile->tgl_masuk) }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Selesai Magang</span>
                                <span class="mobile-value fw-medium text-success">{{ $formatDate($profile->tgl_selesai_magang) }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Masa Kerja</span>
                                <span class="mobile-value fw-bold text-primary">{{ $masaKerja }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">RP</span>
                                <span class="mobile-value fw-bold text-success fs-6">
                                    {{ $profile->rp ? 'Rp ' . number_format($profile->rp, 0, ',', '.') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- DATA PRIBADI -->
                    <div class="mobile-section py-4 border-t border-gray-100">
                        <div class="mobile-title fw-bold mb-3 fs-6 border-start border-info border-4 ps-3">
                            <i class="fas fa-user me-2 text-info"></i>Data Pribadi
                        </div>
                        <div class="row row-cols-2 g-3 small">
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Tgl Lahir</span>
                                <span class="mobile-value fw-medium">{{ $formatDate($profile->tgl_lahir) }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Usia</span>
                                <span class="mobile-value fw-medium">{{ $profile->usia_format ?? '0 tahun 0 bulan' }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Telepon</span>
                                <span class="mobile-value fw-medium">{{ $profile->no_telp ?? '-' }}</span>
                            </div>
                            <div class="col">
                                <span class="mobile-label text-muted d-block mb-1">Email</span>
                                <span class="mobile-value fw-medium">{{ $profile->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- RB & KTR DETAIL + EDIT (Admin) -->
                    <div class="mobile-section py-4 border-t border-gray-100">
                        <div class="mobile-title fw-bold mb-3 fs-6 border-start border-success border-4 ps-3">
                            <i class="fas fa-calculator me-2 text-success"></i>RB & KTR
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Murid MBA</small>
                                <span class="fw-bold text-success fs-6">{{ $profile->jumlah_murid_mba ?? 0 }}</span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Murid ENG</small>
                                <span class="fw-bold text-success fs-6">{{ $profile->jumlah_murid_eng ?? 0 }}</span>
                            </div>
                        </div>

                        @if(auth()->user()->is_admin ?? false)
                            <!-- Admin: Edit RB -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1 d-block">RB</label>
                                <select class="inline-rb-select form-select form-select-sm rounded-pill w-100" data-id="{{ $profile->id }}">
                                    <option value="">Pilih RB...</option>
                                    @foreach($rbOptions as $rb)
                                        <option value="{{ $rb }}" {{ $profile->rb == $rb ? 'selected' : '' }}>{{ $rb }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="mb-2">
                                <small class="text-muted d-block">RB</small>
                                <span class="fw-bold text-primary fs-6">{{ $profile->rb ?? '-' }}</span>
                            </div>
                        @endif

                        @if(auth()->user()->is_admin ?? false)
                            <!-- Admin: Edit KTR -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small mb-1 d-block">KTR</label>
                                <select class="inline-ktr-select form-select form-select-sm rounded-pill w-100" data-id="{{ $profile->id }}">
                                    <option value="">Auto KTR</option>
                                    @foreach($ktrOptions as $ktr)
                                        <option value="{{ $ktr }}" {{ $profile->ktr_tambahan == $ktr ? 'selected' : '' }}>{{ $ktr }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div>
                                <small class="text-muted d-block">KTR</small>
                                <span class="fw-bold text-info fs-6">{{ $profile->ktr_tambahan ?? $profile->ktr ?? '-' }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- SERAGAM -->
                    <div class="mobile-section py-4 border-t border-gray-100">
                        <div class="mobile-title fw-bold mb-3 fs-6 border-start border-warning border-4 ps-3">
                            <i class="fas fa-tshirt me-2 text-warning"></i>Pendataan Seragam
                        </div>
                        <div class="row row-cols-3 g-2 mb-3">
                            @php
                                $seragam = [
                                    'Kaos K-H'     => 'kaos_kuning_hitam',
                                    'Kaos M-K-B'   => 'kaos_merah_kuning_biru',
                                    'Kemeja'       => 'kemeja_kuning_hitam',
                                    'Blazer Merah' => 'blazer_merah',
                                    'Blazer Biru'  => 'blazer_biru',
                                ];
                            @endphp
                            @foreach($seragam as $label => $field)
                                <div class="col text-center">
                                    <small class="text-muted d-block mb-1">{{ $label }}</small>
                                    @if(auth()->user()->is_admin ?? false)
                                        <input type="text" class="form-control form-control-sm text-center seragam-direct fw-bold py-1 rounded-pill" 
                                               value="{{ $profile->$field ?? '' }}" data-field="{{ $field }}" data-id="{{ $profile->id }}">
                                    @else
                                        <span class="fw-bold px-2 py-1 bg-light rounded-pill d-inline-block w-100">{{ $profile->$field ?? '-' }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- DATA MAGANG & BANK -->
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="mobile-section p-3 bg-light rounded-3">
                                <div class="fw-bold small text-muted mb-2">MAGANG</div>
                                <div class="small">
                                    <span class="text-muted">Mentor:</span> <span class="fw-medium">{{ $profile->mentor_magang ?? '-' }}</span><br>
                                    <span class="text-muted">Periode:</span> <span class="fw-medium">{{ $profile->periode ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mobile-section p-3 bg-light rounded-3">
                                <div class="fw-bold small text-muted mb-2">BANK</div>
                                <div class="small">
                                    <span class="text-muted">Bank:</span> <span class="fw-medium">{{ $profile->bank ?? '-' }}</span><br>
                                    <span class="text-muted">No Rek:</span> <span class="fw-medium">{{ $profile->no_rekening ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- AKSI -->
                    <div class="text-center mt-4 pt-3 border-t border-gray-100">
                        <div class="btn-group w-100" role="group">
                            <a href="{{ route('profiles.edit', $profile->id) }}" class="btn btn-warning btn-sm flex-fill me-1">Edit</a>
                            @if(auth()->user()->is_admin ?? false)
                                <form action="{{ route('profiles.destroy', $profile->id) }}" method="POST" class="d-inline me-1" style="flex:1;" onsubmit="return confirm('Yakin hapus {{ addslashes($profile->nama) }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Hapus</button>
                                </form>
                                <button class="btn btn-info btn-sm text-white histori-btn flex-fill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#historiModal"
                                        data-id="{{ $profile->id }}"
                                        data-nama="{{ addslashes($profile->nama) }}">
                                    <i class="fas fa-history"></i> Histori
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-8 my-4">
                <i class="fas fa-users fa-3x text-muted mb-3 opacity-50"></i>
                <p class="text-muted fw-medium fs-5 mb-0">Belum ada data relawan</p>
                <a href="{{ route('profiles.create') }}" class="btn btn-primary mt-3">Tambah Relawan Baru</a>
            </div>
        @endforelse

    </div>
</div>
<!-- MODAL INFO RELAWAN - VERSI CANTIK & RAPI -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            
            <!-- Header -->
            <div class="modal-header border-0 bg-gradient-primary text-white py-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-user-circle fa-2x me-3"></i>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="infoModalLabel">Detail Relawan</h5>
                        <small id="modal-nama-header" class="opacity-90"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                <!-- Informasi Utama -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Nama Lengkap</small>
                                <span id="modal-nama" class="fw-bold fs-5 text-dark"></span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">NIK</small>
                                <span id="modal-nik" class="fw-medium"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jabatan & Status -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted">Jabatan</small>
                            <div id="modal-jabatan" class="fw-semibold mt-1"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted">Status</small>
                            <div id="modal-status" class="fw-semibold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted">Tanggal Keluar</small>
                            <div id="modal-tgl-keluar" class="fw-semibold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted">Keterangan Keluar</small>
                            <div id="modal-keterangan-keluar" class="fw-semibold mt-1"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="bg-light rounded-3 p-3 h-100">
                            <small class="text-muted">Departemen</small>
                            <div id="modal-departemen" class="fw-semibold mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Unit & Cabang (hanya untuk admin) -->
                @if (auth()->check() && (auth()->user()->is_admin ?? false))
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Unit biMBA</small>
                            <div id="modal-unit" class="fw-semibold mt-1"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">No Cabang</small>
                            <div id="modal-cabang" class="fw-semibold mt-1"></div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Tanggal Masuk Awal</small>
                            <div id="modal-tgl-masuk" class="fw-semibold mt-1"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Tanggal Selesai Magang</small>
                            <div id="modal-tgl_selesai_magang" class="fw-semibold mt-1 text-success"></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Masa Kerja</small>
                            <div id="modal-masa-kerja" class="fw-bold text-primary mt-1"></div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Data Pribadi -->
                <h6 class="fw-bold text-muted mb-3">DATA PRIBADI</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted">Tempat Lahir</small>
                        <div id="modal-tempat-lahir" class="fw-medium"></div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Tanggal Lahir</small>
                        <div id="modal-tgl-lahir" class="fw-medium"></div>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted">Usia</small>
                        <div id="modal-usia" class="fw-medium"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">No Telepon</small>
                        <div id="modal-telp" class="fw-medium"></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Email</small>
                        <div id="modal-email" class="fw-medium"></div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Mutasi & Bank -->
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted mb-3">MUTASI JABATAN</h6>
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">Jenis Mutasi</small>
                            <div id="modal-jenis-mutasi" class="fw-medium"></div>
                            <small class="text-muted mt-3 d-block">Tanggal Mutasi</small>
                            <div id="modal-tgl-mutasi" class="fw-medium"></div>
                            <small class="text-muted mt-3 d-block">Masa Kerja Jabatan</small>
                            <div id="modal-masa-jabatan" class="fw-medium"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="fw-bold text-muted mb-3">DATA BANK</h6>
                        <div class="bg-light rounded-3 p-3">
                            <small class="text-muted">No Rekening</small>
                            <div id="modal-no-rek" class="fw-medium"></div>
                            <small class="text-muted mt-3 d-block">Bank</small>
                            <div id="modal-bank" class="fw-medium"></div>
                            <small class="text-muted mt-3 d-block">Atas Nama</small>
                            <div id="modal-atas-nama" class="fw-medium"></div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    <!-- ==================== MODAL HISTORI ==================== -->
    <div class="modal fade" id="historiModal" tabindex="-1" aria-labelledby="historiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="historiModalLabel">
                        <i class="fas fa-history me-2"></i> Riwayat Perubahan - 
                        <span id="histori-nama"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="historiTable">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center">Periode</th>
                                    <th>Status</th>
                                    <th class="text-center">Jml Murid</th>
                                    <th>RB</th>
                                    <th>KTR</th>
                                    <th class="text-end">RP</th>
                                    <th class="text-center">Tgl Keluar</th>           <!-- BARU -->
                                    <th>Keterangan Keluar</th>                        <!-- BARU -->
                                    <th class="text-center">Diubah Oleh</th>
                                    <th class="text-center">Tanggal Ubah</th>
                                </tr>
                            </thead>
                            <tbody id="historiBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Inisialisasi Select2 untuk filter
    $('#searchProfile, #unitFilter, #searchProfileDesktop, #unitFilterDesktop').select2({
        width: '100%',
        placeholder: "Pilih...",
        allowClear: true
    });

    // Auto submit filter saat berubah
    $('#searchProfile, #unitFilter, #searchProfileDesktop, #unitFilterDesktop').on('change', function () {
        $(this).closest('form').submit();
    });

    // Seragam direct input
    $('.seragam-direct').each(function() {
        if ($(this).prop('readonly')) return;

        let timer;
        const input = this;
        const send = () => {
            const id = $(input).closest('[data-id]').data('id');
            const field = input.dataset.field;
            let ukuran = input.value.trim().toUpperCase();

            if (ukuran && !['S','M','L','XL','XXL'].includes(ukuran)) {
                input.value = input.dataset.last || '';
                return;
            }

            input.dataset.last = ukuran;

            fetch(`/profiles/${id}/seragam-kolom`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ field, ukuran: ukuran || null })
            })
            .then(r => r.json())
            .then(() => {
                $(input).css('border-color', ukuran ? '#28a745' : '#ced4da');
            })
            .catch(() => {
                $(input).css('border-color', '#dc3545');
            });
        };

        $(input).on('blur', () => {
            clearTimeout(timer);
            timer = setTimeout(send, 300);
        }).on('keypress', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(input).blur();
            }
        });
    });

    // Inline edit umum (select, date, textarea)
    $('.inline-edit').on('change blur', function(e) {
        const $this = $(this);
        const id = $this.data('id');
        const field = $this.data('field');
        let value = $this.val();

        if ($this.is('textarea') || $this.is('input[type="date"]')) {
            clearTimeout($this.data('timer'));
            $this.data('timer', setTimeout(() => sendUpdate(id, field, value, $this), 500));
        } else {
            sendUpdate(id, field, value, $this);
        }

        function sendUpdate(id, field, value, $element) {
            $element.prop('disabled', true).addClass('opacity-50');

            fetch(`/profiles/${id}/inline-update-field`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ field, value: value === '' ? null : value })
            })
            .then(r => {
                if (!r.ok) throw new Error('Gagal menyimpan');
                return r.json();
            })
            .then(data => {
                $element.prop('disabled', false).removeClass('opacity-50');

                if (data.status_karyawan) {
                    $(`td.status-karyawan[data-id="${id}"]`).text(data.status_karyawan);
                    if (data.status_karyawan === 'Aktif') {
                        $(`td.status-karyawan[data-id="${id}"]`).html('<span class="badge bg-success">Aktif</span>');
                    } else if (data.status_karyawan === 'Magang') {
                        $(`td.status-karyawan[data-id="${id}"]`).html('<span class="badge bg-warning text-dark">Magang</span>');
                    }
                }

                if (data.masa_kerja_text) {
                    $(`td:has(#masa_kerja)`).text(data.masa_kerja_text);
                }

                if (data.formatted_date) {
                    $element.next('small').text(data.formatted_date);
                }

                $element.css('border-color', '#28a745');
                setTimeout(() => $element.css('border-color', ''), 1500);
            })
            .catch(err => {
                console.error(err);
                $element.prop('disabled', false).removeClass('opacity-50');
                $element.css('border-color', '#dc3545');
                alert('Gagal menyimpan perubahan. Coba lagi.');
            });
        }
    });

    // RB Dropdown
    $('.inline-rb-select').on('change', function () {
        const $this = $(this);
        const id = $this.data('id');
        let value = $this.val();

        if (value === '' || value === 'auto') {
            value = 'auto';
        }
        const $saving = $this.parent().find('.saving-rb');

        $saving.show();

        fetch(`/profiles/${id}/inline-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ rb: value })
        })
        .then(r => r.json())
        .then(data => {
            $saving.hide();
            if (data.status === 'ok') {
                const p = data.profile;
                $this.val(p.rb ?? '');

                const $row = $this.closest('tr');
                $row.find('.kolom-rb-calc').text(p.rb_tambahan ?? '-');
                $row.find('.kolom-ktr-tambahan').text(p.ktr_tambahan ?? '-');
                $row.find('.kolom-rp').text(p.rp ? new Intl.NumberFormat('id-ID').format(p.rp) : '-');
                $row.find('.kolom-rombim').text(p.jumlah_rombim ?? '-');

                const $ktrSelect = $row.find('.inline-ktr-select');
                if ($ktrSelect.length && p.ktr) $ktrSelect.val(p.ktr);

                const $jdwl = $row.find('td:nth-child(14)');
                if ($jdwl.length && p.jumlah_murid_jadwal !== undefined) {
                    $jdwl.text(p.jumlah_murid_jadwal 
                        ? `${p.jumlah_murid_jadwal}${p.rb ? ' (' + p.rb + ')' : ''}` 
                        : '-');
                }
            }
        })
        .catch(() => {
            $saving.hide();
            alert('Gagal menyimpan RB.');
        });
    });

    // KTR Dropdown
    $('.inline-ktr-select').on('change', function () {
        const $this = $(this);
        const id = $this.data('id');
        const value = $this.val() || null;
        const $saving = $this.parent().find('.saving-ktr');

        $saving.show();

        fetch(`/profiles/${id}/inline-update-ktr`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ktr: value })
        })
        .then(r => r.json())
        .then(data => {
            $saving.hide();
            if (data.status === 'ok') {
                const p = data.profile;
                $this.val(p.ktr ?? p.ktr_tambahan ?? '');

                const $row = $this.closest('tr');
                $row.find('.kolom-rb-calc').text(p.rb_tambahan ?? '-');
                $row.find('.kolom-ktr-tambahan').text(p.ktr_tambahan ?? '-');
                $row.find('.kolom-rp').html(
                    p.rp 
                        ? `<span style="color:#198754">Rp ${new Intl.NumberFormat('id-ID').format(p.rp)}</span>`
                        : '-'
                );

                $row.find('td:nth-child(17)').text(p.ktr ?? p.ktr_tambahan ?? '-');

                $row.find('.kolom-rombim').text(p.jumlah_rombim ?? '-');
                const $jdwl = $row.find('td:nth-child(14)');
                if ($jdwl.length && p.jumlah_murid_jadwal !== undefined) {
                    $jdwl.text(p.jumlah_murid_jadwal 
                        ? `${p.jumlah_murid_jadwal}${p.rb ? ' (' + p.rb + ')' : ''}` 
                        : '-');
                }
            } else {
                alert('Gagal update KTR: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            $saving.hide();
            alert('Gagal menyimpan KTR. Cek koneksi.');
        });
    });
});
// Modal Info Relawan
$('#infoModal').on('show.bs.modal', function (event) {
    const button = $(event.relatedTarget);
    
    $('#modal-nama').text(button.data('nama'));
    $('#modal-nama-header').text(button.data('nama'));   // untuk header
    $('#modal-nik').text(button.data('nik'));
    $('#modal-jabatan').text(button.data('jabatan'));
    $('#modal-status')
    .text(button.data('status'))
    .removeClass('bg-success bg-danger bg-dark bg-warning bg-secondary text-white text-dark')
    .addClass(button.data('status-class'));
    $('#modal-tgl-keluar').text(button.data('tgl-keluar'));
    $('#modal-keterangan-keluar').text(button.data('keterangan-keluar'));
    $('#modal-departemen').text(button.data('departemen'));
    $('#modal-unit').text(button.data('unit'));
    $('#modal-cabang').text(button.data('cabang'));
    $('#modal-tgl-masuk').text(button.data('tgl-masuk'));
    $('#modal-tgl_selesai_magang').text(button.data('tgl_selesai_magang'));
    $('#modal-masa-kerja').text(button.data('masa-kerja'));
    $('#modal-tempat-lahir').text(button.data('tempat-lahir'));
    $('#modal-tgl-lahir').text(button.data('tgl-lahir'));
    $('#modal-usia').text(button.data('usia'));
    $('#modal-telp').text(button.data('telp'));
    $('#modal-email').text(button.data('email'));
    $('#modal-jenis-mutasi').text(button.data('jenis-mutasi'));
    $('#modal-tgl-mutasi').text(button.data('tgl-mutasi'));
    $('#modal-masa-jabatan').text(button.data('masa-jabatan'));
    $('#modal-no-rek').text(button.data('no-rek'));
    $('#modal-bank').text(button.data('bank'));
    $('#modal-atas-nama').text(button.data('atas-nama'));
});

// Toggle detail mobile card
window.toggleDetail = function(id) {
    const $content = $(`#detail-${id}`);
    const $toggle = $(`.detail-toggle[data-id="${id}"]`);
    
    if ($content.is(':visible')) {
        $content.slideUp(300);
        $toggle.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        $(`.relawan-card-item[data-id="${id}"] .card-header`).css('border-radius', '1rem');
    } else {
        $content.slideDown(400);
        $toggle.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        $(`.relawan-card-item[data-id="${id}"] .card-header`).css('border-radius', '1rem 1rem 0 0');
    }
};

// Close all other details when one is opened (optional)
$(document).on('click', '.card-header', function() {
    const id = $(this).closest('.relawan-card-item').data('id');
    // Close others
    $('.detail-content').not(`#detail-${id}`).slideUp(200);
    $('.detail-toggle').not(`[data-id="${id}"]`).removeClass('fa-chevron-up').addClass('fa-chevron-down');
});
    // ==================== SCRIPT HISTORI ====================
$(document).on('click', '.histori-btn', function () {
    const id = $(this).data('id');
    const nama = $(this).data('nama');

    $('#histori-nama').text(nama);
    $('#historiBody').html('<tr><td colspan="10" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Memuat histori...</td></tr>');

    fetch(`/profiles/${id}/histori`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            let html = '';
            if (data.length === 0) {
                html = `<tr><td colspan="10" class="text-center py-4 text-muted">Belum ada riwayat perubahan.</td></tr>`;
            } else {
                data.forEach(item => {
                    const tglKeluarFormatted = item.tgl_keluar 
                        ? new Date(item.tgl_keluar).toLocaleDateString('id-ID') 
                        : '-';

                    html += `
                        <tr>
                            <td class="text-center fw-bold">${item.periode ?? '-'}</td>
                            <td>
                                <span class="badge ${item.status_karyawan === 'Aktif' ? 'bg-success' : 'bg-danger text-white'}">
                                    ${item.status_karyawan ?? '-'}
                                </span>
                            </td>
                            <td class="text-center">${item.jumlah_murid_jadwal ?? 0}</td>
                            <td class="text-center">${item.rb ?? '-'}</td>
                            <td class="text-center">${item.ktr ?? item.ktr_tambahan ?? '-'}</td>
                            <td class="text-end fw-bold text-success">
                                ${item.rp ? 'Rp ' + new Intl.NumberFormat('id-ID').format(item.rp) : '-'}
                            </td>
                            <td class="text-center">${tglKeluarFormatted}</td>
                            <td class="text-wrap" style="max-width: 200px;">${item.keterangan_keluar ?? '-'}</td>
                            <td class="text-center">${item.changed_by ?? 'Sistem'}</td>
                            <td class="text-center small">${item.created_at ? new Date(item.created_at).toLocaleString('id-ID') : '-'}</td>
                        </tr>`;
                });
            }
            $('#historiBody').html(html);
        })
        .catch(err => {
            console.error(err);
            $('#historiBody').html(`
                <tr>
                    <td colspan="10" class="text-center text-danger py-4">
                        Gagal memuat histori.<br>
                        <small class="text-muted">Cek console (F12) atau hubungi admin</small>
                    </td>
                </tr>`);
        });
});
</script>
@endpush
@endsection