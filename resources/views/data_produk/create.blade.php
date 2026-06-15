{{-- resources/views/data_produk/create.blade.php --}}

@extends('layouts.app')

@section('title', 'Tambah Rekap Stok Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-plus-circle me-2"></i>
                        Tambah Data Rekap Stok Produk (Manual)
                    </h4>
                </div>
                <div class="card-body p-4">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('data_produk.store') }}" method="POST">
                        @csrf

                        <!-- Periode (Wajib) -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Periode Rekap <span class="text-danger">*</span></label>
                                <input type="month"
                                       name="periode"
                                       class="form-control @error('periode') is-invalid @enderror"
                                       value="{{ old('periode', now()->format('Y-m')) }}"
                                       required>
                                @error('periode')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <form action="{{ route('data_produk.store') }}" method="POST">
    @csrf

    <div class="row mb-4">
    <!-- UNIT -->
    <div class="col-md-5">
        <label class="form-label fw-bold">
            Unit biMBA <span class="text-danger">*</span>
        </label>

        @if(!$isAdmin && $userUnit)

            <!-- USER BIASA -->
            <input type="text"
                   class="form-control bg-light fw-bold text-primary"
                   value="{{ $userUnit->no_cabang }} - {{ $userUnit->biMBA_unit ?? $userUnit->nama }}"
                   readonly>

            <input type="hidden"
                   name="unit_id"
                   value="{{ $userUnit->id }}">

        @else

            <!-- ADMIN / USER TANPA UNIT -->
            <select name="unit_id"
                    class="form-select @error('unit_id') is-invalid @enderror"
                    required>

                <option value="">-- Pilih Unit biMBA --</option>

                @foreach($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->no_cabang }} - {{ $unit->biMBA_unit ?? $unit->nama }}
                    </option>
                @endforeach
            </select>

        @endif

        @error('unit_id')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>

    <!-- Sisanya (tabel produk) tetap sama -->
    <div class="table-responsive">
        ... (kode tabel Anda tetap)
    </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th width="15%">PRODUK</th>
                                        <th width="8%">SATUAN</th>
                                        <th width="10%">HARGA (Rp)</th>
                                        <th width="8%">MIN STOK</th>
                                        <th width="9%">SLD AWAL</th>
                                        <th width="9%">TERIMA</th>
                                        <th width="9%">PAKAI</th>
                                        <th width="9%">SLD AKHIR</th>
                                        <th width="8%">STATUS</th>
                                        <th width="9%">OPNAME</th>
                                        <th width="9%">SELISIH</th>
                                        <th width="12%">NILAI (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <!-- Produk (Dropdown dari master) -->
                                        <td>
                                            <select name="kode"
                                                    class="form-select @error('kode') is-invalid @enderror"
                                                    required>
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach($produks as $p)
                                                    <option value="{{ $p->kode }}"
                                                            data-label="{{ $p->label }}"
                                                            data-jenis="{{ $p->jenis }}"
                                                            data-satuan="{{ $p->satuan }}"
                                                            data-harga="{{ $p->harga }}"
                                                            {{ old('kode') == $p->kode ? 'selected' : '' }}>
                                                        {{ $p->kode }} - {{ $p->label }} ({{ $p->jenis }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kode')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            <!-- Hidden fields untuk jenis & label -->
                                            <input type="hidden" name="jenis" id="jenis">
                                            <input type="hidden" name="label" id="label">
                                        </td>

                                        <!-- Satuan (Readonly dari produk) -->
                                        <td>
                                            <input type="text"
                                                   id="satuan"
                                                   class="form-control bg-light"
                                                   readonly>
                                            <input type="hidden" name="satuan" id="satuan_hidden">
                                        </td>

                                        <!-- Harga (Readonly dari produk) -->
                                        <td>
                                            <input type="number"
                                                   id="harga"
                                                   name="harga"
                                                   class="form-control text-end bg-light"
                                                   readonly>
                                        </td>

                                        <!-- Min Stok -->
                                        <td>
                                            <input type="number"
                                                   name="min_stok"
                                                   class="form-control text-end"
                                                   value="{{ old('min_stok', 10) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Saldo Awal -->
                                        <td>
                                            <input type="number"
                                                   name="sld_awal"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('sld_awal', 0) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Terima -->
                                        <td>
                                            <input type="number"
                                                   name="terima"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('terima', 0) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Pakai -->
                                        <td>
                                            <input type="number"
                                                   name="pakai"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('pakai', 0) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Saldo Akhir (Readonly - Otomatis) -->
                                        <td>
                                            <input type="number"
                                                   id="sld_akhir"
                                                   class="form-control text-end fw-bold bg-light"
                                                   readonly>
                                        </td>

                                        <!-- Status (Readonly - Otomatis) -->
                                        <td>
                                            <div id="status" class="fw-bold fs-5 text-center">
                                                -
                                            </div>
                                        </td>

                                        <!-- Opname Fisik -->
                                        <td>
                                            <input type="number"
                                                   name="opname"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('opname', 0) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Selisih (Readonly - Otomatis) -->
                                        <td>
                                            <input type="number"
                                                   id="selisih"
                                                   class="form-control text-end fw-bold {{ old('selisih', 0) < 0 ? 'text-danger' : 'text-success' }} bg-light"
                                                   readonly>
                                        </td>

                                        <!-- Nilai Rupiah (Readonly - Otomatis) -->
                                        <td>
                                            <input type="number"
                                                   id="nilai"
                                                   class="form-control text-end fw-bold bg-light"
                                                   readonly>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-end">
                            <a href="{{ route('data_produk.index') }}" class="btn btn-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 ms-3">
                                <i class="fas fa-save me-2"></i> Simpan Rekap
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectProduk = document.querySelector('select[name="kode"]');
    const inputs = {
        jenis: document.getElementById('jenis'),
        label: document.getElementById('label'),
        satuan: document.getElementById('satuan'),
        satuanHidden: document.getElementById('satuan_hidden'),
        harga: document.getElementById('harga'),
        sldAwal: document.querySelector('input[name="sld_awal"]'),
        terima: document.querySelector('input[name="terima"]'),
        pakai: document.querySelector('input[name="pakai"]'),
        opname: document.querySelector('input[name="opname"]'),
        minStok: document.querySelector('input[name="min_stok"]'),
        sldAkhir: document.getElementById('sld_akhir'),
        status: document.getElementById('status'),
        selisih: document.getElementById('selisih'),
        nilai: document.getElementById('nilai')
    };

    function hitungSemua() {
        const sldAwal = parseInt(inputs.sldAwal.value) || 0;
        const terima = parseInt(inputs.terima.value) || 0;
        const pakai = parseInt(inputs.pakai.value) || 0;
        const opname = parseInt(inputs.opname.value) || 0;
        const harga = parseFloat(inputs.harga.value) || 0;
        const minStok = parseInt(inputs.minStok.value) || 0;

        const sldAkhir = sldAwal + terima - pakai;
        const selisih = opname - sldAkhir;
        const nilai = opname * harga;

        inputs.sldAkhir.value = sldAkhir;
        inputs.selisih.value = selisih;
        inputs.nilai.value = nilai.toLocaleString('id-ID');

        // Status
        if (sldAkhir >= minStok) {
            inputs.status.innerHTML = '<span class="text-success">STOK AMAN</span>';
        } else {
            inputs.status.innerHTML = '<span class="text-danger">STOK KURANG</span>';
        }

        // Warna selisih
        inputs.selisih.classList.toggle('text-danger', selisih < 0);
        inputs.selisih.classList.toggle('text-success', selisih >= 0);
    }

    // Saat pilih produk
    selectProduk.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            inputs.jenis.value = option.dataset.jenis;
            inputs.label.value = option.dataset.label;
            inputs.satuan.value = option.dataset.satuan;
            inputs.satuanHidden.value = option.dataset.satuan;
            inputs.harga.value = option.dataset.harga;
        } else {
            // Reset jika kosong
            inputs.jenis.value = '';
            inputs.label.value = '';
            inputs.satuan.value = '';
            inputs.satuanHidden.value = '';
            inputs.harga.value = 0;
        }
        hitungSemua();
    });

    // Saat input angka berubah
    document.querySelectorAll('.calc-input, input[name="min_stok"]').forEach(el => {
        el.addEventListener('input', hitungSemua);
    });

    // Inisialisasi saat load (jika old value)
    if (selectProduk.value) {
        selectProduk.dispatchEvent(new Event('change'));
    }
    hitungSemua();
});
</script>
@endsection