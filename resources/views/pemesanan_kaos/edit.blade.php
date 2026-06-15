@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Edit Pemesanan Kaos & Item Lainnya</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('pemesanan_kaos.update', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Informasi Dasar -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">No Bukti</label>
                                <input type="text" name="no_bukti" class="form-control" 
                                       value="{{ old('no_bukti', $order->no_bukti) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                                       value="{{ old('tanggal', $order->tanggal ? \Illuminate\Support\Carbon::parse($order->tanggal)->format('Y-m-d') : '') }}" required>
                                @error('tanggal') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <!-- Unit biMBA -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Unit biMBA <span class="text-danger">*</span></label>
                                <select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                    <option value="">- Pilih Unit biMBA -</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}"
                                            {{ old('unit_id', $order->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">
                                    @if(auth()->user()->is_admin)
                                        Admin dapat mengubah unit.
                                    @else
                                        Anda hanya dapat mengedit data unit sendiri.
                                    @endif
                                </small>
                            </div>
                        </div>

                        <!-- Informasi Murid (NIM & Nama) -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">NIM</label>
                                <input type="text" name="nim" id="nim" class="form-control" readonly 
                                       value="{{ old('nim', $order->nim) }}">
                                <input type="hidden" name="nim" id="nim_hidden" value="{{ old('nim', $order->nim) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Murid <span class="text-danger">*</span></label>
                                <input type="text" name="nama_murid" id="nama_murid" class="form-control" readonly 
                                       value="{{ old('nama_murid', $order->nama_murid) }}">
                            </div>
                        </div>

                        <!-- Informasi Tambahan dari Buku Induk -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">GOL</label>
                                <input type="text" name="gol" id="gol" class="form-control" readonly 
                                       value="{{ old('gol', $order->gol) }}">
                                <input type="hidden" name="gol" id="gol_hidden" value="{{ old('gol', $order->gol) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <input type="text" id="tgl_masuk_display" class="form-control" readonly 
                                       value="{{ old('tgl_masuk', $order->tgl_masuk ? \Carbon\Carbon::parse($order->tgl_masuk)->format('d-m-Y') : '-') }}">
                                <input type="hidden" name="tgl_masuk" id="tgl_masuk" 
                                       value="{{ old('tgl_masuk', $order->tgl_masuk) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Lama Belajar</label>
                                <input type="text" name="lama_bljr" id="lama_bljr" class="form-control" readonly 
                                       value="{{ old('lama_bljr', $order->lama_bljr) }}">
                                <input type="hidden" name="lama_bljr" id="lama_bljr_hidden" value="{{ old('lama_bljr', $order->lama_bljr) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Guru</label>
                                <input type="text" name="guru" id="guru" class="form-control" readonly 
                                       value="{{ old('guru', $order->guru) }}">
                                <input type="hidden" name="guru" id="guru_hidden" value="{{ old('guru', $order->guru) }}">
                            </div>
                        </div>

                        <!-- Dropdown Pilih Murid -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Pilih Murid dari Buku Induk <span class="text-danger">*</span></label>
                                <select name="murid_id" id="murid_dropdown" class="form-select" required>
                                    <option value="">- Pilih Unit biMBA dulu -</option>
                                </select>
                                <small class="text-muted">Dropdown akan memuat murid aktif dari unit terpilih</small>
                            </div>
                        </div>

                        <hr>

                        <!-- Pemesanan Kaos -->
                        <h5 class="mb-3">Pemesanan Kaos</h5>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Kaos Lengan Pendek (Jumlah)</label>
                                <input type="number" id="kaos" name="kaos" min="0" class="form-control" 
                                       value="{{ old('kaos', $order->kaos ?? 0) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kaos Lengan Panjang (Jumlah)</label>
                                <input type="number" id="kaos_panjang" name="kaos_panjang" min="0" class="form-control" 
                                       value="{{ old('kaos_panjang', $order->kaos_panjang ?? 0) }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Ukuran Kaos</label>
                                <select name="size" id="size" class="form-select @error('size') is-invalid @enderror">
                                    <option value="">- Tidak Memesan Kaos -</option>
                                    <option value="KAS" {{ old('size', $order->size) == 'KAS' ? 'selected' : '' }}>KAS</option>
                                    <option value="KAM" {{ old('size', $order->size) == 'KAM' ? 'selected' : '' }}>KAM</option>
                                    <option value="KAL" {{ old('size', $order->size) == 'KAL' ? 'selected' : '' }}>KAL</option>
                                    <option value="KAXL" {{ old('size', $order->size) == 'KAXL' ? 'selected' : '' }}>KAXL</option>
                                    <option value="KAXXL" {{ old('size', $order->size) == 'KAXXL' ? 'selected' : '' }}>KAXXL</option>
                                    <option value="KAXXXL" {{ old('size', $order->size) == 'KAXXXL' ? 'selected' : '' }}>KAXXXL</option>
                                    <option value="KAXXXLS" {{ old('size', $order->size) == 'KAXXXLS' ? 'selected' : '' }}>KAXXXLS</option>
                                </select>
                                @error('size') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">
                                    Ukuran berlaku untuk kaos pendek dan panjang dalam entri ini.<br>
                                    Jika perlu ukuran berbeda, buat entri pemesanan baru.<br>
                                    <strong>Kosongkan jika tidak memesan kaos.</strong>
                                </small>
                            </div>
                        </div>

                        <hr>

                        <!-- Item Tambahan -->
                        <h5 class="mb-3">Item Tambahan</h5>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">KPK</label>
                                <input type="number" name="kpk" min="0" class="form-control" 
                                       value="{{ old('kpk', $order->kpk ?? 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">TASMBA</label>
                                <input type="number" name="tas" min="0" class="form-control" 
                                       value="{{ old('tas', $order->tas ?? 0) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">RBAS</label>
                                <input type="number" name="rbas" min="0" class="form-control" 
                                       value="{{ old('rbas', $order->rbas ?? 0) }}">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">BCABS01</label>
                                <input type="number" name="bcabs01" min="0" class="form-control" 
                                       value="{{ old('bcabs01', $order->bcabs01 ?? 0) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">BCABS02</label>
                                <input type="number" name="bcabs02" min="0" class="form-control" 
                                       value="{{ old('bcabs02', $order->bcabs02 ?? 0) }}">
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-4">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" rows="4" class="form-control">{{ old('keterangan', $order->keterangan) }}</textarea>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('pemesanan_kaos.index') }}" class="btn btn-secondary btn-lg">Kembali</a>
                            <button type="submit" class="btn btn-warning btn-lg">Update Data</button>
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
    const unitSelect         = document.getElementById('unit_id');
    const muridDropdown      = document.getElementById('murid_dropdown');
    const nimInput           = document.getElementById('nim');
    const nimHidden          = document.getElementById('nim_hidden');
    const namaInput          = document.getElementById('nama_murid');
    const golInput           = document.getElementById('gol');
    const golHidden          = document.getElementById('gol_hidden');
    const tglMasukDisplay    = document.getElementById('tgl_masuk_display');
    const tglMasukInput      = document.getElementById('tgl_masuk');
    const lamaBelajarInput   = document.getElementById('lama_bljr');
    const lamaBljrHidden     = document.getElementById('lama_bljr_hidden');
    const guruInput          = document.getElementById('guru');
    const guruHidden         = document.getElementById('guru_hidden');
    const kaosInput          = document.getElementById('kaos');
    const kaosPanjangInput   = document.getElementById('kaos_panjang');
    const sizeSelect         = document.getElementById('size');
    const tanggalInput       = document.getElementById('tanggal');
    const keteranganTextarea = document.getElementById('keterangan');

    // Data existing dari order
    const existingNim = '{{ addslashes(trim($order->nim ?? '')) }}';
    const existingNama = '{{ addslashes($order->nama_murid ?? '') }}';
    const existingGol = '{{ addslashes($order->gol ?? '') }}';
    const existingTglMasukDisplay = '{{ $order->tgl_masuk ? \Carbon\Carbon::parse($order->tgl_masuk)->format('d-m-Y') : '-' }}';
    const existingTglMasuk = '{{ $order->tgl_masuk ?? '' }}';
    const existingLamaBljr = '{{ addslashes($order->lama_bljr ?? '') }}';
    const existingGuru = '{{ addslashes($order->guru ?? '') }}';

    // Fungsi hitung minggu ke berapa dalam bulan
    function hitungMingguKe(tanggalStr) {
        if (!tanggalStr) return '';
        const tanggal = new Date(tanggalStr);
        const hari = tanggal.getDate();
        const mingguKe = Math.ceil(hari / 7);
        return mingguKe <= 5 ? mingguKe : 5;
    }

    // Update keterangan otomatis berdasarkan tanggal
    function updateKeterangan() {
        if (tanggalInput.value) {
            const mingguKe = hitungMingguKe(tanggalInput.value);
            const autoText = `Minggu ke-${mingguKe}`;

            // Jika keterangan masih default (hanya minggu ke-X), update otomatis
            // Jika user sudah tambah catatan lain, biarkan saja
            if (keteranganTextarea.value === '' || 
                keteranganTextarea.value.match(/^Minggu ke-[1-5]$/)) {
                keteranganTextarea.value = autoText;
            }
        }
    }

    // Reset field murid
    function resetMuridFields() {
        muridDropdown.innerHTML = '<option value="">Memuat murid...</option>';
        nimInput.value = '';
        nimHidden.value = '';
        namaInput.value = '';
        golInput.value = '';
        golHidden.value = '';
        tglMasukDisplay.value = '';
        tglMasukInput.value = '';
        lamaBelajarInput.value = '';
        lamaBljrHidden.value = '';
        guruInput.value = '';
        guruHidden.value = '';
    }

    // Update ukuran kaos
    function updateSizeSelect() {
        const totalKaos = (parseInt(kaosInput.value) || 0) + (parseInt(kaosPanjangInput.value) || 0);
        if (totalKaos === 0) {
            sizeSelect.value = '';
        }
    }

    // Load murid dari unit
    function loadMurid(unitId, preselectNim = null) {
        resetMuridFields();

        if (unitId) {
            const url = '{{ route('pemesanan_kaos.murid', ':unit_id') }}'.replace(':unit_id', unitId);
            fetch(url)
                .then(response => response.json())
                .then(murids => {
                    muridDropdown.innerHTML = '<option value="">- Pilih Murid -</option>';

                    let found = false;

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

                        if (preselectNim && murid.nim && murid.nim.trim() === preselectNim.trim()) {
                            option.selected = true;
                            fillMuridFields(murid);
                            found = true;
                        }

                        muridDropdown.appendChild(option);
                    });

                    if (!found && preselectNim) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = existingNim + ' | ' + existingNama + ' (Tidak ditemukan di unit ini)';
                        option.selected = true;
                        option.disabled = true;
                        muridDropdown.appendChild(option);

                        nimInput.value = existingNim;
                        nimHidden.value = existingNim;
                        namaInput.value = existingNama;
                        golInput.value = existingGol;
                        golHidden.value = existingGol;
                        tglMasukDisplay.value = existingTglMasukDisplay;
                        tglMasukInput.value = existingTglMasuk;
                        lamaBelajarInput.value = existingLamaBljr;
                        lamaBljrHidden.value = existingLamaBljr;
                        guruInput.value = existingGuru;
                        guruHidden.value = existingGuru;
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    muridDropdown.innerHTML = '<option value="">Gagal memuat murid</option>';
                });
        } else {
            nimInput.value = existingNim;
            nimHidden.value = existingNim;
            namaInput.value = existingNama;
            golInput.value = existingGol;
            golHidden.value = existingGol;
            tglMasukDisplay.value = existingTglMasukDisplay;
            tglMasukInput.value = existingTglMasuk;
            lamaBelajarInput.value = existingLamaBljr;
            lamaBljrHidden.value = existingLamaBljr;
            guruInput.value = existingGuru;
            guruHidden.value = existingGuru;
        }
    }

    // Fungsi isi field murid
    function fillMuridFields(murid) {
        nimInput.value = murid.nim || '';
        nimHidden.value = murid.nim || '';
        namaInput.value = murid.nama || '';
        golInput.value = murid.gol || '-';
        golHidden.value = murid.gol || '-';
        tglMasukDisplay.value = murid.tgl_masuk_display || '-';
        tglMasukInput.value = murid.tgl_masuk || '';
        lamaBelajarInput.value = murid.lama_bljr || '-';
        lamaBljrHidden.value = murid.lama_bljr || '-';
        guruInput.value = murid.guru || '-';
        guruHidden.value = murid.guru || '-';
    }

    // Event listeners
    unitSelect.addEventListener('change', function () {
        loadMurid(this.value, existingNim);
    });

    muridDropdown.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        if (selected.value && selected.dataset.nim) {
            const murid = {
                nim: selected.dataset.nim,
                nama: selected.dataset.nama,
                gol: selected.dataset.gol,
                tgl_masuk_display: selected.dataset.tgl_masuk_display,
                tgl_masuk: selected.dataset.tgl_masuk,
                lama_bljr: selected.dataset.lama_bljr,
                guru: selected.dataset.guru
            };
            fillMuridFields(murid);
        }
    });

    tanggalInput.addEventListener('change', updateKeterangan);
    kaosInput.addEventListener('input', updateSizeSelect);
    kaosPanjangInput.addEventListener('input', updateSizeSelect);

    // Preload saat halaman dibuka
    const currentUnitId = '{{ old('unit_id', $order->unit_id) }}';
    if (currentUnitId) {
        unitSelect.value = currentUnitId;
        loadMurid(currentUnitId, existingNim);
    } else {
        nimInput.value = existingNim;
        nimHidden.value = existingNim;
        namaInput.value = existingNama;
        golInput.value = existingGol;
        golHidden.value = existingGol;
        tglMasukDisplay.value = existingTglMasukDisplay;
        tglMasukInput.value = existingTglMasuk;
        lamaBelajarInput.value = existingLamaBljr;
        lamaBljrHidden.value = existingLamaBljr;
        guruInput.value = existingGuru;
        guruHidden.value = existingGuru;
    }

    updateSizeSelect();
    updateKeterangan(); // Set keterangan awal berdasarkan tanggal existing
});
</script>
@endpush
@endsection