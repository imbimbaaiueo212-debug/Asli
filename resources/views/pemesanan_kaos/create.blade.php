@extends('layouts.app')

@section('title', 'Tambah Pemesanan Kaos & Item Lainnya')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Pemesanan Kaos & Item Lainnya</h4>
                </div>
                <div class="card-body">
                    @php
                        $isAdmin = auth()->check() && (auth()->user()->is_admin ?? false);
                        $userUnit = auth()->user()->bimba_unit ?? null;
                        $defaultUnitId = null;
                        $unitDisplay = 'Unit belum diatur';

                        if (!$isAdmin && $userUnit) {
                            $unit = \App\Models\Unit::where('biMBA_unit', $userUnit)->first();
                            if ($unit) {
                                $defaultUnitId = $unit->id;
                                $unitDisplay = $unit->no_cabang . ' | ' . strtoupper($unit->biMBA_unit);
                            }
                        }
                    @endphp

                    @if (!$isAdmin && !$defaultUnitId)
                        <div class="alert alert-danger mb-4 text-center">
                            <strong>Unit Anda belum diatur!</strong><br>
                            Hubungi admin untuk mengatur unit di profile agar bisa menambah pemesanan kaos.
                        </div>
                    @endif

                    <form action="{{ route('pemesanan_kaos.store') }}" method="POST" id="pemesananForm">
                        @csrf

                        <!-- Hidden Unit (otomatis untuk non-admin) -->
                        @if (!$isAdmin)
                            <input type="hidden" name="unit_id" id="unit_id" value="{{ old('unit_id', $defaultUnitId) }}" required>
                        @endif

                        <!-- Unit biMBA – HANYA UNTUK ADMIN -->
                        @if ($isAdmin)
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Unit biMBA <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                        <option value="">- Pilih Unit biMBA -</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Unit asal pemesan</small>
                                </div>
                            </div>
                        @else
                            <!-- Non-admin: tampilkan unit sebagai info saja -->
                            @if ($defaultUnitId)
                                <div class="mb-4 text-center">
                                    <label class="form-label fw-bold d-block">Unit biMBA Anda</label>
                                    <div class="badge bg-primary fs-5 px-4 py-2">
                                        {{ $unitDisplay }}
                                    </div>
                                    <small class="text-muted d-block mt-2">Unit otomatis dari profile Anda (tidak bisa diubah)</small>
                                </div>
                            @endif
                        @endif

                        <!-- Informasi Dasar -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">No Bukti</label>
                                <input type="text" name="no_bukti" class="form-control" value="{{ old('no_bukti') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                       value="{{ old('tanggal') }}" required>
                                @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Informasi Murid -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">NIM</label>
                                <input type="text" id="nim" class="form-control" readonly placeholder="Akan terisi otomatis">
                                <input type="hidden" name="nim" id="nim_hidden">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Murid <span class="text-danger">*</span></label>
                                <input type="text" name="nama_murid" id="nama_murid" class="form-control" readonly placeholder="Akan terisi otomatis">
                            </div>
                        </div>

                        <!-- Info Tambahan dari Buku Induk -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">GOL</label>
                                <input type="text" id="gol" class="form-control" readonly>
                                <input type="hidden" name="gol" id="gol_hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="text" id="tgl_masuk_display" class="form-control" readonly>
                                <input type="hidden" name="tgl_masuk" id="tgl_masuk">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Lama Belajar</label>
                                <input type="text" id="lama_bljr" class="form-control" readonly>
                                <input type="hidden" name="lama_bljr" id="lama_bljr_hidden">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Guru</label>
                                <input type="text" id="guru" class="form-control" readonly>
                                <input type="hidden" name="guru" id="guru_hidden">
                            </div>
                        </div>

                        <!-- Pilih Murid -->
                        <div class="row mb-5">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Pilih Murid dari Buku Induk <span class="text-danger">*</span></label>
                                <select name="murid_id" id="murid_dropdown" class="form-select" required>
                                    <option value="">-- Pilih Murid --</option>
                                </select>
                                @error('murid_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">
                                    Murid aktif dari unit Anda ({{ $unitDisplay ?? 'tidak terdeteksi' }}) akan muncul di sini
                                </small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Pemesanan Kaos -->
                        <h5 class="mb-3 text-primary">Pemesanan Kaos</h5>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Kaos Lengan Pendek (Jumlah)</label>
                                <input type="number" id="kaos" name="kaos" min="0" class="form-control" value="{{ old('kaos', 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kaos Lengan Panjang (Jumlah)</label>
                                <input type="number" id="kaos_panjang" name="kaos_panjang" min="0" class="form-control" value="{{ old('kaos_panjang', 0) }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Ukuran Kaos <span id="size-required" class="text-danger"></span></label>
                                <select name="size" id="size" class="form-select @error('size') is-invalid @enderror">
                                    <option value="">- Tidak Memesan Kaos -</option>
                                    <option value="KAS" {{ old('size') == 'KAS' ? 'selected' : '' }}>KAS</option>
                                    <option value="KAM" {{ old('size') == 'KAM' ? 'selected' : '' }}>KAM</option>
                                    <option value="KAL" {{ old('size') == 'KAL' ? 'selected' : '' }}>KAL</option>
                                    <option value="KAXL" {{ old('size') == 'KAXL' ? 'selected' : '' }}>KAXL</option>
                                    <option value="KAXXL" {{ old('size') == 'KAXXL' ? 'selected' : '' }}>KAXXL</option>
                                    <option value="KAXXXL" {{ old('size') == 'KAXXXL' ? 'selected' : '' }}>KAXXXL</option>
                                    <option value="KAXXXLS" {{ old('size') == 'KAXXXLS' ? 'selected' : '' }}>KAXXXLS</option>
                                </select>
                                @error('size') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted d-block mt-2">
                                    Ukuran ini berlaku untuk <strong>kaos pendek</strong> dan <strong>panjang</strong>.<br>
                                    Contoh: Pilih KAS → Pendek jadi KAS, Panjang jadi KAS01 di rekap.<br>
                                    Jika ingin ukuran berbeda, buat 2 pemesanan terpisah.
                                </small>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Item Tambahan -->
                        <h5 class="mb-3 text-primary">Item Tambahan</h5>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">KPK</label>
                                <input type="number" name="kpk" min="0" class="form-control" value="{{ old('kpk', 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tas</label>
                                <select name="kode_tas" class="form-select" id="kode_tas">
                                    <option value="">-- Tidak pesan tas --</option>
                                    @foreach($tasOptions as $tas)
                                        <option value="{{ $tas->kode }}" {{ old('kode_tas') == $tas->kode ? 'selected' : '' }}>
                                            {{ $tas->label }} - {{ $tas->nama_produk }} (Rp {{ number_format($tas->harga) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('kode_tas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Jumlah Tas</label>
                                <input type="number" name="jumlah_tas" min="0" class="form-control" value="{{ old('jumlah_tas', 1) }}">
                                @error('jumlah_tas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RBAS</label>
                                <input type="number" name="rbas" min="0" class="form-control" value="{{ old('rbas', 0) }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">BCABS01</label>
                                <input type="number" name="bcabs01" min="0" class="form-control" value="{{ old('bcabs01', 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">BCABS02</label>
                                <input type="number" name="bcabs02" min="0" class="form-control" value="{{ old('bcabs02', 0) }}">
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="form-control" id="keterangan">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="text-end mt-5">
                            <a href="{{ route('pemesanan_kaos.index') }}" class="btn btn-secondary btn-lg me-2">Kembali</a>
                            <button type="submit" class="btn btn-success btn-lg">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // === Variabel Utama ===
    const unitIdInput     = document.getElementById('unit_id'); // hidden atau dropdown
    const muridDropdown   = document.getElementById('murid_dropdown');
    const nimInput        = document.getElementById('nim');
    const nimHidden       = document.getElementById('nim_hidden');
    const namaInput       = document.getElementById('nama_murid');
    const golInput        = document.getElementById('gol');
    const golHidden       = document.getElementById('gol_hidden');
    const tglMasukDisplay = document.getElementById('tgl_masuk_display');
    const tglMasukInput   = document.getElementById('tgl_masuk');
    const lamaBljrInput   = document.getElementById('lama_bljr');
    const lamaBljrHidden  = document.getElementById('lama_bljr_hidden');
    const guruInput       = document.getElementById('guru');
    const guruHidden      = document.getElementById('guru_hidden');
    const kaosInput       = document.getElementById('kaos');
    const kaosPanjangInput = document.getElementById('kaos_panjang');
    const sizeSelect      = document.getElementById('size');
    const tanggalInput    = document.querySelector('input[name="tanggal"]');
    const keteranganTextarea = document.getElementById('keterangan');
    const sizeRequiredLabel = document.getElementById('size-required');

    // === Fungsi Hitung Minggu ===
    function hitungMingguKe(tanggalStr) {
        if (!tanggalStr) return '';
        const tanggal = new Date(tanggalStr);
        const hari = tanggal.getDate();
        const mingguKe = Math.ceil(hari / 7);
        return mingguKe <= 5 ? mingguKe : 5;
    }

    // Update Keterangan otomatis
    function updateKeterangan() {
        if (tanggalInput.value) {
            const mingguKe = hitungMingguKe(tanggalInput.value);
            keteranganTextarea.value = `Minggu ke-${mingguKe}`;
        } else {
            keteranganTextarea.value = '';
        }
    }

    // Reset field murid
    function resetMuridFields() {
        muridDropdown.innerHTML = '<option value="">Memuat murid...</option>';
        nimInput.value = ''; nimHidden.value = '';
        namaInput.value = '';
        golInput.value = ''; golHidden.value = '';
        tglMasukDisplay.value = ''; tglMasukInput.value = '';
        lamaBljrInput.value = ''; lamaBljrHidden.value = '';
        guruInput.value = ''; guruHidden.value = '';
    }

    // Update required size berdasarkan jumlah kaos
    function updateSizeRequired() {
        const totalKaos = (parseInt(kaosInput.value) || 0) + (parseInt(kaosPanjangInput.value) || 0);
        if (totalKaos > 0) {
            sizeSelect.required = true;
            sizeRequiredLabel.innerHTML = '*';
        } else {
            sizeSelect.required = false;
            sizeRequiredLabel.innerHTML = '';
            sizeSelect.value = ''; // reset jika tidak pesan kaos
        }
    }

    // Load murid otomatis saat halaman load (pakai unit profile untuk non-admin)
    function loadMurid() {
        const unitId = unitIdInput ? unitIdInput.value : '{{ $defaultUnitId ?? '' }}';

        if (!unitId) {
            muridDropdown.innerHTML = '<option value="">-- Unit tidak terdeteksi --</option>';
            return;
        }

        const url = '{{ route('pemesanan_kaos.murid', ':unit_id') }}'.replace(':unit_id', unitId);
        fetch(url)
            .then(response => response.json())
            .then(murids => {
                muridDropdown.innerHTML = '<option value="">- Pilih Murid -</option>';
                murids.forEach(murid => {
                    const option = document.createElement('option');
                    option.value = murid.id;
                    option.textContent = murid.display;
                    option.dataset.nim = murid.nim || '';
                    option.dataset.nama = murid.nama || '';
                    option.dataset.gol = murid.gol || '-';
                    option.dataset.tgl_masuk_display = murid.tgl_masuk_display || '-';
                    option.dataset.tgl_masuk = murid.tgl_masuk || '';
                    option.dataset.lama_bljr = murid.lama_bljr || '-';
                    option.dataset.guru = murid.guru || '-';
                    muridDropdown.appendChild(option);
                });
            })
            .catch(err => {
                console.error('Error loading murid:', err);
                muridDropdown.innerHTML = '<option value="">Gagal memuat murid</option>';
            });
    }

    // Saat pilih murid
    muridDropdown.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            nimInput.value = selected.dataset.nim;
            nimHidden.value = selected.dataset.nim;
            namaInput.value = selected.dataset.nama;
            golInput.value = selected.dataset.gol;
            golHidden.value = selected.dataset.gol;
            tglMasukDisplay.value = selected.dataset.tgl_masuk_display;
            tglMasukInput.value = selected.dataset.tgl_masuk;
            lamaBljrInput.value = selected.dataset.lama_bljr;
            lamaBljrHidden.value = selected.dataset.lama_bljr;
            guruInput.value = selected.dataset.guru;
            guruHidden.value = selected.dataset.guru;
        } else {
            resetMuridFields();
        }
    });

    // Event listener untuk tanggal & kaos
    tanggalInput.addEventListener('change', updateKeterangan);
    kaosInput.addEventListener('input', updateSizeRequired);
    kaosPanjangInput.addEventListener('input', updateSizeRequired);

    // Jalankan saat halaman load
    updateKeterangan();
    updateSizeRequired();
    loadMurid(); // load murid otomatis sesuai unit profile
});
</script>
@endpush
@endsection