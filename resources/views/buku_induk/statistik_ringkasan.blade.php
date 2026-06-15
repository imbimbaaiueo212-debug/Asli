@extends('layouts.app')

@section('content')
<style>
    .stat-container {
        background-color: #e7f1ff; 
        padding: 20px;
        border-radius: 8px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    /* Style Filter */
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
    }
    .stat-title-main {
        background: linear-gradient(to bottom, #ffffff, #cce0ff);
        border: 1px solid #b3ccff;
        text-align: center;
        font-weight: bold;
        padding: 8px;
        margin-bottom: 20px;
        font-size: 1rem;
        color: #333;
        border-radius: 4px;
    }
    .unit-badge {
        background-color: #ffffff;
        border: 1px solid #b3ccff;
        padding: 5px 20px;
        display: inline-block;
        margin-bottom: 20px;
        color: #0d6efd;
        font-weight: bold;
        border-radius: 4px;
    }
    .column-title {
        text-align: center;
        font-weight: bold;
        margin-bottom: 15px;
        border-bottom: 2px solid #b3ccff;
        padding-bottom: 8px;
        font-size: 0.95rem;
        color: #444;
    }
    .stat-row {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        gap: 8px;
    }
    .stat-label {
        flex: 1;
        font-size: 0.85rem;
        color: #444;
    }
    .stat-box {
        background: white;
        border: 1px solid #b3ccff;
        width: 65px;
        text-align: center;
        padding: 5px;
        font-size: 0.9rem;
        font-weight: 500;
        border-radius: 4px;
        color: #333;
    }
    .stat-percent {
        background: #f0f5ff;
        border: 1px solid #b3ccff;
        width: 50px;
        text-align: center;
        padding: 5px;
        font-size: 0.8rem;
        color: #333;
        border-radius: 4px;
    }
</style>

<div class="container-fluid py-4">
    <div class="filter-section shadow-sm">
    <form method="GET" class="row g-3 mb-4">

    @php
        // Helper untuk generate opsi bulan-tahun
        $periodeOptions = [];
        foreach ($tahunOptions as $tahun) {
            foreach ($bulanOptions as $bulanKey => $bulanNama) {
                $value = $bulanKey . '-' . $tahun;
                $label = $bulanNama . ' ' . $tahun;
                $periodeOptions[$value] = $label;
            }
        }
    @endphp

<!-- Unit Filter -->
@if($isAdmin)
    <div class="col-md-2">
        <label class="fw-bold">Unit</label>
        <select name="unit_id" class="form-control">
            @foreach($unitOptions as $key => $label)
                <option value="{{ $key }}"
                    {{ ($selectedUnit ?? '') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
@endif

    <!-- Periode Awal -->
    <div class="col-md-5">
        <label class="fw-bold">Periode Awal</label>
        <select id="periode_awal" class="form-control">
            @foreach($periodeOptions as $val => $label)
                <option value="{{ $val }}"
                    {{ ($bulanAwal.'-'.$tahunAwal) == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Periode Akhir -->
    <div class="col-md-5">
        <label class="fw-bold">Periode Akhir</label>
        <select id="periode_akhir" class="form-control">
            @foreach($periodeOptions as $val => $label)
                <option value="{{ $val }}"
                    {{ ($bulanAkhir.'-'.$tahunAkhir) == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Hidden input (yang dikirim ke controller) -->
    <input type="hidden" name="bulan_awal" id="bulan_awal">
    <input type="hidden" name="tahun_awal" id="tahun_awal">
    <input type="hidden" name="bulan_akhir" id="bulan_akhir">
    <input type="hidden" name="tahun_akhir" id="tahun_akhir">

    <!-- Button -->
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Filter</button>
    </div>

</form>
</div>

    <div class="stat-container shadow-sm">
        <div class="stat-title-main">STATISTIK MURID</div>
        
        <div class="unit-badge">
            biMBA-AIUEO {{ $namaUnitTerpilih }}
        </div>

        <div class="row g-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="column-title">Realisasi Murid</div>
                <div class="stat-row">
                    <div class="stat-label">Murid Trial Baru</div>
                    <div class="stat-box">{{ $trialBaru }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Murid Baru</div>
                    <div class="stat-box">{{ $muridBaruCount }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Murid Keluar</div>
                    <div class="stat-box">{{ $muridKeluarCount }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Murid Aktif</div>
                    <div class="stat-box">{{ $muridAktifCount }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Murid Dhuafa</div>
                    <div class="stat-box">{{ $muridDhuafa }}</div>
                </div>
                <div class="stat-row">
                    <div class="stat-label">Murid BNF</div>
                    <div class="stat-box">{{ $muridBNF }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="column-title">Berdasarkan Usia</div>
                @foreach($dataUsia as $item)
                <div class="stat-row">
                    <div class="stat-label">{{ $item['label'] }}</div>
                    <div class="stat-box">{{ $item['jumlah'] }}</div>
                    <div class="stat-percent">{{ $item['persen'] }}%</div>
                </div>
                @endforeach
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="column-title">Lama Belajar</div>
                @foreach($dataLama as $item)
                <div class="stat-row">
                    <div class="stat-label">{{ $item['label'] }}</div>
                    <div class="stat-box">{{ $item['jumlah'] }}</div>
                    <div class="stat-percent">{{ $item['persen'] }}%</div>
                </div>
                @endforeach
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <div class="column-title">Lain-lain</div>
                <div class="stat-row"><div class="stat-label">Tahap Persiapan</div><div class="stat-box">{{ $tahapPersiapan }}</div></div>
                <div class="stat-row"><div class="stat-label">Tahap Lanjutan</div><div class="stat-box">{{ $tahapLanjutan }}</div></div>
                <div class="stat-row"><div class="stat-label">Murid Aktif Kembali</div><div class="stat-box">{{ $aktifKembali }}</div></div>
                <div class="stat-row"><div class="stat-label">Murid Cuti</div><div class="stat-box">{{ $cuti }}</div></div>
                <div class="stat-row"><div class="stat-label">Murid Garansi</div><div class="stat-box">{{ $garansi }}</div></div>
                <div class="stat-row"><div class="stat-label">Murid Pindahan</div><div class="stat-box">{{ $pindahan }}</div></div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    function setHiddenValue(selectId, bulanId, tahunId) {
        const val = document.getElementById(selectId).value;
        if (!val) return;

        const [bulan, tahun] = val.split('-');

        document.getElementById(bulanId).value = bulan;
        document.getElementById(tahunId).value = tahun;
    }

    function updateAll() {
        setHiddenValue('periode_awal', 'bulan_awal', 'tahun_awal');
        setHiddenValue('periode_akhir', 'bulan_akhir', 'tahun_akhir');
    }

    // Saat pertama load
    updateAll();

    // Saat user pilih
    document.getElementById('periode_awal').addEventListener('change', updateAll);
    document.getElementById('periode_akhir').addEventListener('change', updateAll);

    // Submit form (PASTI sudah terisi)
    document.querySelector('form').addEventListener('submit', function () {
        updateAll();
    });

});
</script>
@endsection