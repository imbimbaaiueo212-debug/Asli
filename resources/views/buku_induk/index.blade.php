@extends('layouts.app')
@section('title', 'Buku Induk')
@section('content')
    <div class="container-fluid px-1 px-md-3">
        <div class="card card-header shadow-sm border-0">
            <div class="card-body px-2 px-md-4 py-4">
                <h2 class="mb-4">Data Buku Induk</h2>

                <div class="d-flex flex-wrap gap-2 mb-4">

    @if(auth()->check() && auth()->user()->role == 'admin')
        <a href="{{ route('buku_induk.create') }}" class="btn btn-primary">
            + Tambah Data
        </a>
    

    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
        Unggah Data Excel
    </button>

    <a href="{{ route('buku_induk.export') . '?' . http_build_query(request()->query()) }}"
        class="btn btn-info">
        <i class="fas fa-file-excel"></i> Unduh Data Excel
    </a>
@endif
</div>

                {{-- MODAL IMPORT --}}
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Import Data Buku Induk</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="{{ route('buku_induk.import') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="file" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                                        <input type="file" name="file" id="file" class="form-control"
                                            accept=".xlsx,.xls,.csv" required>
                                        @error('file')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <p class="text-muted small">Pastikan file mengikuti format template Buku Induk.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Import</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- FILTER FORM --}}
                {{-- FILTER FORM --}}
                <form method="GET" action="{{ route('buku_induk.index') }}" id="filterForm"
                      class="card card-body shadow-sm border-0 rounded-3 mb-4">

                    <div class="row g-3 align-items-end">
                        @if (auth()->check() && (auth()->user()->is_admin ?? false))
                            <div class="col-12 col-md-3">
                                <label for="unitFilter" class="form-label fw-bold">Unit biMBA</label>
                                <select name="unit" id="unitFilter" class="form-select">
                                    <option value="">— Semua Unit —</option>
                                    @foreach ($unitOptions as $unit)
                                        <option value="{{ $unit }}" {{ request('unit') == $unit ? 'selected' : '' }}>
                                            {{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-md-{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '4' : '5' }}">
                            <label for="muridFilter" class="form-label fw-bold">Filter NIM / Nama</label>
                            <select name="murid" id="muridFilter" class="form-select">
                                <option value="">— Ketik untuk cari NIM atau Nama —</option>
                                @foreach ($muridOptions as $o)
                                    @php $nimPad = str_pad($o->nim, 3, '0', STR_PAD_LEFT); @endphp
                                    <option value="{{ $o->nim }}" {{ request('murid') == $o->nim ? 'selected' : '' }}>
                                        {{ $nimPad }} | {{ $o->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <label for="statusFilter" class="form-label fw-bold">Status</label>
                            <select name="status" id="statusFilter" class="form-select">
                                <option value="">— Semua —</option>
                                <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Baru" {{ request('status') == 'Baru' ? 'selected' : '' }}>Baru</option>
                                <option value="Keluar" {{ request('status') == 'Keluar' ? 'selected' : '' }}>Keluar</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <label for="perPageFilter" class="form-label fw-bold">Per halaman</label>
                            <select name="perPage" id="perPageFilter" class="form-select">
                                @foreach ([25, 50, 100, 200] as $pp)
                                    <option value="{{ $pp }}" {{ request('perPage', 50) == $pp ? 'selected' : '' }}>
                                        {{ $pp }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-1 d-flex align-items-end">
                            <a href="{{ route('buku_induk.index') }}" class="btn btn-outline-secondary w-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- TABEL DATA --}}
                <div class="table-sticky-wrapper table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="bukuIndukTable">
                        <thead class="tabel card-body">
                            <tr>
                                <th>NIM</th>
                                <th>NAMA</th>
                                <th>INFO</th>
                                <th>STATUS</th>
                                <th>KETERANGAN</th>
                                <th>PERIODE / GARANSI</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        @forelse ($bukuInduk as $index => $item)
                            <tr 
                            @class([
    'table-danger'  => $item->tgl_keluar && strtolower($item->status ?? '') === 'keluar',

    'table-primary' => strtolower($item->status ?? '') === 'baru',

    'table-warning' => strtolower($item->status ?? '') === 'cuti',

    'card-body',
])>

                                <!-- NIM -->
                                <td>{{ $item->nim ?? '-' }}</td>
                                <td>{{ $item->nama }}</td>
                                <!-- INFO -->
                                <td>
                                    Unit: {{ $item->bimba_unit }}<br>
                                    Tgl Daftar: {{ $item->tgl_daftar?->format('d-m-Y') }}<br>
                                    Tahap: {{ $item->tahap ?? '-' }}<br>
                                    Gol: {{ $item->gol ?? '-' }} | KD: {{ $item->kd ?? '-' }}<br>
                                    SPP: Rp {{ number_format((int) str_replace('.', '', $item->spp ?? '0'), 0, ',', '.') }}<br>
                                    Bulan: {{ $item->info_jadwal['bulan_tampil'] }}<br>
                                    Jadwal: {{ $item->kode_jadwal ? substr($item->kode_jadwal, 1) . ':00' : '-' }}<br>
                                    Guru: {{ $item->guru ?? '-' }}<br>
                                    <!-- misalnya di kolom INFO atau kolom Jadwal -->
                                    Hari: 
                                    @php
                                        $shift = '-';
                                        $hariList = [];

                                        $kode = (int) ($item->kode_jadwal ?? 0);

                                        if ($kode >= 108 && $kode <= 116) {
                                            $shift = 'SRJ';
                                            $hariList = ['Senin', 'Rabu', 'Jumat'];
                                        } elseif ($kode >= 208 && $kode <= 211) {
                                            $shift = 'SKS';
                                            $hariList = ['Selasa', 'Kamis', 'Sabtu'];
                                        } elseif ($kode >= 308 && $kode <= 311) {
                                            $shift = 'S6';
                                            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        }
                                    @endphp

                                    @if ($shift !== '-')
                                        <strong>{{ $shift }}</strong> ({{ implode(' | ', $hariList) }})
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $item->status }}</td>

                                <!-- KETERANGAN -->
                                <!-- KETERANGAN -->
<td>
    @if (strtolower($item->status ?? '') === 'keluar')
        @if ($item->tgl_keluar)
            <strong>Tanggal Aktif:</strong> {{ $item->tgl_masuk ? \Carbon\Carbon::parse($item->tgl_masuk)->format('d/m/Y') : '-' }}
            <br>
            <strong class="text-danger">Tanggal Keluar:</strong> {{ \Carbon\Carbon::parse($item->tgl_keluar)->format('d/m/Y') }}
            
            @if ($item->kategori_keluar || $item->alasan || $item->keterangan)
                <br><br>
                <small><strong>Kategori:</strong> <span class="text-danger">{{ trim($item->kategori_keluar ?? '-') }}</span></small>
                <br>
                <small><strong>Alasan:</strong> <span class="text-danger">{{ trim($item->alasan ?? $item->keterangan ?? '-') }}</span></small>
            @endif
        @else
            <span class="text-muted">-</span>
        @endif

    @else
        {{-- Untuk status selain Keluar --}}
        @if ($item->tgl_masuk)
            <strong>Tanggal Mulai KBM:</strong> {{ \Carbon\Carbon::parse($item->tgl_masuk)->format('d/m/Y') }}
        @else
            <span class="text-muted">belum ada tgl masuk</span>
        @endif
    @endif
</td>

                                <!-- PERIODE / GARANSI -->
                                <td>
                                    {{ $item->periode ?? '-' }}

                                    {{-- PERIODE BELAJAR --}}
                                    @if ($item->tgl_mulai && $item->tgl_akhir)
                                        <br>
                                        <small>
                                            ({{ \Carbon\Carbon::parse($item->tgl_mulai)->format('d-m-Y') }}
                                            →
                                            {{ \Carbon\Carbon::parse($item->tgl_akhir)->format('d-m-Y') }})
                                        </small>
                                    @endif

                                {{-- ✅ GARANSI --}}
                                @if ($item->tgl_surat_garansi)

                                    <hr class="my-1">

                                    <small class="text-primary fw-bold">
                                        📌 GARANSI 372
                                    </small>

                                    <br>
                                    <small>
                                        Diberikan:
                                        {{ \Carbon\Carbon::parse($item->tgl_surat_garansi)->format('d-m-Y') }}
                                    </small>

                                    {{-- ✅ HANYA TAMPIL JIKA SUDAH DIAJUKAN --}}
                                    @if ($item->tgl_pengajuan_garansi && $item->perpanjang_garansi !== 'Diberikan')
                                        <br>
                                        <small>
                                            Pengajuan:
                                            {{ \Carbon\Carbon::parse($item->tgl_pengajuan_garansi)->format('d-m-Y') }}
                                        </small>
                                    @endif

                                    {{-- ✅ HANYA TAMPIL JIKA ADA --}}
                                    @if ($item->tgl_selesai_garansi && $item->perpanjang_garansi !== 'Diberikan')
                                        <br>
                                        <small>
                                            Selesai:
                                            {{ \Carbon\Carbon::parse($item->tgl_selesai_garansi)->format('d-m-Y') }}
                                        </small>
                                    @endif

                                    {{-- ✅ MASA AKTIF --}}
                                    @if ($item->masa_aktif_garansi && $item->perpanjang_garansi !== 'Diberikan')
                                        <br>
                                        <small>
                                            Masa Aktif:
                                            {{ $item->masa_aktif_garansi }} bulan
                                        </small>
                                    @endif

                                    <br>
                                    <small>
                                        Status:
                                        @if($item->perpanjang_garansi == 'Aktif')
                                            <span class="badge bg-success">Aktif</span>

                                        @elseif($item->perpanjang_garansi == 'Diberikan')
                                            <span class="badge bg-primary">Sudah Diberikan</span>

                                        @elseif(str_contains($item->perpanjang_garansi ?? '', 'Segera'))
                                            <span class="badge bg-warning text-dark">
                                                {{ $item->perpanjang_garansi }}
                                            </span>

                                        @elseif($item->perpanjang_garansi == 'Habis')
                                            <span class="badge bg-danger">Habis</span>

                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </small>

                                    @if ($item->note_garansi)
                                        <br>
                                        <small class="text-muted">
                                            {{ $item->note_garansi }}
                                        </small>
                                    @endif

                                @endif
                                    
                                </td>

                                <!-- Aksi -->
<td class="text-center" style="min-width: 140px;">
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" 
                type="button" data-bs-toggle="dropdown">
            Aksi
        </button>

        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <a class="dropdown-item" href="{{ route('buku_induk.edit', $item->id) }}">
                    ✏️ Edit Lengkap
                </a>
            </li>
            
            <!-- EDIT STATUS -->
            <li>
                <button class="dropdown-item text-info" type="button" 
                        data-bs-toggle="modal" data-bs-target="#statusModal{{ $item->id }}">
                    📝 Update Status Murid
                </button>
            </li>

            <li>
                <button class="dropdown-item" type="button" 
                        data-bs-toggle="modal" data-bs-target="#detailModal{{ $item->id }}">
                    🔍 Detail
                </button>
            </li>

            @if (auth()->user()?->role === 'admin')
                <li>
                    <a class="dropdown-item" href="{{ route('buku_induk.history', $item->id) }}">
                        🕒 Riwayat
                    </a>
                </li>
            @endif

            <li><hr class="dropdown-divider"></li>

            <!-- Surat Pindah -->
            <li>
                <a class="dropdown-item text-success" 
                   href="{{ route('buku_induk.surat_pindah', $item->id) }}" 
                   target="_blank">
                    📄 Surat Pindah
                </a>
            </li>

            @if (auth()->user()?->role === 'admin')
                <li><hr class="dropdown-divider"></li>
                
                <li>
                    <form action="{{ route('buku_induk.destroy', $item->id) }}" 
                          method="POST" onsubmit="return confirm('Yakin hapus data murid ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger">
                            🗑️ Hapus
                        </button>
                    </form>
                </li>
            @endif
        </ul>
    </div>
</td>

                            </tr>
                            {{-- MODAL DETAIL – FULL FIELD --}}
                            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1"
                                aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title" id="detailModalLabel{{ $item->id }}">
                                                Detail Murid: {{ $item->nama }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                        {{-- =========================
                                            📌 IDENTITAS MURID
                                        ========================= --}}
                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-primary mb-3">📌 Identitas Murid</h6>
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4">NIM</dt>
                                                <dd class="col-sm-8">: {{ $item->nim }}</dd>

                                                <dt class="col-sm-4">Nama</dt>
                                                <dd class="col-sm-8">: {{ $item->nama }}</dd>

                                                <dt class="col-sm-4">Unit / Cabang</dt>
                                                <dd class="col-sm-8">
                                                    : {{ $item->no_cabang ?? '-' }} | {{ $item->bimba_unit ?? '-' }}
                                                    @if($item->no_cabang)
                                                        
                                                    @endif
                                                </dd>
                                                <dt class="col-sm-4">Tempat Lahir</dt>
                                                <dd class="col-sm-8">: {{ $item->tmpt_lahir }}</dd>

                                                <dt class="col-sm-4">Tanggal Lahir</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_lahir_formatted }}</dd>

                                                <dt class="col-sm-4">Usia</dt>
                                                @php
                                                    $usiaText = '-';
                                                    if ($item->tgl_lahir) {
                                                        $diff = \Carbon\Carbon::parse($item->tgl_lahir)->diff(now());
                                                        $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
                                                    }
                                                @endphp
                                                <dd class="col-sm-8">: {{ $usiaText }}</dd>
                                                 <dt class="col-sm-4">Tempat Lahir</dt>
                                                <dd class="col-sm-8">: {{ $item->tmpt_lahir }}</dd>
                                                <dt class="col-sm-4">Usia</dt>
                                                @php
                                                    $usiaText = '-';
                                                    if ($item->tgl_lahir) {
                                                        $diff = \Carbon\Carbon::parse($item->tgl_lahir)->diff(now());
                                                        $usiaText = $diff->y . ' tahun ' . $diff->m . ' bulan';
                                                    }
                                                @endphp
                                                <dd class="col-sm-8">: {{ $usiaText }}</dd>
                                                <dt class="col-sm-4">Tanggal Daftar</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_daftar?->format('d-m-Y') }}</dd>

                                                <dt class="col-sm-4">Tanggal Masuk</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_masuk_formatted }}</dd>

                                                <dt class="col-sm-4">Lama Belajar</dt>
                                                <dd class="col-sm-8">: {{ $item->lama_bljr }}</dd>

                                                <dt class="col-sm-4">Tahap</dt>
                                                <dd class="col-sm-8">: {{ $item->tahap }}</dd>

                                                <dt class="col-sm-4">Kelas</dt>
                                                <dd class="col-sm-8">: {{ $item->kelas }}</dd>

                                                <dt class="col-sm-4">Golongan</dt>
                                                <dd class="col-sm-8">: {{ $item->gol }} | {{ $item->kd }}</dd>
                                                 <dt class="col-sm-4">Jenis KBM</dt>
                                                <dd class="col-sm-8">: {{ $item->jenis_kbm }}</dd>
                                                <dt class="col-sm-4">Level</dt>
                                                <dd class="col-sm-8">: {{ $item->level }}</dd>
                                                <dt class="col-sm-4">Keterangan Level</dt>
                                                <dd class="col-sm-8">: {{ $item->keterangan_level }}</dd>
                                                <dt class="col-sm-4">Tanggal Perubahan Level</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_level_formatted }}</dd>
                                                <dt class="col-sm-4">Bulan Aktif</dt>
                                                <dd class="col-sm-8">
                                                    :
                                                    @if($item->info_jadwal['status'] === 'ok')
                                                        <span class="text-primary fw-semibold">
                                                            {{ $item->info_jadwal['bulan_tampil'] }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                                <dt class="col-sm-4">Orangtua</dt>
                                                <dd class="col-sm-8">: {{ $item->orangtua }}</dd>

                                                <dt class="col-sm-4">No HP</dt>
                                                <dd class="col-sm-8">: {{ $item->no_telp_hp }}</dd>

                                                <dt class="col-sm-4">Alamat</dt>
                                                <dd class="col-sm-8">: {{ $item->alamat_murid }}</dd>
                                                <dt class="col-sm-4">SPP</dt>
                                                <dd class="col-sm-8">: Rp {{ number_format((int) str_replace('.', '', $item->spp), 0, ',', '.') }}</dd>

                                                <dt class="col-sm-4">No Pembayaran</dt>
                                                <dd class="col-sm-8">: {{ $item->no_pembayaran_murid }}</dd>
                                                <dt class="col-sm-4">Status</dt>
                                                <dd class="col-sm-8">: {{ $item->status }}</dd>

                                                <dt class="col-sm-4">Kategori Keluar</dt>
                                                <dd class="col-sm-8">: {{ $item->kategori_keluar }}</dd>

                                                <dt class="col-sm-4">Alasan</dt>
                                                <dd class="col-sm-8">: {{ $item->alasan }}</dd>

                                                <dt class="col-sm-4">Tanggal Keluar</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_keluar }}</dd>
                                                <dt class="col-sm-4">Keterangan Optional</dt>
                                                <dd class="col-sm-8">: {{ $item->keterangan_optional }}</dd>

                                                <dt class="col-sm-4">No Cabang Merge</dt>
                                                <dd class="col-sm-8">: {{ $item->no_cab_merge }}</dd>

                                                

                                                <dt class="col-sm-4">Petugas Trial</dt>
                                                <dd class="col-sm-8">: {{ $item->petugas_trial }}</dd>

                                                <dt class="col-sm-4">Guru</dt>
                                                <dd class="col-sm-8">: {{ $item->guru }}</dd>
                                            </dl>
                                        </div>

                                        {{-- =========================
                                            📊 JADWAL & AKTIVITAS
                                        ========================= --}}
                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-info mb-3">📊 Jadwal biMBA</h6>
                                            <dl class="row mb-0">

                                                <dt class="col-sm-4">Kode Jadwal</dt>
                                                <dd class="col-sm-8">: {{ $item->kode_jadwal }}</dd>
                                                <dt class="col-sm-4">Hari & Jam</dt>
                                                <dd class="col-sm-8">
                                                    :
                                                    @php
                                                        $shift = '-';
                                                        $hariList = [];
                                                        $jam = '-';

                                                        $kode = (int) ($item->kode_jadwal ?? 0);

                                                        // =====================
                                                        // SHIFT + HARI
                                                        // =====================
                                                        if ($kode >= 108 && $kode <= 116) {
                                                            $shift = 'SRJ';
                                                            $hariList = ['Senin', 'Rabu', 'Jumat'];
                                                        } elseif ($kode >= 208 && $kode <= 211) {
                                                            $shift = 'SKS';
                                                            $hariList = ['Selasa', 'Kamis', 'Sabtu'];
                                                        } elseif ($kode >= 308 && $kode <= 311) {
                                                            $shift = 'S6';
                                                            $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                                        }

                                                        // =====================
                                                        // JAM
                                                        // =====================
                                                        if ($kode > 0) {
                                                            $jamAngka = substr($kode, -2);
                                                            $jam = str_pad($jamAngka, 2, '0', STR_PAD_LEFT) . ':00';
                                                        }
                                                    @endphp

                                                    @if ($shift !== '-')
                                                        <strong>{{ $shift }}</strong>
                                                        <span class="text-muted">({{ implode(' | ', $hariList) }})</span>
                                                        -
                                                        <span class="fw-bold text-primary">{{ $jam }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </dd>
                                            </dl>
                                        </div>

                                        {{-- =========================
                                            📦 BNF dan DU'AFA
                                        ========================= --}}
                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-dark mb-3">📦 BNF dan DU'AFA</h6>
                                            <dl class="row mb-0">

                                                <dt class="col-sm-4">Periode</dt>
                                                <dd class="col-sm-8">: {{ $item->periode }}</dd>

                                                <dt class="col-sm-4">Tanggal Mulai</dt>
                                                <dd class="col-sm-8">
                                                    {{ $item->tgl_mulai ? \Carbon\Carbon::parse($item->tgl_mulai)->translatedFormat('d F Y') : '-' }}
                                                </dd>

                                                <dt class="col-sm-4">Tanggal Akhir</dt>
                                                <dd class="col-sm-8">
                                                    {{ $item->tgl_akhir ? \Carbon\Carbon::parse($item->tgl_akhir)->translatedFormat('d F Y') : '-' }}
                                                </dd>

                                                <dt class="col-sm-4">Alert</dt>
                                                <dd class="col-sm-8">: {{ $item->alert }}</dd>
                                            </dl>
                                        </div>
                                        
                                        {{-- =========================
                                            Paket 72
                                        ========================= --}}
                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-muted mb-3">📦 Paket 72
                                            </h6>
                                            <dl class="row mb-0">

                                                <dt class="col-sm-4">Tanggal Bayar</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_bayar }}</dd>

                                                <dt class="col-sm-4">Tanggal Selesai</dt>
                                                <dd class="col-sm-8">: {{ $item->tgl_selesai }}</dd>

                                                <dt class="col-sm-4">Alert 2</dt>
                                                <dd class="col-sm-8">: {{ $item->alert2 }}</dd>
                                            </dl>
                                        </div>
                                        {{-- =========================
                                            Supply Modul
                                            ========================= --}}
                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-muted mb-3">📦 Supply Modul
                                            </h6>
                                            <dl class="row mb-0">
                                    
                                                <dt class="col-sm-4">Asal Modul</dt>
                                                <dd class="col-sm-8">: {{ $item->asal_modul }}</dd>

                                                <dt class="col-sm-4">Note Garansi</dt>
                                                <dd class="col-sm-8">: {{ $item->note_garansi }}</dd>
                                            </dl>
                                        </div>

                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-muted mb-3">📌 MURID PINDAH KE INTERVIO (ONLINE)</h6>
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4">Status Pindah</dt>
                                                <dd class="col-sm-8">: {{ $item->status_pindah }}</dd>

                                                <dt class="col-sm-4">Tanggal Pindah</dt>
                                                <dd class="col-sm-8">: {{ $item->tanggal_pindah }}</dd>

                                                <dt class="col-sm-4">Ke Bimba Intervio</dt>
                                                <dd class="col-sm-8">: {{ $item->ke_bimba_intervio }}</dd>

                                                <dt class="col-sm-4">Keterangan</dt>
                                                <dd class="col-sm-8">: {{ $item->keterangan }}</dd>
                                            </dl>
                                        </div>

                                        {{-- =========================
                                            Surat Garansi BCA 372 Bebas
                                            ========================= --}}

                                        @if ($item->tgl_surat_garansi)

                                        <div class="border rounded p-3 mb-3">
                                            <h6 class="fw-bold text-muted mb-3">
                                                📌 SURAT GARANSI BCA 372 BEBAS
                                            </h6>

                                            <dl class="row mb-0">

                                                <dt class="col-sm-4">Tanggal Diberikan</dt>
                                                <dd class="col-sm-8">
                                                    : {{ \Carbon\Carbon::parse($item->tgl_surat_garansi)->format('d-m-Y') }}
                                                </dd>

                                                <dt class="col-sm-4">Tanggal Pengajuan</dt>
                                                <dd class="col-sm-8">
                                                    : {{ \Carbon\Carbon::parse($item->tgl_pengajuan_garansi)->format('d-m-Y') }}
                                                </dd>

                                                <dt class="col-sm-4">Berlaku Sampai</dt>
                                                <dd class="col-sm-8">
                                                    : {{ \Carbon\Carbon::parse($item->tgl_selesai_garansi)->format('d-m-Y') }}
                                                </dd>

                                                <dt class="col-sm-4">Masa Aktif</dt>
                                                <dd class="col-sm-8">
                                                    : {{ $item->masa_aktif_garansi }} bulan
                                                </dd>

                                                <dt class="col-sm-4">Status</dt>
                                                <dd class="col-sm-8">
                                                    :
                                                    @if($item->perpanjang_garansi == 'Aktif')
                                                        <span class="badge bg-success">Aktif</span>
                                                    @elseif(str_contains($item->perpanjang_garansi, 'Segera'))
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $item->perpanjang_garansi }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">Habis</span>
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">Note</dt>
                                                <dd class="col-sm-8">
                                                    : {{ $item->note_garansi ?? '-' }}
                                                </dd>

                                            </dl>
                                        </div>

                                        @endif

                                    </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                           {{-- ====================== MODAL UBAH STATUS ====================== --}}
                            <div class="modal fade" id="statusModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">Ubah Status - {{ $item->nama }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        
                                        <form id="statusForm{{ $item->id }}" action="{{ route('buku_induk.updateStatus', $item->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PATCH')
                                            
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    
                        <!-- PILIH STATUS -->
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Pilih Status Baru</label>
                                                    <select name="status" id="statusSelect{{ $item->id }}" class="form-select form-select-lg" required>
                                                        <option value="">-- Pilih Status --</option>
                                                        <option value="Aktif">Aktif Kembali</option>
                                                        <option value="Keluar">Keluar</option>
                                                        <option value="Pindah Golongan">Pindah Golongan</option>
                                                        <option value="Cuti">Cuti</option>
                                                    </select>
                                                </div>

                       <!-- FIELD KELUAR -->
                                                <div id="keluarFields{{ $item->id }}" class="status-fields col-12" style="display: none;">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Tanggal Keluar <span class="text-danger">*</span></label>
                                                            <input type="date" name="tgl_keluar" id="tgl_keluar{{ $item->id }}" 
                                                                class="form-control" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Kategori Keluar</label>
                                                            <select name="kategori_keluar" class="form-select">
                                                                <option value="">-- Pilih Kategori --</option>
                                                                @foreach($kategoriKeluarOptions as $kk)
                                                                    <option value="{{ $kk }}">{{ $kk }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Alasan Keluar</label>
                                                            <textarea name="alasan" class="form-control" rows="3" placeholder="Masukkan alasan..."></textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                       <!-- FIELD CUTI -->
<div id="cutiFields{{ $item->id }}" 
     class="status-fields col-12"
     style="display: none;">

    <div class="row g-3">

        <!-- Tanggal Mulai -->
        <div class="col-md-6">
            <label class="form-label">Tanggal Mulai Cuti</label>
            <input type="date"
                   name="data[{{ $item->id }}][tanggal_mulai]"
                   class="form-control"
                   value="{{ old('data.'.$item->id.'.tanggal_mulai', $item->tanggal_mulai) }}">
        </div>

        <!-- Jenis Cuti -->
        <div class="col-md-6">
            <label class="form-label">Jenis Cuti</label>
            <select name="data[{{ $item->id }}][jenis_cuti]" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="Sakit" 
                    {{ old('data.'.$item->id.'.jenis_cuti', $item->jenis_cuti) == 'Sakit' ? 'selected' : '' }}>
                    Sakit
                </option>
            </select>
        </div>

        <!-- Upload Surat (opsional saat mengakhiri cuti) -->
        <div class="col-md-6">
            <label class="form-label">Upload Surat Dokter (opsional)</label>
            <input type="file"
                   name="data[{{ $item->id }}][surat_dokter]"
                   class="form-control"
                   accept=".pdf,.jpg,.jpeg,.png">
        </div>

        <!-- Tanggal Selesai -->
        <div class="col-md-6">
            <label class="form-label text-danger">
                Tanggal Selesai Cuti <span class="text-danger">*</span>
            </label>
            <input type="date"
                   name="data[{{ $item->id }}][tgl_selesai_cuti]"
                   class="form-control"
                   value="{{ old('data.'.$item->id.'.tgl_selesai_cuti', $item->tgl_selesai_cuti) }}"
                   required>
        </div>

        <!-- Alasan -->
        <div class="col-12">
            <label class="form-label">Alasan Cuti</label>
            <textarea name="data[{{ $item->id }}][alasan_cuti]"
                      class="form-control"
                      rows="3">{{ old('data.'.$item->id.'.alasan_cuti', $item->alasan_cuti) }}</textarea>
        </div>

    </div>
</div>

                        <!-- FIELD PINDAH GOLONGAN -->
                <div id="pindahFields{{ $item->id }}" class="status-fields col-12" style="display: none;">
                    <div class="row g-3">
                        
                        <!-- Golongan Lama -->
                        <div class="col-md-6">
                            <label class="form-label">Golongan Lama</label>
                            <input type="text" class="form-control bg-light" value="{{ $item->gol ?? '-' }}" readonly>
                            <input type="hidden" name="gol_lama" value="{{ $item->gol }}">
                        </div>

                        <!-- Gol Baru -->
                        <div class="col-md-6">
                            <label class="form-label">Gol Baru <span class="text-danger">*</span></label>
                            <select name="gol_baru" id="gol_baru{{ $item->id }}" class="form-select" required>
                                <option value="">-- Pilih Gol Baru --</option>
                                @foreach($golOptions as $g)
                                    <option value="{{ $g->kode }}">{{ $g->kode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- KD Lama -->
                        <div class="col-md-6">
                            <label class="form-label">KD Lama</label>
                            <input type="text" class="form-control bg-light" value="{{ $item->kd ?? '-' }}" readonly>
                            <input type="hidden" name="kd_lama" value="{{ $item->kd }}">
                        </div>

                        <!-- KD Baru -->
                        <div class="col-md-6">
                            <label class="form-label">KD Baru <span class="text-danger">*</span></label>
                            <select name="kd_baru" id="kd_baru{{ $item->id }}" class="form-select" required>
                                <option value="">-- Pilih KD Baru --</option>
                                @foreach($kdOptions as $k)
                                    <option value="{{ $k }}">{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- SPP Lama -->
                        <div class="col-md-6">
                            <label class="form-label">SPP Lama</label>
                            <input type="text" class="form-control bg-light" 
                                value="Rp {{ number_format($item->spp ?? 0) }}" readonly>
                            <input type="hidden" name="spp_lama" value="{{ $item->spp }}">
                        </div>

                        <!-- SPP Baru -->
                        <div class="col-md-6">
                            <label class="form-label">SPP Baru <span class="text-danger">*</span></label>
                            <input type="number" name="spp_baru" id="spp_baru{{ $item->id }}" 
                                class="form-control" min="0" required>
                        </div>

                        <!-- Kelas Baru -->
                        <div class="col-md-6">
                            <label class="form-label">Kelas Baru</label>
                            <select name="kelas_baru" class="form-select">
                                <option value="">-- Pilih Kelas --</option>
                                <option value="biMBA-AIUEO" {{ $item->kelas == 'biMBA-AIUEO' ? 'selected' : '' }}>biMBA-AIUEO</option>
                                <option value="English biMBA" {{ $item->kelas == 'English biMBA' ? 'selected' : '' }}>English biMBA</option>
                            </select>
                        </div>

                        <!-- Tanggal Pindah -->
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Pindah</label>
                            <input type="date" name="tanggal_pindah_golongan" class="form-control" 
                                value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <!-- Alasan -->
                        <div class="col-12">
                            <label class="form-label">Alasan Pindah Golongan</label>
                            <textarea name="alasan_pindah" class="form-control" rows="3" 
                                placeholder="Contoh: Naik level, permintaan orang tua, dll..."></textarea>
                        </div>

                        <input type="hidden" name="source" value="manual">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Tidak ada data</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $bukuInduk->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .select2-container .select2-selection--single {
            height: calc(1.5em + .75rem + 2px);
            padding-top: .375rem;
        }

        /* ============================================= */
        /* Mobile & tablet (max 1024px): tetap tabel 5 kolom, termasuk landscape */
        /* ============================================= */
        @media (max-width: 1024px) {
            .container-fluid {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .card-body {
                padding: 1rem 0.5rem !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin: 0 -0.5rem;
            }

            .table {
                font-size: 0.85rem;
                min-width: 800px;
                /* paksa scroll horizontal jika layar sempit */
            }

            .table th,
            .table td {
                padding: 0.45rem 0.6rem;
                white-space: normal;
                vertical-align: middle;
            }

            /* Sembunyikan header & kolom desktop */
            .d-none.d-lg-table-row,
            .d-none.d-lg-table-cell {
                display: none !important;
            }

            /* Tombol aksi vertikal di kolom kecil */
            .d-flex.gap-1 {
                flex-direction: column;
                gap: 0.35rem !important;
            }

            .btn-sm {
                padding: 0.25rem 0.45rem;
                font-size: 0.78rem;
            }
        }

        /* Desktop besar (>1024px): tabel full kolom */
        @media (min-width: 1025px) {

            .table th,
            .table td {
                white-space: nowrap;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    const sppMapping = @json($sppMapping ?? []);

    @foreach($bukuInduk as $item)
        const statusSelect{{ $item->id }} = $('#statusSelect{{ $item->id }}');
        
        // FIELD GROUP
        const keluarFields{{ $item->id }} = $('#keluarFields{{ $item->id }}');
        const cutiFields{{ $item->id }}   = $('#cutiFields{{ $item->id }}');
        const pindahFields{{ $item->id }} = $('#pindahFields{{ $item->id }}');

        // INPUT FIELD
        const tglKeluar{{ $item->id }} = $('#tgl_keluar{{ $item->id }}');

        const golBaru{{ $item->id }} = $('#gol_baru{{ $item->id }}');
        const kdBaru{{ $item->id }}  = $('#kd_baru{{ $item->id }}');
        const sppBaru{{ $item->id }} = $('#spp_baru{{ $item->id }}');

        const currentStatus{{ $item->id }} = '{{ strtolower($item->status ?? "") }}';

        // RESET SEMUA FIELD
        function resetAllFields{{ $item->id }}() {
            keluarFields{{ $item->id }}.hide();
            cutiFields{{ $item->id }}.hide();
            pindahFields{{ $item->id }}.hide();

            tglKeluar{{ $item->id }}.prop('required', false);
            golBaru{{ $item->id }}.prop('required', false);
            kdBaru{{ $item->id }}.prop('required', false);
            sppBaru{{ $item->id }}.prop('required', false);
        }

        // Saat modal terbuka
        $('#statusModal{{ $item->id }}').on('shown.bs.modal', function () {
            resetAllFields{{ $item->id }}();

            if (currentStatus{{ $item->id }} === 'cuti') {
                // Jika murid sedang cuti → default ke Aktif Kembali
                statusSelect{{ $item->id }}.val('Aktif');
                cutiFields{{ $item->id }}.show();
            }
        });

        // Saat pilihan status berubah
        statusSelect{{ $item->id }}.on('change', function () {
            resetAllFields{{ $item->id }}();

            const selected = this.value;

            if (selected === 'Keluar') {
                keluarFields{{ $item->id }}.show();
                tglKeluar{{ $item->id }}.prop('required', true);

            } else if (selected === 'Cuti' || selected === 'Aktif') {
                // Tampilkan field cuti baik untuk perpanjang cuti maupun mengakhiri cuti
                cutiFields{{ $item->id }}.show();

            } else if (selected === 'Pindah Golongan') {
                pindahFields{{ $item->id }}.show();
                golBaru{{ $item->id }}.prop('required', true);
                kdBaru{{ $item->id }}.prop('required', true);
                sppBaru{{ $item->id }}.prop('required', true);
            }
        });

        // AUTO FILL SPP (Pindah Golongan)
        function updateSPP{{ $item->id }}() {
            const gol = golBaru{{ $item->id }}.val();
            const kd  = kdBaru{{ $item->id }}.val();

            if (gol && kd && sppMapping[gol] && sppMapping[gol][kd] !== undefined) {
                let nilai = Math.round(parseFloat(sppMapping[gol][kd]));
                sppBaru{{ $item->id }}.val(nilai);
            } else {
                sppBaru{{ $item->id }}.val('');
            }
        }

        golBaru{{ $item->id }}.on('change', updateSPP{{ $item->id }});
        kdBaru{{ $item->id }}.on('change', updateSPP{{ $item->id }});

    @endforeach

});
$(document).ready(function () {

    // =========================
    // SELECT2 FILTER
    // =========================
    $('#unitFilter, #muridFilter').select2({
        width: '100%',
        allowClear: true
    });

    // =========================
    // AUTO SUBMIT FILTER
    // =========================
    $('#unitFilter, #muridFilter, #statusFilter, #perPageFilter').on('change', function () {
        $('#filterForm').submit();
    });

});
</script>
@endpush