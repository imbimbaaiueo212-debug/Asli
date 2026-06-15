@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Data Pindah Golongan</h2>

    <a href="{{ route('pindah-golongan.index') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pindah-golongan.update', $pindahGolongan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- NIM (Buku Induk) -->
        <div class="col-md-12 mb-3">
            <label for="nim" class="form-label">Pilih Murid/Guru (NIM)</label>
            <input type="text" name="nim" id="nim" class="form-control" value="{{ $pindahGolongan->nim }}" readonly>
        </div>

        <div class="row mt-3">
            <!-- Data Lama -->
            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" value="{{ $pindahGolongan->nama }}" readonly>
            </div>

            <div class="col-md-6 mb-3">
                <label for="guru" class="form-label">Guru</label>
                <input type="text" name="guru" id="guru" class="form-control" value="{{ $pindahGolongan->guru }}" readonly>
            </div>

            <div class="col-md-6 mb-3">
                <label for="gol" class="form-label">Golongan Lama</label>
                <input type="text" name="gol" id="gol" class="form-control" value="{{ $pindahGolongan->gol }}" readonly>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kd" class="form-label">Kode Lama</label>
                <input type="text" name="kd" id="kd" class="form-control" value="{{ $pindahGolongan->kd }}" readonly>
            </div>

            <div class="col-md-6 mb-3">
                <label for="spp" class="form-label">SPP Lama</label>
                <input type="text" name="spp" id="spp" class="form-control" value="{{ number_format($pindahGolongan->spp,0,',','.') }}" readonly>
            </div>

            {{-- NEW: Unit & Cabang (dropdown dari Buku Induk) --}}
@php
    $currentUnit = old('bimba_unit', $pindahGolongan->bimba_unit ?? optional($pindahGolongan->bukuInduk)->bimba_unit ?? '');
    $currentCab  = old('no_cabang', $pindahGolongan->no_cabang ?? optional($pindahGolongan->bukuInduk)->no_cabang ?? '');
@endphp

<div class="col-md-6 mb-3">
    <label for="bimba_unit" class="form-label">Unit (biMBA)</label>

    <select name="bimba_unit" id="bimba_unit" class="form-control">
        <option value="">-- Pilih Unit --</option>
        @foreach($units as $unit)
            <option value="{{ $unit }}" {{ $currentUnit === $unit ? 'selected' : '' }}>
                {{ $unit }}
            </option>
        @endforeach

        {{-- Jika unit sekarang tidak ada dalam daftar, tampilkan Lainnya --}}
        @if($currentUnit && !in_array($currentUnit, $units))
            <option value="__other__" selected>Lainnya...</option>
        @else
            <option value="__other__">Lainnya...</option>
        @endif
    </select>

    {{-- Input manual jika user memilih Lainnya --}}
    <input type="text" id="bimba_unit_custom" name="bimba_unit"
           class="form-control mt-2"
           placeholder="Ketik nama unit..."
           value="{{ (!in_array($currentUnit, $units) ? $currentUnit : '') }}"
           style="{{ (!in_array($currentUnit, $units) && $currentUnit) ? '' : 'display:none;' }}">
</div>

<div class="col-md-6 mb-3">
    <label for="no_cabang" class="form-label">No Cabang</label>

    <select name="no_cabang" id="no_cabang" class="form-control">
        <option value="">-- Pilih Cabang --</option>
        {{-- akan diisi otomatis oleh JS --}}
    </select>

    {{-- Input manual untuk cabang jika unit custom --}}
    <input type="text" id="no_cabang_custom" name="no_cabang"
           class="form-control mt-2"
           placeholder="Ketik nomor cabang..."
           value="{{ $currentCab }}"
           style="display:none;">
