@extends('layouts.app')

@section('title', 'Edit Data Murid - ' . $bukuInduk->nama)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-11">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-primary fw-bold d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit Murid: {{ $bukuInduk->nim }} | {{ $bukuInduk->nama }}</h3>
                    <a href="{{ route('buku_induk.index') }}" class="btn btn-light btn-sm">Kembali ke Daftar</a>
                </div>

                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('buku_induk.update', $bukuInduk->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">

                            <!-- 1. IDENTITAS UTAMA -->
                            <div class="col-12">
                                <h5 class="text-primary fw-bold border-bottom pb-0 mb-0">DETAIL MURID</h5>
                            </div>

                            @php
                                    $user = auth()->user();
                                @endphp

                                @if($user && empty($user->bimba_unit))
                                    {{-- ADMIN / belum punya unit → tampilkan select --}}
                                    <div class="col-lg-3">
                                        <label class="form-label fw-bold text-primary">
                                            Unit biMBA <span class="text-danger">*</span>
                                        </label>

                                        <select name="bimba_unit" id="bimba_unit_select"
                                            class="form-select @error('bimba_unit') is-invalid @enderror" required>

                                            <option value="">-- Pilih biMBA Unit --</option>

                                            @foreach($units as $namaUnit => $noCabang)
                                                <option value="{{ $namaUnit }}"
                                                    data-no-cabang="{{ $noCabang }}"
                                                    {{ old('bimba_unit', $bukuInduk->bimba_unit) === $namaUnit ? 'selected' : '' }}>
                                                    {{ $namaUnit }} ({{ $noCabang }})
                                                </option>
                                            @endforeach

                                        </select>

                                        @error('bimba_unit')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    {{-- USER → hidden (pakai unit dari login) --}}
                                    <input type="hidden" name="bimba_unit" value="{{ $user->bimba_unit }}">
                                @endif

                                 <div class="col-lg-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                                       value="{{ old('nama', $bukuInduk->nama) }}" required>
                                @error('nama') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" name="tmpt_lahir" class="form-control"
                                       value="{{ old('tmpt_lahir', $bukuInduk->tmpt_lahir) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" name="tgl_lahir" id="tgl_lahir" class="form-control"
                                    value="{{ old('tgl_lahir', $bukuInduk->tgl_lahir?->format('d-m-Y')) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-primary fw-bold">Usia</label>
                                <input type="text" id="usia_display" class="form-control bg-light text-center" readonly
                                    value="{{ $bukuInduk->usia ?? '-' }} tahun">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tanggal Daftar</label>
                                <input type="text" name="tgl_daftar" id="tgl_daftar" class="form-control"
                                       value="{{ old('tgl_daftar', $bukuInduk->tgl_daftar?->format('d-m-Y')) }}">
                                <small class="text-muted">Jika kosong, akan otomatis diisi tanggal hari ini.</small>
                            </div>

                            <!-- Tanggal Aktif -->
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Aktif <span class="text-danger">*</span></label>
                                <input type="text" name="tgl_masuk" id="tgl_masuk" class="form-control"
                                    value="{{ old('tgl_masuk', $bukuInduk->tgl_masuk?->format('d-m-Y')) }}" required>
                            </div>

                            <!-- Lama Belajar -->
                            <div class="col-md-3">
                                <label class="form-label text-primary fw-bold">Lama Belajar</label>
                                <input type="text" id="lama_belajar_display" class="form-control bg-light text-center" readonly
                                    value="{{ $bukuInduk->lama_bljr ?? '0 tahun 0 bulan' }}">
                            </div>

                            <!--NIM DI hidden -->
                            

                                                  @php
                                    $user = auth()->user();
                                @endphp

                                @if($user && empty($user->no_cabang))
                                    {{-- ADMIN / belum punya no cabang → tampilkan --}}
                                    <div class="col-lg-3">
                                        <label class="form-label fw-bold">No Cabang</label>

                                        <input type="text"
                                            id="no_cabang_display"
                                            class="form-control bg-light text-center fw-bold fs-5 text-primary"
                                            readonly
                                            value="{{ old('no_cabang', $bukuInduk->no_cabang ?? '-') }}">

                                        <input type="hidden"
                                            name="no_cabang"
                                            id="no_cabang_hidden"
                                            value="{{ old('no_cabang', $bukuInduk->no_cabang ?? '') }}">
                                    </div>
                                @else
                                    {{-- USER → tidak tampil, pakai dari login --}}
                                    <input type="hidden" name="no_cabang" value="{{ $user->no_cabang }}">
                                @endif

                                <div class="col-lg-3">
                                <input type="hidden" name="nim" value="{{ old('nim', $bukuInduk->nim) }}">
                                @error('nim') 
                                    <div class="text-danger small">{{ $message }}</div> 
                                @enderror
                            </div>
                            
                            <!--- End -->
                           <div class="col-md-12">
                                <label class="form-label fw-bold text-primary">Status Saat Ini</label>

                                @php
                                    $status = strtolower($bukuInduk->status ?? 'baru');

                                    $class = match($status) {
                                        'aktif' => 'bg-success text-white',
                                        'keluar' => 'bg-danger text-white',
                                        'baru' => 'bg-primary text-white',
                                        default => 'bg-secondary text-white',
                                    };
                                @endphp

                                <div class="form-control text-center fs-5 fw-bold {{ $class }}">
                                    {{ ucfirst($status) }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tanggal Keluar</label>
                                <input type="text" name="tgl_keluar" id="tgl_keluar" class="form-control" placeholder="Masukan Tanggal Keluar"
                                       value="{{ old('tgl_keluar', $bukuInduk->tgl_keluar?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Kategori Keluar</label>
                                <select name="kategori_keluar" id="kategori_keluar" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoriKeluarOptions as $kk)
                                        <option value="{{ $kk }}" {{ old('kategori_keluar', $bukuInduk->kategori_keluar) == $kk ? 'selected' : '' }}>{{ $kk }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Alasan Keluar</label>
                                <input type="text" name="alasan" class="form-control"
                                       value="{{ old('alasan', $bukuInduk->alasan) }}">
                            </div>
                            <!-- Batas Akhir Murid Detail -->

                            <div class="col-md-3">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                <select name="kelas" id="kelas" class="form-select" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelasOptions as $k)
                                        <option value="{{ $k }}" {{ old('kelas', $bukuInduk->kelas) == $k ? 'selected' : '' }}>{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Gol <span class="text-danger">*</span></label>
                                <select name="gol" id="gol" class="form-control">
                                    <option value="">-- Pilih Golongan --</option>
                                    @foreach($golOptions as $gol)
                                        <option value="{{ $gol->kode }}" 
                                            {{ old('gol', $bukuInduk->gol) == $gol->kode ? 'selected' : '' }}>
                                            {{ $gol->kode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">KD <span class="text-danger">*</span></label>
                                <select name="kd" id="kd" class="form-select" required>
                                    <option value="">-- Pilih KD --</option>
                                    @foreach($kdOptions as $kd)
                                        <option value="{{ $kd }}" {{ old('kd', $bukuInduk->kd) == $kd ? 'selected' : '' }}>
                                            {{ $kd }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-primary fw-bold">SPP <span class="text-danger">*</span></label>
                                <input type="text" id="spp_display" class="form-control bg-light text-success fw-bold text-center" readonly
                                       value="{{ $bukuInduk->spp ? 'Rp ' . number_format($bukuInduk->spp,0,',','.') : 'Belum ditentukan' }}">
                                <input type="hidden" name="spp" id="spp" value="{{ old('spp', $bukuInduk->spp) }}">
                            </div>

                            <!-- No Cabang tidak muncul untuk User -->

                            <!-- END -->
                            <div class="col-md-3">
                                <label class="form-label">Tahap</label>
                                <select name="tahap" id="tahap_select" class="form-select">
                                    <option value="">-- Pilih Tahap --</option>
                                    @foreach($tahapanOptions as $t)
                                        <option value="{{ $t }}"
                                            {{ old('tahap', $bukuInduk->tahap) == $t ? 'selected' : '' }}>
                                            {{ $t }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold">Tanggal Tahap</label>
                                <input type="text" name="tgl_tahapan" id="tgl_tahapan" class="form-control"
                                    value="{{ old('tgl_tahapan', $bukuInduk->tgl_tahapan?->format('d-m-Y')) }}">
                            </div>

                            <div class="w-100"></div> <!-- 🔥 ini bikin turun ke baris baru -->
                            

                            <div class="col-md-4">
                                <label class="form-label">Petugas Trial</label>
                                <select name="petugas_trial" id="petugas_trial" class="form-select">
                                    <option value="">-- Tidak Ada --</option>
                                    @foreach($profil as $p)
                                        <option value="{{ $p->nama }}"
                                            {{ old('petugas_trial', $bukuInduk->petugas_trial) == $p->nama ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Guru / Motivator</label>
                                <select name="guru" id="guru" class="form-select">
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($profil as $p)
                                        <option value="{{ $p->nama }}"
                                            {{ old('guru', $bukuInduk->guru) == $p->nama ? 'selected' : '' }}>
                                            {{ $p->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="w-100"></div> <!-- 🔥 ini bikin turun ke baris baru -->

                             <div class="col-md-2">
                                <label class="form-label">Jenis KBM</label>
                                <select name="jenis_kbm" id="jenis_kbm" class="form-select">
                                    <option value="">-- Pilih Jenis --</option>
                                    @foreach($jenisKbmOptions as $jk)
                                        <option value="{{ $jk }}" {{ old('jenis_kbm', $bukuInduk->jenis_kbm) == $jk ? 'selected' : '' }}>{{ $jk }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Level</label>
                                <select name="level" id="level" class="form-select">
                                    <option value="">-- Pilih Level --</option>
                                    @foreach($levelOptions as $l)
                                        <option value="{{ $l }}" {{ old('level', $bukuInduk->level) == $l ? 'selected' : '' }}>
                                            {{ $l }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold">Tanggal Kenaikan Level</label>
                                <input type="text" name="tgl_level" id="tgl_level" class="form-control" placeholder="Masukan Tanggal Kenaikan Level"
                                    value="{{ old('tgl_level', $bukuInduk->tgl_level?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Keterangan Level</label>
                                <textarea name="keterangan_level" class="form-control" rows="1">{{ old('keterangan_level', $bukuInduk->keterangan_level) }}</textarea>
                            </div>

                            <ul class="list-group mt-2">
                            @foreach($bukuInduk->levelHistories as $h)
                                <li class="list-group-item">
                                    <strong>{{ $h->level }}</strong> - 
                                    {{ \Carbon\Carbon::parse($h->tgl_level)->format('d M Y') }}

                                    @if($h->keterangan)
                                        <br>
                                        <small class="text-muted">
                                            {{ $h->keterangan }}
                                        </small>
                                    @endif
                                </li>
                            @endforeach
                            </ul>

                            

                            <div class="col-md-6">
                                <label class="form-label">Nama Orang Tua <span class="text-danger">*</span></label>
                                <input type="text" name="orangtua" class="form-control"
                                       value="{{ old('orangtua', $bukuInduk->orangtua) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. Telp / HP <span class="text-danger">*</span></label>
                                <input type="text" name="no_telp_hp" class="form-control"
                                       value="{{ old('no_telp_hp', $bukuInduk->no_telp_hp) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Alamat Murid</label>
                                <textarea name="alamat_murid" class="form-control" rows="1">{{ old('alamat_murid', $bukuInduk->alamat_murid) }}</textarea>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Note</label>
                                <select name="note" id="note" class="form-select">
                                    <option value="">-- Tidak Ada --</option>
                                    @foreach($noteOptions as $n)
                                        <option value="{{ $n }}" {{ old('note', $bukuInduk->note) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                    <label class="form-label">Info <span class="text-danger">*</span></label>
                                    <select name="info" id="info_select" class="form-select" required>
                                        <option value="">-- Pilih Info --</option>
                                        @foreach($infoOptions as $i)
                                            <option value="{{ $i }}"
                                                {{ old('info', $bukuInduk->info) == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- 🔥 KETERANGAN (AWALNYA DIHIDE) --}}
                                <div class="col-md-3 mt-2" id="keterangan_info_wrapper" style="display: none;">
                                    <label class="form-label text-success fw-bold">Keterangan Info</label>
                                    <textarea name="keterangan_info" class="form-control" rows="2">
                                {{ old('keterangan_info', $bukuInduk->keterangan_info ?? '') }}
                                    </textarea>
                            </div>

                            <!-- NAMA HUMAS (Murid) -->
                            <div class="col-md-3 mt-2" id="nama_humas_wrapper" style="display: none;">
                                <label class="form-label text-success fw-bold">Nama Humas (Murid) <span class="text-danger">*</span></label>
                                <select name="nama_humas" id="nama_humas_select" class="form-select @error('nama_humas') is-invalid @enderror">
                                    <option value="">-- Pilih Murid sebagai Humas --</option>
                                    
                                    @foreach($muridOptions as $murid)  {{-- $muridOptions akan kita passing dari controller --}}
                                        <option value="{{ $murid->nama }}"
                                            {{ old('nama_humas', $bukuInduk->nama_humas) == $murid->nama ? 'selected' : '' }}>
                                            {{ $murid->nama }} 
                                            @if($murid->nim) ({{ $murid->nim }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('nama_humas')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">No Pembayaran Murid / VA</label>
                                <input type="text" 
                                    name="no_pembayaran_murid" 
                                    class="form-control"
                                    value="{{ old('no_pembayaran_murid', $bukuInduk->no_pembayaran_murid) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">No Cab/Marge</label>
                                <input type="text" 
                                    name="no_cab_merge" 
                                    class="form-control"
                                    value="{{ old('no_cab_merge', $bukuInduk->no_cab_merge) }}">
                            </div>

                            <!-- 5. KBM & JADWAL -->
                            <div class="col-12"><hr class="my-4"></div>
                                <h4 class="col-12 mb-3">⏰ JADWAL biMBA</h4>

                            <div class="col-md-2">
                                <label class="form-label fw-bold text-success">Kode Jadwal <span class="text-danger">*</span></label>
                                <select name="kode_jadwal" id="kode_jadwal" class="form-select" required>
                                    <option value="">-- Pilih Kode Jadwal --</option>
                                    @foreach($kodeJadwalOptions as $kode)
                                        <option value="{{ $kode }}"
                                            {{ old('kode_jadwal', $bukuInduk->kode_jadwal) == $kode ? 'selected' : '' }}>
                                            {{ $kode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-success">Hari & Jam</label>
                                <div id="jadwal_preview" class="form-control-plaintext border p-2 bg-light">
                                    @php
                                        $details = $bukuInduk->jadwal()
                                            ->select('hari', 'jam_ke', 'shift')
                                            ->distinct()
                                            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
                                            ->orderBy('jam_ke')
                                            ->get();
                                    @endphp

                                    @if($details->isNotEmpty())
                                        @php
                                            $shift = $details->first()->shift;
                                            $hariList = $details->pluck('hari')->unique()->values()->toArray();
                                            $jamMap = [
                                                1 => '08:00', 2 => '09:00', 3 => '10:00', 4 => '11:00', 5 => '12:00',
                                                6 => '13:00', 7 => '14:00', 8 => '15:00', 9 => '16:00'
                                            ];
                                            $jamKe = $details->min('jam_ke');
                                            $jam = $jamMap[$jamKe] ?? '-';
                                        @endphp

                                        <strong>{{ $shift }}</strong>
                                        <span class="text-muted">({{ implode(' | ', $hariList) }})</span>
                                        - <span class="fw-bold text-primary">{{ $jam }}</span>

                                    @else
                                        <span class="text-muted">Pilih kode jadwal terlebih dahulu</span>
                                    @endif
                                </div>
                            </div>

                            {{-- ================= BEASISWA ================= --}}
                             <div class="col-12"><hr class="my-4"></div>
                                <h4 class="col-12 mb-3 fw-bold">🗓️ MASA AKTIF (DHUAFA & BNF)</h4>

                                <div class="row g-3">

                                    {{-- PERIODE --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-success">Periode</label>

                                        @php
                                            $selectedPeriode = old('periode', $bukuInduk->periode ?? '');
                                            preg_match('/\d+/', $selectedPeriode, $match);
                                            $selectedNumber = $match[0] ?? '';
                                        @endphp

                                        <select name="periode" class="form-control text-danger">
                                            <option value="">-- Pilih --</option>
                                            @for ($i = 1; $i <= 10; $i++)
                                                <option value="Ke-{{ $i }}" {{ $selectedNumber == $i ? 'selected' : '' }}>
                                                    Ke-{{ $i }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>

                                    {{-- TGL MULAI --}}
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-success">Tanggal Mulai</label>
                                        <input type="text"
                                            name="tgl_mulai"
                                            id="tgl_mulai"
                                            class="form-control"
                                            value="{{ old('tgl_mulai', $bukuInduk->tgl_mulai?->format('d-m-Y')) }}">
                                    </div>

                                    {{-- TGL AKHIR --}}
                                    <div class="col-md-3" id="beasiswaSection">
                                        <label class="form-label fw-bold text-success">Tanggal Akhir</label>
                                        <input type="text"
                                            name="tgl_akhir"
                                            id="tgl_akhir"
                                            class="form-control"
                                            value="{{ old('tgl_akhir', $bukuInduk->tgl_akhir?->format('d-m-Y')) }}">
                                    </div>

                                    {{-- JUMLAH --}}
                                    <div class="col-md-3" id="beasiswaSection2">
                                        <label class="form-label fw-bold text-success">Jumlah Beasiswa</label>
                                        <input type="number"
                                            name="jumlah_beasiswa"
                                            id="jumlah_beasiswa"
                                            class="form-control"
                                            value="{{ old('jumlah_beasiswa', $bukuInduk->jumlah_beasiswa ?? '') }}">
                                    </div>

                                </div>

                                {{-- CHECKBOX --}}
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="alert"
                                                id="alert"
                                                value="aktif"
                                                {{ old('alert', $bukuInduk->alert) === 'aktif' ? 'checked' : '' }}>

                                            <label class="form-check-label text-info" for="alert">
                                                Beasiswa Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <div class="col-12">
                                <hr class="my-4">
                            </div>

                            <div class="col-12 mb-3">
                                <h4 class="fw-bold">⏱️ Masa Aktif Paket 72</h4>
                            </div>

                            <div class="row">

                                    {{-- TANGGAL BAYAR --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="tgl_bayar" class="form-label">Tanggal Bayar</label>
                                        <input type="date"
                                            name="tgl_bayar"
                                            id="tgl_bayar"
                                            class="form-control"
                                            value="{{ old('tgl_bayar', optional($bukuInduk->tgl_bayar)->format('Y-m-d')) }}">
                                    </div>

                                    {{-- TANGGAL SELESAI --}}
                                    <div class="col-md-4 mb-3">
                                        <label for="tgl_selesai" class="form-label fw-bold text-success">Tanggal Selesai</label>
                                        <input type="date"
                                            name="tgl_selesai"
                                            id="tgl_selesai"
                                            class="form-control"
                                            value="{{ old('tgl_selesai', optional($bukuInduk->tgl_selesai)->format('Y-m-d')) }}">
                                    </div>

                                    {{-- ALERT --}}
                                    <div class="col-md-4 mb-3 d-flex align-items-end">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                type="checkbox"
                                                name="alert2"
                                                id="alert2"
                                                value="aktif"
                                                {{ old('alert2', $bukuInduk->alert2 ?? '') === 'aktif' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold text-info" for="alert2">
                                                Paket 72 Aktif
                                            </label>
                                        </div>
                                    </div>

                                </div>


                            <!-- Supply Modul -->
                            <div class="col-12"><hr class="my-4"></div>
                                <h4 class="col-12 mb-3">📚 SUPLLY MODUL</h4>
                                

                             <div class="col-md-3">
                                <label class="form-label">Asal Modul</label>
                                <select name="asal_modul" id="asal_modul" class="form-select">
                                    <option value="">-- Pilih Asal Modul --</option>
                                    @foreach($asalModulOptions as $am)
                                        <option value="{{ $am }}" 
                                            {{ old('asal_modul', $bukuInduk->asal_modul) == $am ? 'selected' : '' }}>
                                            {{ $am }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-primary fw-bold">KETERANGAN OPTIONAL</label>
                                <textarea name="keterangan_optional" class="form-control" rows="1">{{ old('keterangan_optional', $bukuInduk->keterangan_optional) }}</textarea>
                            </div>

                            <div class="col-12"><hr class="my-4"></div>
                            <h4 class="col-12 mb-3">📝 SURAT GARANSI BCA 372 BEBAS</h4>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Tanggal Surat Diberikan Garansi</label>
                                <input type="date" 
                                    name="tgl_surat_garansi" 
                                    id="tgl_surat_garansi"
                                    class="form-control"
                                    value="{{ old('tgl_surat_garansi', optional($bukuInduk->tgl_surat_garansi)->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Tgl Pengajuan Garansi</label>
                                <input type="date" name="tgl_pengajuan_garansi" class="form-control"
                                    value="{{ old('tgl_pengajuan_garansi', $bukuInduk->tgl_pengajuan_garansi?->format('Y-m-d')) }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Tgl Selesai Garansi</label>
                                <input type="date" name="tgl_selesai_garansi" class="form-control"
                                    value="{{ old('tgl_selesai_garansi', $bukuInduk->tgl_selesai_garansi?->format('Y-m-d')) }}" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-primary">Masa Aktif</label>
                                <input type="text" name="masa_aktif_garansi" class="form-control"
                                    value="{{ old('masa_aktif_garansi', $bukuInduk->masa_aktif_garansi) }}" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Alasan Pengajuan Garansi</label>
                                <textarea name="alasan_garansi" class="form-control" rows="1">
                                    {{ old('alasan_garansi', $bukuInduk->alasan_garansi) }}
                                </textarea>
                            </div>

                             <div class="col-md-3">
                                <label class="form-label">Perpanjang Garansi</label>
                                <select name="perpanjang_garansi" id="perpanjang_garansi" class="form-select">
                                    <option value="">-- Pilih --</option>
                                    <option value="Ya" {{ old('perpanjang_garansi', $bukuInduk->perpanjang_garansi) == 'Ya' ? 'selected' : '' }}>Ya</option>
                                    <option value="Tidak" {{ old('perpanjang_garansi', $bukuInduk->perpanjang_garansi) == 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Note Garansi</label>
                                <select name="note_garansi" id="note_garansi" class="form-select">
                                    <option value="">-- Pilih Note --</option>
                                    @foreach($noteGaransiOptions as $ng)
                                        <option value="{{ $ng }}"
                                            {{ old('note_garansi', $bukuInduk->note_garansi) == $ng ? 'selected' : '' }}>
                                            {{ $ng }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-12"><hr class="my-4"></div>
                            <h4 class="col-12 mb-3">📝 TAMBAHAN UNTUK SURAT MUTASI</h4>

                            <!-- MODUL TERAKHIR -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-primary">Modul Terakhir</label>
                                <input type="text" 
                                    name="modul_terakhir" 
                                    id="modul_terakhir"
                                    class="form-control"
                                    value="{{ old('modul_terakhir', $bukuInduk->modul_terakhir ?? '') }}"
                                    placeholder="Masukan Modul Terakhir Murid">
                            </div>

                        </div>

                        <hr class="my-5">

                        <div class="text-end">
                            <a href="{{ route('buku_induk.index') }}" class="btn btn-secondary btn-lg px-5 me-3">Batal</a>
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="fas fa-save"></i> Update Data Murid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==================== SELECT2 ====================
    $('#gol, #kd, #kelas, #bimba_unit_select, #tahap_select, #level, #info_select, #petugas_trial, #guru, #jenis_kbm, #note, #note_garansi, #kategori_keluar, #kode_jadwal, #asal_modul, #perpanjang_garansi, #nama_humas_select')
        .select2({ 
            width: '100%', 
            placeholder: '-- Pilih --', 
            allowClear: true 
        });

    // ==================== ELEMENTS ====================
    const golSelect          = document.getElementById('gol');
    const kdSelect           = document.getElementById('kd');
    const sppHidden          = document.getElementById('spp');
    const sppDisplay         = document.getElementById('spp_display');

    // Beasiswa
    const beasiswaSection1   = document.getElementById('beasiswaSection');   
    const beasiswaSection2   = document.getElementById('beasiswaSection2');  
    const alertBeasiswa      = document.getElementById('alert');
    const periodeSelect      = document.getElementById('periode');
    const tglMulai           = document.getElementById('tgl_mulai');
    const tglAkhir           = document.getElementById('tgl_akhir');
    const jumlahBeasiswa     = document.getElementById('jumlah_beasiswa');

    // Paket 72
    const paket72Section     = document.getElementById('paket72Section');
    const alert72            = document.getElementById('alert2');
    const tglBayar           = document.getElementById('tgl_bayar');
    const tglSelesai72       = document.getElementById('tgl_selesai');

    // Date Fields
    const tglLahir           = document.getElementById('tgl_lahir');
    const usiaDisplay        = document.getElementById('usia_display');
    const tglMasuk           = document.getElementById('tgl_masuk');
    const lamaBelajarDisplay = document.getElementById('lama_belajar_display');

    // Jadwal
    const kodeJadwalSelect   = document.getElementById('kode_jadwal');
    const jadwalPreview      = document.getElementById('jadwal_preview');

    const sppMapping = @json($sppMapping ?? []);
    const beasiswaGol = ['S3B1', 'S3B2', 'S3B3', 'D'];

    // ==================== HELPER ====================
function normalizeDate(val) {
    if (!val) return '';
    val = val.toString().trim();

    // dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy → dd-mm-yyyy
    let m = val.match(/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/);
    if (m) return `${m[1].padStart(2,'0')}-${m[2].padStart(2,'0')}-${m[3]}`;

    // yyyy-mm-dd, yyyy/mm/dd → dd-mm-yyyy
    m = val.match(/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/);
    if (m) return `${m[3].padStart(2,'0')}-${m[2].padStart(2,'0')}-${m[1]}`;

    return val;
}

// Fungsi baru yang LEBIH AKURAT
function addMonthsForDateInput(dateStr, months) {
    if (!dateStr) return '';

    // Pastikan dulu diubah ke format YYYY-MM-DD (paling aman untuk JS Date)
    let cleanDate = dateStr;
    
    // Jika format dd-mm-yyyy
    let m = dateStr.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if (m) {
        cleanDate = `${m[3]}-${m[2]}-${m[1]}`;
    }

    let d = new Date(cleanDate);
    if (isNaN(d.getTime())) return '';

    // Tambah bulan dengan cara yang benar
    d.setMonth(d.getMonth() + months);

    return d.toISOString().split('T')[0];
}

function calculateAge(dateStr) {
    if (!dateStr) return '0 tahun 0 bulan';
    const iso = normalizeDate(dateStr).split('-').reverse().join('-');
    const birth = new Date(iso);
    if (isNaN(birth.getTime())) return '0 tahun 0 bulan';

    const today = new Date();
    let years = today.getFullYear() - birth.getFullYear();
    let months = today.getMonth() - birth.getMonth();
    
    if (months < 0 || (months === 0 && today.getDate() < birth.getDate())) {
        years--; 
        months += 12;
    }
    return `${years} tahun ${months} bulan`;
}

function calculateLamaBelajar(dateStr) {
    if (!dateStr) return '0 tahun 0 bulan';
    const iso = normalizeDate(dateStr).split('-').reverse().join('-');
    const start = new Date(iso);
    if (isNaN(start.getTime())) return '0 tahun 0 bulan';

    const today = new Date();
    let totalMonths = (today.getFullYear() - start.getFullYear()) * 12 + (today.getMonth() - start.getMonth());
    if (today.getDate() < start.getDate()) totalMonths--;

    const years = Math.floor(totalMonths / 12);
    const months = totalMonths % 12;
    return `${years} tahun ${months} bulan`;
}

// ==================== SETUP DATE FIELD ====================
function setupDateField(field, displayField = null, calculator = null) {
    if (!field) return;

    const processDate = () => {
        field.value = normalizeDate(field.value);
        if (displayField && calculator) {
            displayField.value = calculator(field.value);
        }
    };

    field.addEventListener('blur', processDate);
    field.addEventListener('input', () => {
        if (displayField && calculator) displayField.value = calculator(field.value);
    });
    field.addEventListener('paste', () => {
        setTimeout(processDate, 10);
    });
}

    // ==================== SPP ====================
    function updateSPP() {
        if (!golSelect || !kdSelect || !sppHidden || !sppDisplay) return;
        
        const gol = golSelect.value;
        const kd  = kdSelect.value;
        
        if (gol && kd && sppMapping[gol]?.[kd] !== undefined) {
            const harga = parseInt(sppMapping[gol][kd]);
            sppHidden.value = harga;
            sppDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(harga);
        } else {
            sppDisplay.value = 'Belum ditentukan';
        }
    }

        // ==================== BEASISWA ====================
    function handleBeasiswaAutoFill() {
        const gol = golSelect ? golSelect.value : '';
        if (!beasiswaGol.includes(gol)) return;

        const beasiswaMapping = {
            'S3B1': 100000, 'S3B2': 200000, 'S3B3': 50000, 'D': 300000
        };
        const nominal = beasiswaMapping[gol] || 0;

        if (periodeSelect && !periodeSelect.value) periodeSelect.value = 'Ke-1';
        if (tglMulai && !tglMulai.value) {
            tglMulai.value = new Date().toISOString().split('T')[0];
        }

        if (tglAkhir && tglMulai && tglMulai.value) {
            tglAkhir.value = addMonthsForDateInput(tglMulai.value, 6);
        }

        if (jumlahBeasiswa) jumlahBeasiswa.value = nominal * 6;
        if (alertBeasiswa) alertBeasiswa.checked = true;
    }

    // ==================== PAKET 72 ====================
    function handlePaket72AutoFill() {
        if (!golSelect || golSelect.value !== 'P72') return;

        if (alert72) alert72.checked = true;

        if (tglBayar && !tglBayar.value) {
            tglBayar.value = new Date().toISOString().split('T')[0];
        }

        if (tglBayar?.value && tglSelesai72) {
            tglSelesai72.value = addMonthsForDateInput(tglBayar.value, 6);
        }
    }

    function handleGolChange() {
        if (!golSelect) return;
        
        updateSPP();

        const gol = golSelect.value;

        // === BEASISWA ===
        if (beasiswaGol.includes(gol)) {
            if (beasiswaSection1) beasiswaSection1.style.display = 'block';
            if (beasiswaSection2) beasiswaSection2.style.display = 'block';
            handleBeasiswaAutoFill();
        } else {
            if (beasiswaSection1) beasiswaSection1.style.display = 'none';
            if (beasiswaSection2) beasiswaSection2.style.display = 'none';
        }

        // === PAKET 72 ===
        if (gol === 'P72') {
            if (paket72Section) paket72Section.style.display = 'block';
            handlePaket72AutoFill();
        } else {
            if (paket72Section) paket72Section.style.display = 'none';
        }
    }

    function handleTglMulaiChange() {
        if (tglMulai && tglAkhir && tglMulai.value) {
            const normalized = normalizeDate(tglMulai.value);
            tglAkhir.value = addMonthsForDateInput(normalized, 6);
        }
    }

    function handleTglBayarChange() {
        if (tglBayar && tglSelesai72 && tglBayar.value) {
            tglSelesai72.value = addMonthsForDateInput(tglBayar.value, 6);
        }
    }

    // ==================== JADWAL PREVIEW ====================
    const jadwalMap = { /* ... tetap sama ... */ 
        108: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '08:00' },
        109: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '09:00' },
        110: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '10:00' },
        111: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '11:00' },
        112: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '12:00' },
        113: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '13:00' },
        114: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '14:00' },
        115: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '15:00' },
        116: { shift: 'SRJ', hari: 'Senin | Rabu | Jumat', jam: '16:00' },

        208: { shift: 'SKS', hari: 'Selasa | Kamis | Sabtu', jam: '08:00' },
        209: { shift: 'SKS', hari: 'Selasa | Kamis | Sabtu', jam: '09:00' },
        210: { shift: 'SKS', hari: 'Selasa | Kamis | Sabtu', jam: '10:00' },
        211: { shift: 'SKS', hari: 'Selasa | Kamis | Sabtu', jam: '11:00' },

        308: { shift: 'S6', hari: 'Senin - Sabtu', jam: '08:00' },
        309: { shift: 'S6', hari: 'Senin - Sabtu', jam: '09:00' },
        310: { shift: 'S6', hari: 'Senin - Sabtu', jam: '10:00' },
        311: { shift: 'S6', hari: 'Senin - Sabtu', jam: '11:00' },
    };

    function updateJadwalPreview() {
        if (!jadwalPreview || !kodeJadwalSelect) return;
        const val = kodeJadwalSelect.value.trim();
        if (jadwalMap[val]) {
            const j = jadwalMap[val];
            jadwalPreview.innerHTML = `
                <strong>${j.shift}</strong><br>
                <span>${j.hari}</span> - 
                <span class="fw-bold text-primary">${j.jam}</span>
            `;
        } else {
            jadwalPreview.innerHTML = '<span class="text-muted">Pilih kode jadwal terlebih dahulu</span>';
        }
    }

    // ==================== INIT & EVENTS ====================
    setupDateField(tglLahir, usiaDisplay, calculateAge);
    setupDateField(tglMasuk, lamaBelajarDisplay, calculateLamaBelajar);
    setupDateField(document.getElementById('tgl_daftar'));
    setupDateField(document.getElementById('tgl_tahapan'));
    setupDateField(tglMulai);
    setupDateField(tglBayar);
    setupDateField(tglSelesai72);

    // Event Listeners dengan safety check
    if (golSelect) $(golSelect).on('change', handleGolChange);
    if (kdSelect)  $(kdSelect).on('change', updateSPP);

    if (tglMulai) {
        tglMulai.addEventListener('change', handleTglMulaiChange);
        tglMulai.addEventListener('blur', handleTglMulaiChange);
    }

    if (tglBayar) {
        tglBayar.addEventListener('change', handleTglBayarChange);
        tglBayar.addEventListener('blur', handleTglBayarChange);
    }

    if (kodeJadwalSelect) {
        $(kodeJadwalSelect).on('change', updateJadwalPreview);
        updateJadwalPreview();
    }

    // Initial Load
    handleGolChange();

});

document.addEventListener('DOMContentLoaded', function () {

    const unitSelect = document.getElementById('bimba_unit_select');
    const noCabangHidden = document.getElementById('no_cabang_hidden');
    const noCabangDisplay = document.getElementById('no_cabang_display');

    if (!unitSelect) return;

    // =============================
    // 1. UNIT → NO CABANG
    // =============================
    unitSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const noCabang = selected.getAttribute('data-no-cabang') || '';

        noCabangHidden.value = noCabang;
        if (noCabangDisplay) {
            noCabangDisplay.value = noCabang || '-';
        }
    });

    // =============================
    // 2. NO CABANG → UNIT (INI YANG KAMU BUTUH)
    // =============================
    const currentCabang = noCabangHidden?.value;

    if (currentCabang) {
        for (let i = 0; i < unitSelect.options.length; i++) {
            const opt = unitSelect.options[i];
            if (opt.getAttribute('data-no-cabang') === currentCabang) {
                unitSelect.value = opt.value;
                break;
            }
        }
    }

});
// ==================== INFO → SHOW NAMA HUMAS ====================
const infoSelect = document.getElementById('info_select');
const keteranganWrapper = document.getElementById('keterangan_info_wrapper');
const humasWrapper = document.getElementById('nama_humas_wrapper');

function handleInfoChange() {
    const value = (infoSelect?.value || '').trim().toLowerCase();

    // Keterangan Info
    if (['humas', 'referral', 'lainnya'].includes(value)) {
        keteranganWrapper.style.display = 'block';
    } else {
        keteranganWrapper.style.display = 'none';
    }

    // Nama Humas (Murid)
    if (value === 'humas') {
        humasWrapper.style.display = 'block';
    } else {
        humasWrapper.style.display = 'none';
    }
}

if (infoSelect) {
    $(infoSelect).on('change', handleInfoChange);
    setTimeout(handleInfoChange, 400); // untuk edit mode
}
</script>

<style>
    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
        padding-top: 0.375rem !important;
        padding-bottom: 0.375rem !important;
    }
</style>
@endpush