@extends('layouts.app')

@section('title', 'Edit Pendaftaran')

@section('content')
<div class="container">
    <h2 class="mb-3">Edit Pendaftaran</h2>

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
        $useOld = $errors->any();
        $bi = $registration->bi ?? [];
        if (is_string($bi)) $bi = json_decode($bi, true) ?? [];
        
        $trial = $trial ?? null;
    @endphp

    <div class="card">
        <div class="card-body">
            <form method="POST" 
                  action="{{ route('registrations.update', $registration->id) }}" 
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- IDENTITAS MURID --}}
                <h5 class="mb-3">Identitas Murid</h5>

                <div class="mb-3">
                    <label class="form-label fw-bold">Murid</label>
                    <select name="student_id" class="form-select" required>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}"
                                {{ ($useOld ? old('student_id') : $registration->student_id) == $s->id ? 'selected' : '' }}
                                data-unit="{{ $s->bimba_unit ?? '' }}"
                                data-cabang="{{ $s->no_cabang ?? '' }}"
                                data-nim="{{ $s->nim ?? '' }}"
                                data-nama="{{ $s->nama ?? '' }}"
                                data-tgllahir="{{ $s->tgl_lahir ?? '' }}">
                                {{ $s->nim ?: '—' }} — {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Tempat Lahir</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bi[tempat_lahir]') : ($bi['tempat_lahir'] ?? $biPrefill['tempat_lahir'] ?? '') }}">
                        <input type="hidden" name="bi[tempat_lahir]"
                               value="{{ $useOld ? old('bi[tempat_lahir]') : ($bi['tempat_lahir'] ?? $biPrefill['tempat_lahir'] ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Tanggal Lahir</label>
                        <input type="text" id="displayTglLahir" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bi[tanggal_lahir]') : ($bi['tanggal_lahir'] ?? $biPrefill['tanggal_lahir'] ?? '') }}">
                        <input type="hidden" name="bi[tanggal_lahir]" id="hiddenTglLahir"
                               value="{{ $useOld ? old('bi[tanggal_lahir]') : ($bi['tanggal_lahir'] ?? $biPrefill['tanggal_lahir'] ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-primary">Usia</label>
                        <input type="text" id="displayUsia" class="form-control bg-light" readonly>
                        <input type="hidden" name="bi[usia]" id="hiddenUsia"
                               value="{{ $useOld ? old('bi[usia]') : ($bi['usia'] ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">Nama Orang Tua</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bi[orangtua]') : ($bi['orangtua'] ?? $biPrefill['orangtua'] ?? '') }}">
                        <input type="hidden" name="bi[orangtua]"
                               value="{{ $useOld ? old('bi[orangtua]') : ($bi['orangtua'] ?? $biPrefill['orangtua'] ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">Nomor Telepon</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bi[no_telp]') : ($bi['no_telp'] ?? $biPrefill['no_telp'] ?? '') }}">
                        <input type="hidden" name="bi[no_telp]"
                               value="{{ $useOld ? old('bi[no_telp]') : ($bi['no_telp'] ?? $biPrefill['no_telp'] ?? '') }}">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold text-primary">Alamat Lengkap</label>
                        <input type="text" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bi[alamat]') : ($bi['alamat'] ?? $biPrefill['alamat'] ?? '') }}">
                        <input type="hidden" name="bi[alamat]"
                               value="{{ $useOld ? old('bi[alamat]') : ($bi['alamat'] ?? $biPrefill['alamat'] ?? '') }}">
                    </div>
                </div>

                {{-- UNIT & CABANG --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">Unit biMBA</label>
                        <input type="text" id="displayUnit" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('bimba_unit') : $registration->bimba_unit }}">
                        <input type="hidden" name="bimba_unit" id="hiddenUnit"
                               value="{{ $useOld ? old('bimba_unit') : $registration->bimba_unit }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold text-primary">No Cabang</label>
                        <input type="text" id="displayCabang" class="form-control bg-light" readonly
                               value="{{ $useOld ? old('no_cabang') : $registration->no_cabang }}">
                        <input type="hidden" name="no_cabang" id="hiddenCabang"
                               value="{{ $useOld ? old('no_cabang') : $registration->no_cabang }}">
                    </div>
                </div>

                {{-- STATUS & TANGGAL --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending"  {{ ($useOld ? old('status') : $registration->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ ($useOld ? old('status') : $registration->status) === 'verified' ? 'selected' : '' }}>Verified</option>
                            
                            <!-- ACCEPTED DIBUKA UNTUK SEMUA USER -->
                            <option value="accepted" 
                                    {{ ($useOld ? old('status') : $registration->status) === 'accepted' ? 'selected' : '' }}>
                                Accepted
                            </option>
                            
                            <option value="rejected" {{ ($useOld ? old('status') : $registration->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        
                        @if (!$isAdmin)
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i> Semua user dapat mengubah status ke Accepted
                            </small>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Daftar</label>
                        <input type="date" name="tanggal_daftar" class="form-control"
                               value="{{ $useOld ? old('tanggal_daftar') : optional($registration->tanggal_daftar)->format('Y-m-d') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Mulai KBM</label>
                        <input type="date" name="tanggal_penerimaan" class="form-control"
                               value="{{ $useOld ? old('tanggal_penerimaan') : optional($registration->tanggal_penerimaan)->format('Y-m-d') }}">
                    </div>
                </div>

                <hr class="my-4">

                {{-- DATA BUKU INDUK & JADWAL --}}
                <h5 class="mb-3">Data Buku Induk & Jadwal</h5>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Hari</label>
                        <input type="text" name="bi[hari]" class="form-control"
                               value="{{ $useOld ? old('bi[hari]') : ($bi['hari'] ?? $biPrefill['hari'] ?? '') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Jam</label>
                        <input type="text" name="bi[jam]" class="form-control"
                               value="{{ $useOld ? old('bi[jam]') : ($bi['jam'] ?? $biPrefill['jam'] ?? '') }}">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tahapan</label>
                        <select name="bi[tahap]" class="form-control" id="tahap">
                            <option value="">-- Pilih Tahapan --</option>
                            @foreach ($tahapanOptions as $t)
                                <option value="{{ $t }}" {{ ($useOld ? old('bi.tahap') : ($bi['tahap'] ?? $biPrefill['tahap'] ?? '')) === $t ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="bi[kelas]" class="form-control">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($kelasOptions as $k)
                                <option value="{{ $k }}" {{ ($useOld ? old('bi.kelas') : ($bi['kelas'] ?? $biPrefill['kelas'] ?? '')) === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Guru Pengajar</label>
                        <select name="bi[guru]" class="form-control">
                            <option value="">-- Pilih Guru --</option>
                            @foreach ($guruOptions as $g)
                                <option value="{{ $g }}" {{ ($useOld ? old('bi.guru') : ($bi['guru'] ?? $biPrefill['guru'] ?? '')) == $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Gol</label>
                        <select name="bi[gol]" id="bi_gol" class="form-control">
                            <option value="">-- Pilih Gol --</option>
                            @foreach ($hargaSaptataruna->unique('kode') as $row)
                                @if ($row->kode)
                                    <option value="{{ $row->kode }}" 
                                        {{ ($useOld ? old('bi.gol') : ($bi['gol'] ?? $biPrefill['gol'] ?? '')) === $row->kode ? 'selected' : '' }}>
                                        {{ $row->kode }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">KD</label>
                        <select name="bi[kd]" id="bi_kd" class="form-control">
                            <option value="">-- Pilih KD --</option>
                            @foreach ($kdOptions as $kd)
                                <option value="{{ $kd }}" 
                                    {{ ($useOld ? old('bi.kd') : ($bi['kd'] ?? $biPrefill['kd'] ?? '')) === $kd ? 'selected' : '' }}>
                                    {{ $kd }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">SPP (Rp)</label>
                        <input type="text" id="bi_spp_display" class="form-control bg-light" readonly>
                        <input type="hidden" name="bi[spp]" id="bi_spp"
                               value="{{ $useOld ? old('bi.spp') : ($bi['spp'] ?? $biPrefill['spp'] ?? '') }}">
                    </div>
                </div>

                {{-- Jenis KBM & Level --}}
                <div class="row">
                    <!-- Jenis KBM -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Jenis KBM</label>
                        @php 
                            $val = $bi['jenis_kbm'] ?? old('bi.jenis_kbm') ?? $biPrefill['jenis_kbm'] ?? '';
                        @endphp
                        @if($val)
                            <p class="form-control bg-light border rounded p-2 mb-0 fw-medium">{{ $val }}</p>
                            <input type="hidden" name="bi[jenis_kbm]" value="{{ $val }}">
                        @else
                            <select name="bi[jenis_kbm]" class="form-control">
                                <option value="">-- Pilih Jenis KBM --</option>
                                @foreach($jenisKbmOptions ?? [] as $jk)
                                    <option value="{{ $jk }}" {{ old('bi.jenis_kbm') == $jk ? 'selected' : '' }}>{{ $jk }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Level -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Level</label>
                        @php 
                            $val = $bi['level'] ?? old('bi.level') ?? $biPrefill['level'] ?? '';
                        @endphp
                        @if($val)
                            <p class="form-control bg-light border rounded p-2 mb-0 fw-medium">{{ $val }}</p>
                            <input type="hidden" name="bi[level]" value="{{ $val }}">
                        @else
                            <select name="bi[level]" id="level" class="form-control">
                                @foreach($levelOptions ?? [] as $l)
                                    <option value="{{ $l }}" {{ old('bi.level') == $l ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <!-- Tanggal Level -->
                    <div class="col-md-4 mb-3" id="tgl_level_wrapper">
                        <label class="form-label fw-bold text-success">Tanggal Level</label>
                        @php 
                            $val = $bi['tgl_level'] ?? old('bi.tgl_level') ?? $biPrefill['tgl_level'] ?? '';
                        @endphp
                        @if($val)
                            <p class="form-control bg-light border rounded p-2 mb-0 fw-medium">
                                {{ \Carbon\Carbon::parse($val)->format('d-m-Y') }}
                            </p>
                            <input type="hidden" name="bi[tgl_level]" value="{{ $val }}">
                        @else
                            <input type="date" name="bi[tgl_level]" id="tgl_level" class="form-control">
                        @endif
                    </div>
                </div>

                <hr class="my-4">
                    <h5 class="mb-3">Supply Modul & Keterangan</h5>

                    <div class="row">
                        <!-- Asal Modul -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Asal Modul</label>
                            @php 
                                $val = $bi['asal_modul'] 
                                    ?? old('bi.asal_modul') 
                                    ?? $biPrefill['asal_modul'] 
                                    ?? $registration->asal_modul 
                                    ?? '';
                            @endphp
                            @if($val)
                                <p class="form-control bg-light border rounded p-2 mb-0 fw-medium">{{ $val }}</p>
                                <input type="hidden" name="bi[asal_modul]" value="{{ $val }}">
                            @else
                                <select name="bi[asal_modul]" class="form-control">
                                    <option value="">-- Pilih Asal Modul --</option>
                                    @foreach($asalModulOptions ?? [] as $am)
                                        <option value="{{ $am }}" {{ old('bi.asal_modul') == $am ? 'selected' : '' }}>{{ $am }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <!-- Keterangan Optional -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Keterangan Optional</label>
                            @php 
                                $val = $bi['keterangan_optional'] 
                                    ?? old('bi.keterangan_optional') 
                                    ?? $biPrefill['keterangan_optional'] 
                                    ?? '';
                            @endphp
                            @if($val)
                                <p class="form-control bg-light border rounded p-2 mb-0 fw-medium">{{ $val }}</p>
                                <input type="hidden" name="bi[keterangan_optional]" value="{{ $val }}">
                            @else
                                <input type="text" name="bi[keterangan_optional]" class="form-control"
                                    value="{{ old('bi.keterangan_optional') }}">
                            @endif
                        </div>
                    </div>

                

                {{-- ATTACHMENT --}}
                <div class="mb-4 mt-4">
                    <label class="form-label">Upload Dokumen Baru (Opsional)</label>
                    @if ($registration->attachment_path)
                        <div class="mb-2">
                            <a href="{{ asset('public/storage/' . $registration->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                Lihat File Saat Ini
                            </a>
                        </div>
                    @endif
                    <input type="file" name="attachment" class="form-control" accept=".pdf,image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('registrations.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ==================== AUTO FILL MURID ====================
    const studentSelect = document.getElementById('studentSelect');

    function fillStudentData(option) {
        if (!option) return;

        document.getElementById('displayTempatLahir').value = option.dataset.tempatlahir || '';
        document.getElementById('hiddenTempatLahir').value = option.dataset.tempatlahir || '';

        document.getElementById('displayTglLahir').value = option.dataset.tgllahir || '';
        document.getElementById('hiddenTglLahir').value = option.dataset.tgllahir || '';

        document.getElementById('displayOrangTua').value = option.dataset.orangtua || '';
        document.getElementById('hiddenOrangTua').value = option.dataset.orangtua || '';

        document.getElementById('displayNoTelp').value = option.dataset.notelp || '';
        document.getElementById('hiddenNoTelp').value = option.dataset.notelp || '';

        document.getElementById('displayAlamat').value = option.dataset.alamat || '';
        document.getElementById('hiddenAlamat').value = option.dataset.alamat || '';

        document.getElementById('displayUnit').value = option.dataset.unit || '';
        document.getElementById('hiddenUnit').value = option.dataset.unit || '';

        document.getElementById('displayCabang').value = option.dataset.cabang || '';
        document.getElementById('hiddenCabang').value = option.dataset.cabang || '';

        hitungUsia();
    }

    if (studentSelect) {
        studentSelect.addEventListener('change', function() {
            fillStudentData(this.options[this.selectedIndex]);
        });
        // Load data saat pertama kali
        fillStudentData(studentSelect.options[studentSelect.selectedIndex]);
    }

    // ==================== HITUNG USIA ====================
    function hitungUsia() {
        const tgl = document.getElementById('hiddenTglLahir');
        const display = document.getElementById('displayUsia');
        if (!tgl?.value || !display) return;

        let birth = new Date(tgl.value);
        let today = new Date();
        let years = today.getFullYear() - birth.getFullYear();
        let months = today.getMonth() - birth.getMonth();

        if (months < 0 || (months === 0 && today.getDate() < birth.getDate())) {
            years--; months += 12;
        }

        display.value = `${years} tahun ${months} bulan`;
    }

    // ==================== SPP AUTO FILL ====================
    const mapping = @json($sppMapping ?? []);
    const gol = document.getElementById('bi_gol');
    const kd  = document.getElementById('bi_kd');
    const sppHidden = document.getElementById('bi_spp');
    const sppDisplay = document.getElementById('bi_spp_display');

    function updateSPP() {
        const g = (gol?.value || '').trim().toUpperCase();
        const k = (kd?.value || '').trim().toUpperCase();
        if (mapping[g] && mapping[g][k] !== undefined) {
            const val = parseInt(mapping[g][k]);
            sppHidden.value = val;
            sppDisplay.value = new Intl.NumberFormat('id-ID').format(val);
        }
    }

    if (gol) gol.addEventListener('change', updateSPP);
    if (kd)  kd.addEventListener('change', updateSPP);
    setTimeout(updateSPP, 400);

    // ==================== MONEY FORMAT ====================
    document.querySelectorAll('.money-format').forEach(el => {
        el.addEventListener('input', function () {
            let val = this.value.replace(/\D/g, '');
            if (val) this.value = new Intl.NumberFormat('id-ID').format(parseInt(val));
        });
    });

    // ==================== TOGGLE TANGGAL ====================
    const toggle = (selectId, wrapperId) => {
        const sel = document.getElementById(selectId);
        const wrap = document.getElementById(wrapperId);
        if (!sel || !wrap) return;
        sel.addEventListener('change', () => wrap.style.display = sel.value ? 'block' : 'none');
        setTimeout(() => wrap.style.display = sel.value ? 'block' : 'none', 300);
    };

    toggle('tahap', 'tgl_tahapan_wrapper');
    toggle('level', 'tgl_level_wrapper');

    setTimeout(hitungUsia, 600);
});
</script>
@endpush
@endsection