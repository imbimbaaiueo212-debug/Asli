@extends('layouts.app')

@section('title', 'Tambah Pendaftaran')

@section('content')
<div class="container">
    <h2 class="mb-3">Tambah Pendaftaran</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $students           = $students           ?? collect();
        $selectedStudentId  = $selectedStudentId  ?? old('student_id');
        $prefilledNim       = $prefilledNim       ?? '';
        $prefilledNama      = $prefilledNama      ?? '';
        $prefilledUnit      = $prefilledUnit      ?? '';
        $prefilledCabang    = $prefilledCabang    ?? '';
        $prefilledTglLahir  = $prefilledTglLahir  ?? '';
        $prefilledTglMasuk  = $prefilledTglMasuk  ?? '';
        $tahapanOptions     = $tahapanOptions     ?? [];
        $kelasOptions       = $kelasOptions       ?? [];
        $hargaSaptataruna   = $hargaSaptataruna   ?? collect();
        $kdOptions          = $kdOptions          ?? [];
        $sppMapping         = $sppMapping         ?? [];
        $guruOptions        = $guruOptions        ?? [];
    @endphp

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('registrations.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- IDENTITAS MURID --}}
        <h5 class="mb-3">Identitas Murid</h5>

        <div class="mb-3">
            <label class="form-label fw-bold">Nama Murid</label>
            <select name="student_id" class="form-select" required id="studentSelect" 
                    {{ $students->isEmpty() ? 'disabled' : '' }}>
                <option value="">-- Pilih Murid --</option>
                @foreach ($students as $s)
                    <option value="{{ $s->id }}"
                        data-unit="{{ $s->bimba_unit ?? '' }}"
                        data-cabang="{{ $s->no_cabang ?? '' }}"
                        data-nim="{{ $s->nim ?? '' }}"
                        data-nama="{{ $s->nama ?? '' }}"
                        data-tgllahir="{{ $s->tgl_lahir ?? $s->muridTrial?->tgl_lahir ?? '' }}"
                        data-orangtua="{{ $s->orangtua ?? $s->muridTrial?->orangtua ?? '' }}"
                        data-alamat="{{ $s->alamat ?? $s->muridTrial?->alamat ?? '' }}"
                        data-info="{{ $s->muridTrial?->info ?? '' }}"
                        {{ (int) $selectedStudentId === (int) $s->id ? 'selected' : '' }}>
                        {{ $s->nim ?: '—' }} — {{ $s->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- TEMPAT & TANGGAL LAHIR (SEJAJAR) --}}
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold text-primary">Tempat Lahir</label>
                <input type="text"
                    id="displayTempatLahir"
                    class="form-control bg-light"
                    value="{{ old('bi[tempat_lahir]', $prefilledTmptLahir ?? '') }}"
                    readonly>
                <input type="hidden"
                    name="bi[tempat_lahir]"
                    value="{{ old('bi[tempat_lahir]', $prefilledTmptLahir ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold text-primary">Tanggal Lahir</label>
                <input type="text"
                    id="displayTglLahir"
                    class="form-control bg-light"
                    value="{{ old('bi[tanggal_lahir]', $prefilledTglLahir ? \Carbon\Carbon::parse($prefilledTglLahir)->format('Y-m-d') : '') }}"
                    readonly>
                <input type="hidden"
                    name="bi[tanggal_lahir]"
                    value="{{ old('bi[tanggal_lahir]', $prefilledTglLahir ? \Carbon\Carbon::parse($prefilledTglLahir)->format('Y-m-d') : '') }}">
            </div>

            <div class="col-md-4 mb-3">
            <label class="form-label fw-bold text-primary">Usia</label>
            <input type="text"
                id="displayUsia"
                class="form-control bg-light"
                readonly>
            <input type="hidden"
                name="bi[usia]"
                id="hiddenUsia"
                value="{{ old('bi[usia]', $prefilledUsia ?? '') }}">
        </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-bold text-primary">Nama Orang Tua</label>
                <input type="text"
                    class="form-control bg-light"
                    value="{{ old('bi[orangtua]', $prefilledOrangtua ?? '') }}"
                    readonly>
                <input type="hidden"
                    name="bi[orangtua]"
                    value="{{ old('bi[orangtua]', $prefilledOrangtua ?? '') }}">
            </div>

            <div class="col-md-4 mb-3">
    <label class="form-label fw-bold text-primary">Nomor Telepon / HP</label>
    <input type="text" 
           class="form-control bg-light" 
           value="{{ old('bi[no_telp_hp]', 
               trim(implode(' / ', array_filter([$prefilledHpAyah ?? '', $prefilledHpIbu ?? ''])))
           ) }}" 
           readonly>
    
    <input type="hidden" 
           name="bi[no_telp_hp]" 
           value="{{ old('bi[no_telp_hp]', 
               trim(implode(' / ', array_filter([$prefilledHpAyah ?? '', $prefilledHpIbu ?? ''])))
           ) }}">
