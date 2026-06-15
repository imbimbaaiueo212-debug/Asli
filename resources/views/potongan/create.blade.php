@extends('layouts.app')

@section('title', 'Tambah Data Potongan Tunjangan')

@section('content')
<div class="container">
    <h2 class="mb-4">Tambah Data Potongan Tunjangan</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('potongan.store') }}" method="POST">
        @csrf

        <!-- Pilih Pegawai -->
        <div class="mb-3">
            <label class="form-label fw-bold">Pegawai <span class="text-danger">*</span></label>
            <select name="pendapatan_id" id="pendapatan_id" class="form-select" onchange="fillData()" required>
                <option value="">-- Pilih Pegawai --</option>
                @foreach($pendapatans as $pendapatan)
                    <option 
                        value="{{ $pendapatan->id }}" 
                        data-nik="{{ $pendapatan->nik ?? '' }}"
                        data-jabatan="{{ $pendapatan->jabatan ?? '' }}"
                        data-status="{{ $pendapatan->status ?? '' }}"
                        data-departemen="{{ $pendapatan->departemen ?? '' }}"
                        data-masa_kerja="{{ $pendapatan->masa_kerja ?? '' }}"
                        {{-- Tambahan: unit & cabang --}}
                        data-bimba_unit="{{ $pendapatan->bimba_unit ?? '' }}"
                        data-no_cabang="{{ $pendapatan->no_cabang ?? '' }}"
                        {{ old('pendapatan_id') == $pendapatan->id ? 'selected' : '' }}>
                        {{ $pendapatan->nama ?? 'Nama tidak ada' }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Hidden input nama (tetap ada) -->
        <input type="hidden" name="nama" id="nama">

        <!-- Field otomatis -->
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">NIK</label>
                <input type="text" name="nik" id="nik" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Status</label>
                <input type="text" name="status" id="status" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Departemen</label>
                <input type="text" name="departemen" id="departemen" class="form-control" readonly>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Masa Kerja</label>
                <input type="text" name="masa_kerja_display" id="masa_kerja" class="form-control" readonly>
            </div>
        </div>

        {{-- BIMBA UNIT & NO CABANG – HANYA TAMPIL JIKA ADMIN --}}
        @if (auth()->user()->isAdminUser())
            <div class="row g-3 mt-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Unit biMBA</label>
                    <input type="text" id="bimba_unit" name="bimba_unit" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">No. Cabang</label>
                    <input type="text" id="no_cabang" name="no_cabang" class="form-control">
                </div>
            </div>
        @else
            {{-- Non-admin: simpan otomatis via hidden (tidak muncul di tampilan) --}}
            <input type="hidden" id="bimba_unit" name="bimba_unit">
            <input type="hidden" id="no_cabang" name="no_cabang">
        @endif

        <hr class="my-4">

        <h5 class="text-primary">Potongan Tunjangan</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Sakit</label>
                <input type="number" name="sakit" class="form-control" value="{{ old('sakit', 0) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Izin</label>
                <input type="number" name="izin" class="form-control" value="{{ old('izin', 0) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Alpa</label>
                <input type="number" name="alpa" class="form-control" value="{{ old('alpa', 0) }}">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-bold">Tidak Aktif</label>
                <input type="number" name="tidak_aktif" class="form-control" value="{{ old('tidak_aktif', 0) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Kelebihan</label>
                <input type="number" step="0.01" name="kelebihan" class="form-control" value="{{ old('kelebihan', 0) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Lain-lain</label>
                <input type="number" step="0.01" name="lain_lain" class="form-control" value="{{ old('lain_lain', 0) }}">
            </div>

            <!-- Pilih Bulan (opsional) -->
            <div class="col-md-6">
                <label class="form-label fw-bold">Bulan</label>
                <select name="bulan" class="form-select">
                    <option value="">-- Pilih Bulan (opsional) --</option>
                    @php
                        $bulanList = [
                            '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
                            '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
                            '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember',
                        ];
                        $selected = old('bulan', '');
                    @endphp
                    @foreach($bulanList as $key => $value)
                        <option value="{{ $key }}" {{ $selected == $key ? 'selected' : '' }}>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold">Total</label>
                <input type="number" step="0.01" name="total" class="form-control" value="{{ old('total', 0) }}">
            </div>
        </div>

        <div class="mt-5 text-end">
            <button type="submit" class="btn btn-success btn-lg px-5">Simpan Potongan</button>
            <a href="{{ route('potongan.index') }}" class="btn btn-outline-secondary btn-lg px-5 ms-3">Batal</a>
        </div>
    </form>
</div>

<script>
function fillData() {
    const select = document.getElementById('pendapatan_id');
    const selected = select.options[select.selectedIndex];

    if (!selected || !selected.value) {
        // Reset semua field jika tidak ada yang dipilih
        document.getElementById('nik').value         = '';
        document.getElementById('jabatan').value     = '';
        document.getElementById('status').value      = '';
        document.getElementById('departemen').value  = '';
        document.getElementById('masa_kerja').value  = '';
        document.getElementById('nama').value        = '';
        document.getElementById('bimba_unit').value  = '';
        document.getElementById('no_cabang').value   = '';
        return;
    }

    // Hitung masa kerja
    const totalBulan = parseInt(selected.dataset.masa_kerja) || 0;
    const tahun      = Math.floor(totalBulan / 12);
    const bulan      = totalBulan % 12;
    const masaFormat = (tahun > 0 ? tahun + ' tahun ' : '') + bulan + ' bulan';

    // Isi field
    document.getElementById('nik').value         = selected.dataset.nik || '';
    document.getElementById('jabatan').value     = selected.dataset.jabatan || '';
    document.getElementById('status').value      = selected.dataset.status || '';
    document.getElementById('departemen').value  = selected.dataset.departemen || '';
    document.getElementById('masa_kerja').value  = masaFormat;
    document.getElementById('nama').value        = selected.text.trim();

    // Isi bimba_unit & no_cabang (untuk hidden atau visible)
    const unit   = selected.dataset.bimba_unit || '';
    const cabang = selected.dataset.no_cabang || '';

    document.getElementById('bimba_unit').value = unit;
    document.getElementById('no_cabang').value  = cabang;
}

// Auto isi kalau ada old value
document.addEventListener('DOMContentLoaded', function () {
    fillData();
});
</script>
@endsection