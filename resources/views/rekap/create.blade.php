@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Tambah Rekap Absensi</h3>

    <form action="{{ route('rekap.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label>NIK</label>
                <input type="text" name="nik" id="nik" class="form-control" placeholder="Akan otomatis terisi" readonly>
            </div>

            <div class="col-md-4">
                <label>Nama Relawan <span class="text-danger">*</span></label>
                <input type="text" name="nama_relawan" id="nama_relawan" class="form-control" required 
                       placeholder="Ketik nama relawan" autocomplete="off">
                <small class="text-muted">Ketik nama → data Unit & Cabang otomatis muncul</small>
            </div>

            <div class="col-md-2">
                <label>Unit biMBA</label>
                <input type="text" name="bimba_unit" id="bimba_unit" class="form-control" readonly placeholder="Otomatis">
            </div>

            <div class="col-md-2">
                <label>No Cabang</label>
                <input type="text" name="no_cabang" id="no_cabang" class="form-control" readonly placeholder="Otomatis">
            </div>

            <div class="col-md-1 text-center mt-4">
                <button type="button" id="clear_nama" class="btn btn-sm btn-outline-danger" title="Clear">
                    Clear
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label>Jabatan</label>
                <input type="text" name="jabatan" id="jabatan" class="form-control" placeholder="Otomatis dari Profile">
            </div>
            <div class="col-md-6">
                <label>Departemen</label>
                <input type="text" name="departemen" id="departemen" class="form-control" placeholder="Otomatis dari Profile">
            </div>
        </div>

        <hr>

        <h5 class="mt-4">SR (Senin - Rabu - Jumat)</h5>
        <div class="row">
            @foreach([108,109,110,111,112,113,114,115,116] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="srj_{{ $kode }}" class="form-control form-control-sm" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <h5 class="mt-4">SKS (Selasa - Kamis - Sabtu)</h5>
        <div class="row">
            @foreach([206,207,208,209,210,211] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="sks_{{ $kode }}" class="form-control form-control-sm" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <h5 class="mt-4">S6 (Senin - Selasa - Rabu - Jumat - Sabtu)</h5>
        <div class="row">
            @foreach([306,307,308,309,310,311] as $kode)
            <div class="col-md-1 mb-2">
                <input type="number" name="s6_{{ $kode }}" class="form-control form-control-sm" placeholder="{{ $kode }}">
            </div>
            @endforeach
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <label>Jumlah Murid</label>
                <input type="number" name="jumlah_murid" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Jumlah Rombim</label>
                <input type="number" name="jumlah_rombim" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Penyesuaian RB</label>
                <input type="number" name="penyesuaian_rb" class="form-control" value="0">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success btn-lg">Simpan Rekap</button>
            <a href="{{ route('rekap.index') }}" class="btn btn-secondary btn-lg">Kembali</a>
        </div>
    </form>
</div>

{{-- SCRIPT OTOMATIS ISI DATA DARI PROFILE --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const namaInput = document.getElementById('nama_relawan');
    const nikInput = document.getElementById('nik');
    const unitInput = document.getElementById('bimba_unit');
    const cabangInput = document.getElementById('no_cabang');
    const jabatanInput = document.getElementById('jabatan');
    const departemenInput = document.getElementById('departemen');
    const clearBtn = document.getElementById('clear_nama');

    namaInput.addEventListener('input', function () {
        const nama = this.value.trim();
        if (nama.length < 3) {
            clearFields();
            return;
        }

        fetch(`/api/cari-profile?nama=${encodeURIComponent(nama)}`)
            .then(response => response.json())
            .then(data => {
                if (data.nama) {
                    nikInput.value = data.nik || '';
                    unitInput.value = data.bimba_unit || '';
                    cabangInput.value = data.no_cabang || '';
                    jabatanInput.value = data.jabatan || 'Guru';
                    departemenInput.value = data.departemen || '';
                } else {
                    clearFields();
                }
            })
            .catch(() => clearFields());
    });

    clearBtn.addEventListener('click', function () {
        namaInput.value = '';
        clearFields();
        namaInput.focus();
    });

    function clearFields() {
        nikInput.value = '';
        unitInput.value = '';
        cabangInput.value = '';
        jabatanInput.value = '';
        departemenInput.value = '';
    }
});
</script>
@endsection