</div>

        <!-- Alamat Lengkap -->
<div class="col-md-12 mb-3">
    <label class="form-label fw-bold text-primary">Alamat Lengkap Murid</label>
    <input type="text" 
           class="form-control bg-light" 
           readonly
           value="{{ old('bi[alamat_murid]', 
               trim(implode(', ', array_filter([
                   $prefilledAlamat ?? '',
                   $prefilledNoRumah ?? '',
                   ($prefilledRt && $prefilledRw) ? "RT/RW {$prefilledRt}/{$prefilledRw}" : null,
                   $prefilledKelurahan ?? '',
                   $prefilledKecamatan ?? '',
                   $prefilledKodyaKab ?? '',
                   $prefilledProvinsi ?? ''
               ])))
           ) }}">
    
    <input type="hidden" 
           name="bi[alamat_murid]" 
           value="{{ old('bi[alamat_murid]', $prefilledAlamat ?? '') }}">
</div>
        </div>

                {{-- UNIT & CABANG (OTOMATIS DARI MURID) --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">Unit biMBA</label>
                        <input type="text"
                               id="displayUnit"
                               class="form-control bg-light"
                               value="{{ old('bimba_unit', $prefilledUnit) }}"
                               readonly>
                        <input type="hidden"
                               name="bimba_unit"
                               value="{{ old('bimba_unit', $prefilledUnit) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">No Cabang</label>
                        <input type="text"
                               id="displayCabang"
                               class="form-control bg-light"
                               value="{{ old('no_cabang', $prefilledCabang) }}"
                               readonly>
                        <input type="hidden"
                               name="no_cabang"
                               value="{{ old('no_cabang', $prefilledCabang) }}">
                    </div>
                </div>

                {{-- STATUS & TANGGAL DAFTAR --}}
                <div class="row mb-4">
                <!-- Tanggal Daftar -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Daftar</label>
                    <input type="date"
                        name="tanggal_daftar"
                        class="form-control"
                        value="{{ old('tanggal_daftar') }}">
                    @error('tanggal_daftar')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Tanggal Mulai KBM -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Mulai KBM</label>
                    <input type="date"
                        name="tanggal_penerimaan"
                        class="form-control"
                        value="{{ old('tanggal_penerimaan') }}">
                    @error('tanggal_penerimaan')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Hari</label>
                        <input type="text" 
                            name="bi[hari]" 
                            class="form-control"
                            value="{{ old('bi[hari]', $prefilledHari ?? '') }}"
                            placeholder="Senin, Selasa, Rabu..." readonly>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Jam</label>
                        <input type="text" 
                            name="bi[jam]" 
                            class="form-control"
                            value="{{ old('bi[jam]', $prefilledJam ?? '') }}"
                            placeholder="08:00 - 09:30" readonly>
                    </div>
                </div>
                {{-- End --}}

                <div class="row">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tahapan</label>
                            <select class="form-control" name="bi[tahap]" id="tahap">
                                <option value="">-- Pilih Tahapan --</option>
                                @foreach ($tahapanOptions as $t)
                                    <option value="{{ $t }}" {{ old('bi.tahap') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="tgl_tahapan_wrapper" style="display: none;">
                            <label class="form-label fw-bold text-success">Tanggal Tahapan <span class="text-danger">*</span></label>
                            <input type="date" 
                                name="bi[tgl_tahapan]" 
                                id="tgl_tahapan" 
                                class="form-control"
                                value="{{ old('bi.tgl_tahapan') }}">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-control" name="bi[kelas]">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelasOptions as $k)
                                <option value="{{ $k }}" {{ old('bi.kelas') === $k ? 'selected' : '' }}>
                                    {{ $k }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-primary fw-bold">Guru Pengajar</label>
                        <select class="form-control" name="bi[guru]">
                            <option value="">-- Pilih Guru --</option>
                            @foreach ($guruOptions as $guru)
                                <option value="{{ $guru }}" {{ old('bi.guru') === $guru ? 'selected' : '' }}>
                                    {{ $guru }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Gol <span class="text-danger">*</span></label>
                        <select class="form-control" name="bi[gol]" id="bi_gol" required>
                            <option value="">-- Pilih Gol --</option>
                            @foreach ($hargaSaptataruna->unique('kode') as $row)
                                @if ($row->kode)
                                    <option value="{{ $row->kode }}" {{ old('bi.gol') === $row->kode ? 'selected' : '' }}>
                                        {{ $row->kode }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- KD -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">KD <span class="text-danger">*</span></label>
                        <select class="form-control" name="bi[kd]" id="bi_kd" required>
                            <option value="">-- Pilih KD --</option>
                            @foreach (['A','B','C','D','E','F'] as $kd)
                                <option value="{{ $kd }}" {{ old('bi.kd') === $kd ? 'selected' : '' }}>{{ $kd }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SPP -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">SPP</label>
                        <input type="text" class="form-control bg-light" id="bi_spp_display" readonly placeholder="Otomatis terisi">
                        <input type="hidden" name="bi[spp]" id="bi_spp" value="{{ old('bi.spp') }}">
                    </div>
                </div>
                <div class="row mb-4">

                    <div class="row">
                <!-- Jenis KBM -->
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Jenis KBM</label>
                    <select name="bi[jenis_kbm]" class="form-control">
                        <option value="">-- Pilih Jenis KBM --</option>
                        @foreach($jenisKbmOptions ?? [] as $jk)
                            <option value="{{ $jk }}">{{ $jk }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Level -->
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Level</label>
                    <select name="bi[level]" id="level" class="form-control">
                        <option value="">-- Pilih Level --</option>
                        @foreach($levelOptions ?? [] as $l)
                            <option value="{{ $l }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Level (muncul otomatis) -->
                <div class="col-md-4 mb-3" id="tgl_level_wrapper" style="display: none;">
                    <label class="form-label fw-bold text-success">Tanggal Level</label>
                    <input type="date" 
                        name="bi[tgl_level]" 
                        id="tgl_level" 
                        class="form-control"
                        value="{{ old('bi.tgl_level') }}" required>
                </div>
            </div>

            <!-- === BAGIAN DUafa & BNF (mirip Buku Induk) === -->
                <div id="duafa-bnf-section" style="display: none;">
                    <hr>
                    <h5 class="text-success">🗓️ Masa Aktif (Dhuafa & BNF)</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label>Periode</label>
                            <select name="bi[periode]" id="periode" class="form-control">
                                <option value="">-- Pilih --</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="Ke-{{ $i }}">Ke-{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="bi[tgl_mulai]" id="tgl_mulai" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Tanggal Akhir</label>
                            <input type="date" name="bi[tgl_akhir]" id="tgl_akhir" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Jumlah Beasiswa</label>
                            <input type="number" name="bi[jumlah_beasiswa]" id="jumlah_beasiswa" class="form-control">
                        </div>
                    </div>
                </div>

            <hr class="my-4">

            <h5 class="mb-3 text-primary">Supply Modul</h5>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Asal Modul</label>
                    <select name="bi[asal_modul]" class="form-control">
                        <option value="">-- Pilih Asal Modul --</option>
                        @foreach($asalModulOptions ?? [] as $am)
                            <option value="{{ $am }}">{{ $am }}</option>
                        @endforeach
                    </select>
                </div>

                                <hr class="my-4">

                <!-- SURAT GARANSI BCA 372 BEBAS -->
                <h4 class="col-12 mb-3">📝 SURAT GARANSI BCA 372 BEBAS</h4>

                <div class="row">
                    <div class="col-md-6 mb-3 fw-bold">
                        <label for="tgl_surat_garansi">Tanggal Diberikan Surat</label>
                        <input type="text" 
                               name="bi[tgl_surat_garansi]" 
                               id="tgl_surat_garansi" 
                               class="form-control" 
                               placeholder="DD-MM-YYYY"
                               value="{{ old('bi.tgl_surat_garansi') }}">
                        @error('bi.tgl_surat_garansi')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Note Garansi -->
                    <div class="col-md-6 mb-3 fw-bold">
                        <label for="note_garansi">Note Garansi</label>
                        <select name="bi[note_garansi]" id="note_garansi" class="form-control">
                            <option value="">-- Pilih Note Garansi --</option>
                            @foreach($noteGaransiOptions as $ng)
                                <option value="{{ $ng }}" {{ old('bi.note_garansi') == $ng ? 'selected' : '' }}>
                                    {{ $ng }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Keterangan Optional</label>
                    <input type="text" 
                        name="bi[keterangan_optional]" 
                        class="form-control" 
                        value="{{ old('bi.keterangan_optional') }}"
                        placeholder="Catatan tambahan...">
                </div>
            </div>
                   <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Informasi</label>

                        {{-- Hidden agar value tetap terkirim --}}
                        <input type="hidden"
                            name="bi[info]"
                            value="{{ old('bi.info', $prefilledInfo ?? '') }}">

                        <select class="form-select" disabled>
                            <option value="">-- Pilih Informasi --</option>

                            @foreach($infoOptions as $option)
                                <option value="{{ $option }}"
                                    {{ old('bi.info', $prefilledInfo ?? '') == $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
               <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Upload Bukti Pembayaran <span class="text-danger">*</span></label>
                        <input type="file" 
                            id="attachment"
                            name="attachment"
                            class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png,.webp"
                            required>
                        <small class="text-muted">Maks 3MB (PDF, JPG, PNG, WebP)</small>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        
                        @php
                            $st = old('status', 'pending');
                        @endphp
                        
                        <select name="status" id="status" class="form-select" required>
                            <option value="pending"  {{ $st === 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ $st === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="accepted" id="opt-accepted" 
                                    {{ $st === 'accepted' ? 'selected' : '' }}>Accepted</option>
                            <option value="rejected" {{ $st === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                </div>
               
                {{-- TOMBOL AKSI --}}
                <div class="d-flex gap-2 mt-4">
                    <button type="submit"
                            class="btn btn-primary"
                            {{ $students->isEmpty() ? 'disabled' : '' }}>
                        Simpan Pendaftaran
                    </button>
                    <a href="{{ route('registrations.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==================== USIA ====================
    const tglLahirInput = document.querySelector('input[name="bi[tanggal_lahir]"]');
    const displayUsia   = document.getElementById('displayUsia');
    const hiddenUsia    = document.getElementById('hiddenUsia');

    function hitungUsia() {
        if (!tglLahirInput || !displayUsia) return;

        let val = tglLahirInput.value.trim();
        if (!val) {
            displayUsia.value = '';
            if (hiddenUsia) hiddenUsia.value = '';
            return;
        }

        let birthDate = new Date(val);
        if (isNaN(birthDate.getTime())) {
            let parts = val.split(/[-/]/);
            if (parts.length === 3) {
                if (parts[0].length === 4) {
                    birthDate = new Date(parts[0], parts[1]-1, parts[2]);
                } else {
                    birthDate = new Date(parts[2], parts[1]-1, parts[0]);
                }
            }
        }

        if (isNaN(birthDate.getTime())) {
            displayUsia.value = 'Format salah';
            return;
        }

        const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();

        if (months < 0 || (months === 0 && today.getDate() < birthDate.getDate())) {
            years--;
            months += 12;
        }

        const usiaText = `${years} tahun ${months} bulan`;
        displayUsia.value = usiaText;
        if (hiddenUsia) hiddenUsia.value = years;
    }

    if (tglLahirInput) {
        tglLahirInput.addEventListener('change', hitungUsia);
        tglLahirInput.addEventListener('blur', hitungUsia);
    }
    setTimeout(hitungUsia, 600);

    // ==================== TAHAPAN ====================
    const tahapSelect = document.getElementById('tahap');
    const tglTahapanWrapper = document.getElementById('tgl_tahapan_wrapper');

    function toggleTanggalTahapan() {
        if (!tahapSelect || !tglTahapanWrapper) return;
        const value = tahapSelect.value;
        tglTahapanWrapper.style.display = (value === 'Persiapan' || value === 'Lanjutan') ? 'block' : 'none';
    }

    if (tahapSelect) {
        tahapSelect.addEventListener('change', toggleTanggalTahapan);
        setTimeout(toggleTanggalTahapan, 300);
    }

    // ==================== SPP (PERBAIKAN TERBARU) ====================
    const mapping = @json($sppMapping);
    const golSelect = document.getElementById('bi_gol');
    const kdSelect  = document.getElementById('bi_kd');
    const sppHidden = document.getElementById('bi_spp');
    const sppDisplay = document.getElementById('bi_spp_display');

    function updateSPP() {
        if (!golSelect || !kdSelect) return;

        const gol = (golSelect.value || '').trim().toUpperCase();
        const kd  = (kdSelect.value || '').trim().toUpperCase();

        let nilaiSPP = 0;
        if (mapping[gol] && mapping[gol][kd] !== undefined) {
            nilaiSPP = mapping[gol][kd];
        }

        if (sppHidden) sppHidden.value = nilaiSPP;
        if (sppDisplay) {
            sppDisplay.value = nilaiSPP > 0 
                ? new Intl.NumberFormat('id-ID').format(nilaiSPP) 
                : '0';
        }
    }

    if (golSelect) golSelect.addEventListener('change', updateSPP);
    if (kdSelect)  kdSelect.addEventListener('change', updateSPP);

    setTimeout(updateSPP, 400);
    setTimeout(updateSPP, 1000);

});

// Toggle Tanggal Level
document.addEventListener('DOMContentLoaded', function () {
    const levelSelect = document.getElementById('level');
    const tglLevelWrapper = document.getElementById('tgl_level_wrapper');

    function toggleTanggalLevel() {
        if (!levelSelect || !tglLevelWrapper) return;
        const value = levelSelect.value;
        tglLevelWrapper.style.display = value ? 'block' : 'none';
    }

    if (levelSelect) {
        levelSelect.addEventListener('change', toggleTanggalLevel);
        setTimeout(toggleTanggalLevel, 300); // untuk nilai lama
    }
});

// upload file
document.addEventListener('DOMContentLoaded', function() {
    const attachmentInput = document.getElementById('attachment');
    const statusSelect    = document.getElementById('status');
    const acceptedOption  = document.getElementById('opt-accepted');
    const verifiedOption  = document.querySelector('option[value="verified"]');

    const normalAcceptedText   = 'Accepted';
    const disabledAcceptedText = 'Accepted ';

    function toggleStatusOptions() {
        const hasFile = attachmentInput.files.length > 0;

        if (hasFile) {
            // File sudah diupload
            acceptedOption.disabled = false;
            acceptedOption.textContent = normalAcceptedText;
            
            verifiedOption.disabled = true;        // Tutup Verified
        } else {
            // Belum ada file
            acceptedOption.disabled = true;
            acceptedOption.textContent = disabledAcceptedText;
            
            verifiedOption.disabled = false;       // Buka Verified
        }

        // Auto adjust value jika pilihan yang aktif sedang disabled
        const currentValue = statusSelect.value;

        if (hasFile && currentValue === 'verified') {
            // Kalau sudah upload file tapi masih Verified, otomatis pindah ke Accepted
            statusSelect.value = 'accepted';
        } 
        else if (!hasFile && currentValue === 'accepted') {
            // Kalau belum upload tapi memilih Accepted, pindah ke Verified
            statusSelect.value = 'verified';
        }
    }

    // Event listeners
    attachmentInput.addEventListener('change', toggleStatusOptions);
    
    // Jalankan saat halaman load (penting untuk Edit form)
    toggleStatusOptions();
});

document.addEventListener('DOMContentLoaded', function () {

    // ==================== SPP MAPPING ====================
    const sppMapping = @json($sppMapping);
    const golSelect = document.getElementById('bi_gol');
    const kdSelect  = document.getElementById('bi_kd');
    const sppDisplay = document.getElementById('bi_spp_display');
    const sppHidden  = document.getElementById('bi_spp');

    function updateSPP() {
        const gol = golSelect.value?.trim().toUpperCase();
        const kd  = kdSelect.value?.trim().toUpperCase();

        if (gol && kd && sppMapping[gol] && sppMapping[gol][kd] !== undefined) {
            const nilai = sppMapping[gol][kd];
            sppHidden.value = nilai;
            sppDisplay.value = 'Rp ' + new Intl.NumberFormat('id-ID').format(nilai);
        } else {
            sppHidden.value = '';
            sppDisplay.value = '';
        }
    }

    golSelect?.addEventListener('change', updateSPP);
    kdSelect?.addEventListener('change', updateSPP);
    setTimeout(updateSPP, 500);

    // ==================== DUafa / BNF LOGIC (S3B1, S3B2, S3B3) ====================
    const duafaSection = document.getElementById('duafa-bnf-section');

    function toggleDuafaBNF() {
        const gol = golSelect.value?.trim().toUpperCase();
        const trigger = ['S3B1', 'S3B2', 'S3B3', 'D'];

        if (trigger.includes(gol)) {
            duafaSection.style.display = 'block';

            // Auto isi (mirip Buku Induk)
            document.getElementById('periode').value = 'Ke-1';
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('tgl_mulai').value = today;

            const end = new Date();
            end.setMonth(end.getMonth() + 6);
            document.getElementById('tgl_akhir').value = end.toISOString().split('T')[0];

            const mappingBeasiswa = { 'S3B1': 100000, 'S3B2': 200000, 'S3B3': 50000, 'D': 300000 };
            const nominal = mappingBeasiswa[gol] || 0;
            if (nominal) {
                document.getElementById('jumlah_beasiswa').value = nominal * 6;
            }
        } else {
            duafaSection.style.display = 'none';
        }
    }

    golSelect?.addEventListener('change', toggleDuafaBNF);
    setTimeout(toggleDuafaBNF, 600);

    // ==================== TAHAPAN & LEVEL ====================
    const tahapSelect = document.getElementById('tahap');
    const tglTahapanWrapper = document.getElementById('tgl_tahapan_wrapper');

    tahapSelect?.addEventListener('change', function() {
        tglTahapanWrapper.style.display = (this.value === 'Persiapan' || this.value === 'Lanjutan') ? 'block' : 'none';
    });

    const levelSelect = document.getElementById('level');
    const tglLevelWrapper = document.getElementById('tgl_level_wrapper');

    levelSelect?.addEventListener('change', function() {
        tglLevelWrapper.style.display = this.value ? 'block' : 'none';
    });
});
</script>
@endpush
@endsection