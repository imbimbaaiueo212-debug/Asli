@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Data Potongan Tunjangan</h2>

    <form action="{{ route('potongan.update', $potongan->id) }}" method="POST" id="form-potongan">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>NIK</label>
            <input type="text" name="nik" class="form-control" value="{{ $potongan->nik }}" readonly>
        </div>

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ $potongan->nama }}" readonly>
        </div>

        <div class="mb-3">
            <label>Jabatan</label>
            <input type="text" name="jabatan" class="form-control" value="{{ $potongan->jabatan }}" readonly>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <input type="text" name="status" class="form-control" value="{{ $potongan->status }}" readonly>
        </div>

        <div class="mb-3">
            <label>Departemen</label>
            <input type="text" name="departemen" class="form-control" value="{{ $potongan->departemen }}" readonly>
        </div>

        {{-- Unit & Cabang (readonly) --}}
        <div class="mb-3">
            <label>Unit biMBA</label>
            <input type="text" class="form-control"
                   value="{{ $potongan->bimba_unit ?? '-' }}" readonly>
        </div>

        <div class="mb-3">
            <label>No. Cabang</label>
            <input type="text" class="form-control"
                   value="{{ $potongan->no_cabang ?? '-' }}" readonly>
        </div>

        <div class="mb-3">
            <label>Masa Kerja</label>
            {{-- Tipe text agar kompatibel dengan "X tahun Y bulan" --}}
            <input type="text" name="masa_kerja" class="form-control" value="{{ $potongan->masa_kerja }}" readonly>
        </div>

        {{-- Read-only jumlah hari / nominal (sesuaikan app logic) --}}
        <div class="row">
            <div class="col-md-3 mb-3">
                <label>Sakit</label>
                <input type="number" name="sakit" class="form-control" value="{{ $potongan->sakit }}" readonly>
            </div>
            <div class="col-md-3 mb-3">
                <label>Izin</label>
                <input type="number" name="izin" class="form-control" value="{{ $potongan->izin }}" readonly>
            </div>
            <div class="col-md-3 mb-3">
                <label>Alpa</label>
                <input type="number" name="alpa" class="form-control" value="{{ $potongan->alpa }}" readonly>
            </div>
            <div class="col-md-3 mb-3">
                <label>Tidak Aktif</label>
                <input type="number" name="tidak_aktif" class="form-control" value="{{ $potongan->tidak_aktif }}" readonly>
            </div>
        </div>

        {{-- KELEBIHAN --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Kelebihan (Nominal, Rp)</label>
                {{-- fallback ke legacy 'kelebihan' jika 'kelebihan_nominal' kosong --}}
                <input 
                    type="text" 
                    name="kelebihan_nominal" 
                    id="kelebihan_nominal" 
                    class="form-control numeric-format" 
                    value="{{ old('kelebihan_nominal', $potongan->kelebihan_nominal ?? $potongan->kelebihan ?? 0) }}" 
                    placeholder="500.000">
                @error('kelebihan_nominal')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label>Kelebihan Dari Bulan</label>
                <input 
                    type="month" 
                    name="kelebihan_bulan" 
                    class="form-control"
                    value="{{ old('kelebihan_bulan', $potongan->kelebihan_bulan ?? '') }}">
                @error('kelebihan_bulan')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- BULAN --}}
        <div class="mb-3">
            <label>Bulan Potongan</label>
            <input type="text" name="bulan" class="form-control" value="{{ $potongan->bulan }}" readonly>
        </div>

        {{-- LAIN-LAIN --}}
        <div class="mb-3">
            <label>Lain-lain (Nominal)</label>
            <input type="text" name="lain_lain" id="lain_lain" class="form-control numeric-format" value="{{ old('lain_lain', $potongan->lain_lain ?? 0) }}">
            @error('lain_lain')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- CASH ADVANCE --}}
        <div class="mb-3">
            <label>Cash Advance (Nominal)</label>
            <input type="text" name="cash_advance_nominal" id="cash_advance_nominal" class="form-control numeric-format" value="{{ old('cash_advance_nominal', $potongan->cash_advance_nominal ?? 0) }}">
            @error('cash_advance_nominal')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label>Catatan Cash Advance</label>
            <input type="text" name="cash_advance_note" class="form-control" value="{{ old('cash_advance_note', $potongan->cash_advance_note) }}">
            @error('cash_advance_note')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        {{-- TOTAL --}}
        <div class="mb-3">
            <label>Total Potongan (Nominal)</label>
            <input type="text" name="total" id="total" class="form-control numeric-format" value="{{ old('total', $potongan->total ?? 0) }}">
            @error('total')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
            <div class="form-text small">
                Total dihitung otomatis: Kelebihan + Lain-lain + Cash Advance + (sakit/izin/alpa/tidak_aktif jika nilainya berupa nominal).
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('potongan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

{{-- JS: format angka ribuan & auto-calc total --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ambil elemen
    const keleInput  = document.getElementById('kelebihan_nominal');
    const lainInput  = document.getElementById('lain_lain');
    const cashInput  = document.getElementById('cash_advance_nominal');
    const totalInput = document.getElementById('total');

    // semua input yang punya class .numeric-format akan diformat
    const numericInputs = document.querySelectorAll('.numeric-format');

    function toCleanNumber(str) {
        if (str === null || str === undefined || str === '') return 0;
        // hapus semua non-digit kecuali koma dan titik
        str = String(str).replace(/[^0-9\.,-]/g, '');
        // hilangkan titik ribuan
        str = str.replace(/\./g, '');
        // ubah koma desimal indonesia ke titik
        str = str.replace(/,/g, '.');
        const n = parseFloat(str);
        return isNaN(n) ? 0 : n;
    }

    function formatRupiah(value) {
        let num = toCleanNumber(value);
        return new Intl.NumberFormat('id-ID').format(Math.round(num));
    }

    function updateTotal() {
        const kele = toCleanNumber(keleInput.value);
        const lain = toCleanNumber(lainInput.value);
        const cash = toCleanNumber(cashInput.value);

        let sakit = 0, izin = 0, alpa = 0, tidakAktif = 0;
        try {
            sakit      = toCleanNumber(document.querySelector('input[name="sakit"]').value);
            izin       = toCleanNumber(document.querySelector('input[name="izin"]').value);
            alpa       = toCleanNumber(document.querySelector('input[name="alpa"]').value);
            tidakAktif = toCleanNumber(document.querySelector('input[name="tidak_aktif"]').value);
        } catch (e) {}

        const total = sakit + izin + alpa + tidakAktif + kele + lain + cash;
        totalInput.value = formatRupiah(total);
    }

    // format awal
    numericInputs.forEach(function(inp) {
        inp.value = formatRupiah(inp.value);

        inp.addEventListener('input', function() {
            this.value = formatRupiah(this.value);
            updateTotal();
        });
    });

    updateTotal();

    // sebelum submit: ubah ke angka bersih
    document.getElementById('form-potongan').addEventListener('submit', function() {
        numericInputs.forEach(function(inp) {
            let clean = toCleanNumber(inp.value);
            inp.value = clean;
        });
    });
});
</script>

@endsection
    