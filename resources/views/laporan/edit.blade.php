@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-3">Edit Laporan Bagi Hasil</h2>

    <form action="{{ route('laporan.update', $laporan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>No Cabang</label>
                <input type="text" name="no_cabang" class="form-control" value="{{ $laporan->no_cabang }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>biMBA Unit</label>
                <input type="text" name="bimba_unit" class="form-control" value="{{ $laporan->bimba_unit }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Bulan</label>
                <input type="month" name="bulan" class="form-control" value="{{ \Carbon\Carbon::parse($laporan->bulan)->format('Y-m') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nama Bank</label>
                <input type="text" name="nama_bank" class="form-control" value="{{ $laporan->nama_bank }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>No Rekening</label>
                <input type="text" name="no_rekening" class="form-control" value="{{ $laporan->no_rekening }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Atas Nama</label>
                <input type="text" name="atas_nama" class="form-control" value="{{ $laporan->atas_nama }}" required>
            </div>
        </div>

        <h5>biMBA-AIUEO</h5>
        <div class="row">
            @foreach([
                'bimba_murid_aktif_lalu','bimba_murid_baru','bimba_murid_kembali','bimba_murid_keluar',
                'bimba_murid_aktif_ini','bimba_murid_dhuafa','bimba_murid_bnf','bimba_murid_garansi',
                'bimba_murid_deposit','bimba_murid_piutang','bimba_murid_wajib_spp','bimba_murid_bayar_spp',
                'bimba_murid_belum_bayar','bimba_total_penerimaan_spp','bimba_persentase_bagi_hasil','bimba_jumlah_bagi_hasil'
            ] as $field)
            <div class="col-md-3 mb-3">
                <label>{{ ucwords(str_replace('_',' ',$field)) }}</label>
                <input type="number" name="{{ $field }}" class="form-control" value="{{ $laporan->$field }}">
            </div>
            @endforeach
        </div>

        <h5>English biMBA</h5>
        <div class="row">
            @foreach([
                'eng_murid_aktif_lalu','eng_murid_baru','eng_murid_kembali','eng_murid_keluar',
                'eng_murid_aktif_ini','eng_murid_dhuafa','eng_murid_bnf','eng_murid_garansi',
                'eng_murid_deposit','eng_murid_piutang','eng_murid_wajib_spp','eng_murid_bayar_spp',
                'eng_murid_belum_bayar','eng_total_penerimaan_spp','eng_persentase_bagi_hasil','eng_jumlah_bagi_hasil'
            ] as $field)
            <div class="col-md-3 mb-3">
                <label>{{ ucwords(str_replace('_',' ',$field)) }}</label>
                <input type="number" name="{{ $field }}" class="form-control" value="{{ $laporan->$field }}">
            </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('laporan.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