</div>

            <!-- Data Baru -->
            <div class="col-md-6 mb-3">
                <label for="gol_baru" class="form-label">Golongan Baru</label>
                <select name="gol_baru" id="gol_baru" class="form-control">
                    <option value="">-- Pilih Golongan Baru --</option>
                    @foreach($hargaSaptataruna->unique('kode') as $harga)
                        <option value="{{ $harga->kode }}"
                            {{ $pindahGolongan->gol_baru == $harga->kode ? 'selected' : '' }}>
                            {{ $harga->kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kd_baru" class="form-label">Kode Baru</label>
                <select name="kd_baru" id="kd_baru" class="form-control">
                    <option value="">-- Pilih Kode Baru --</option>
                    @foreach(['A','B','C','D','F'] as $kode)
                        <option value="{{ $kode }}" {{ $pindahGolongan->kd_baru == $kode ? 'selected' : '' }}>
                            {{ $kode }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="spp_baru" class="form-label">SPP Baru</label>
                <input type="text" name="spp_baru" id="spp_baru" class="form-control" value="{{ $pindahGolongan->spp_baru }}" readonly>
            </div>

            <!-- Lain-lain -->
            <div class="col-md-6 mb-3">
                <label for="tanggal_pindah_golongan" class="form-label">Tanggal Pindah Golongan</label>
                <input type="date" name="tanggal_pindah_golongan" id="tanggal_pindah_golongan" class="form-control"
                    value="{{ $pindahGolongan->tanggal_pindah_golongan ? \Carbon\Carbon::parse($pindahGolongan->tanggal_pindah_golongan)->format('Y-m-d') : '' }}">
            </div>

            <div class="col-md-12 mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <input type="text" name="keterangan" id="keterangan" class="form-control" value="{{ $pindahGolongan->keterangan }}">
            </div>

            <div class="col-md-12 mb-3">
                <label for="alasan_pindah" class="form-label">Alasan Pindah Golongan</label>
                <textarea name="alasan_pindah" id="alasan_pindah" rows="3" class="form-control">{{ $pindahGolongan->alasan_pindah }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Update
        </button>
    </form>
</div>

<!-- Auto-fill SPP Baru berdasarkan Golongan & Kode Baru -->
<script>
    // unitMap = { "GRIYA PESONA MADANI": ["05141"], ... }
    var unitMap = @json($unitMap);

    function populateCabang(unit, selected = '') {
        var cabSelect = document.getElementById('no_cabang');
        var cabCustom = document.getElementById('no_cabang_custom');
        cabSelect.innerHTML = '<option value="">-- Pilih Cabang --</option>';

        if (!unit || unit === '__other__' || !unitMap[unit]) {
            cabSelect.style.display = 'none';
            cabCustom.style.display = '';
            cabCustom.value = selected;
            return;
        }

        cabSelect.style.display = '';
        cabCustom.style.display = 'none';

        unitMap[unit].forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if (c === selected) opt.selected = true;
            cabSelect.appendChild(opt);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var unitSel = document.getElementById('bimba_unit');
        var unitCustom = document.getElementById('bimba_unit_custom');

        var initialUnit = "{{ $currentUnit }}";
        var initialCab  = "{{ $currentCab }}";

        // --- INIT ---
        if (initialUnit && !unitMap.hasOwnProperty(initialUnit)) {
            // Custom unit
            unitSel.value = "__other__";
            unitCustom.style.display = '';
            populateCabang("__other__", initialCab);
        } else {
            // Normal unit
            unitSel.value = initialUnit;
            populateCabang(initialUnit, initialCab);
        }

        // --- ON CHANGE UNIT ---
        unitSel.addEventListener('change', function () {
            if (unitSel.value === "__other__") {
                unitCustom.style.display = '';
                unitCustom.value = '';
                populateCabang("__other__", '');
            } else {
                unitCustom.style.display = 'none';
                populateCabang(unitSel.value, '');
            }
        });

        // --- Submit cleanup ---
        document.querySelector('form').addEventListener('submit', function () {
            if (unitSel.value === "__other__") {
                unitSel.disabled = true; // kirim custom
            } else {
                unitCustom.disabled = true; // kirim select
            }

            if (document.getElementById('no_cabang').style.display === 'none') {
                document.getElementById('no_cabang').disabled = true;
            } else {
                document.getElementById('no_cabang_custom').disabled = true;
            }
        });
    });
</script>


@endsection
