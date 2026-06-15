@extends('layouts.app')

@section('title', 'Bagi Hasil')

@section('content')
<div class="container">
    <h2 class="mb-3">Laporan Bagi Hasil</h2>
    <a href="{{ route('laporan.create') }}" class="btn btn-primary mb-3">Tambah Laporan</a>

    <div class="table-responsive">
    <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th rowspan="2">No Cabang</th>
                <th rowspan="2">biMBA Unit</th>
                <th rowspan="2">Bulan</th>
                <th rowspan="2">Nama Bank</th>
                <th rowspan="2">No Rekening</th>
                <th rowspan="2">Atas Nama</th>

                <th colspan="16">biMBA-AIUEO</th>
                <th colspan="16">English biMBA</th>

                <th rowspan="2">Aksi</th>
            </tr>
            <tr>
                {{-- biMBA-AIUEO --}}
                <th>Murid Aktif Bulan Lalu</th>
                <th>Murid Baru Bulan Ini</th>
                <th>Murid Aktif Kembali</th>
                <th>Murid Keluar Bulan Ini</th>
                <th>Murid Aktif Bulan Ini</th>
                <th>Murid Dhuafa</th>
                <th>Murid BNF</th>
                <th>Murid Garansi</th>
                <th>Murid Deposit</th>
                <th>Murid Piutang</th>
                <th>Murid Yang Harus Bayar SPP</th>
                <th>Murid Yang Sudah Bayar SPP</th>
                <th>Murid Yang Belum Bayar SPP</th>
                <th>Total Penerimaan SPP</th>
                <th>% Bagi Hasil</th>
                <th>Jumlah Bagi Hasil</th>

                {{-- English biMBA --}}
                <th>Murid Aktif Bulan Lalu</th>
                <th>Murid Baru Bulan Ini</th>
                <th>Murid Aktif Kembali</th>
                <th>Murid Keluar Bulan Ini</th>
                <th>Murid Aktif Bulan Ini</th>
                <th>Murid Dhuafa</th>
                <th>Murid BNF</th>
                <th>Murid Garansi</th>
                <th>Murid Deposit</th>
                <th>Murid Piutang</th>
                <th>Murid Yang Harus Bayar SPP</th>
                <th>Murid Yang Sudah Bayar SPP</th>
                <th>Murid Yang Belum Bayar SPP</th>
                <th>Total Penerimaan SPP</th>
                <th>% Bagi Hasil</th>
                <th>Jumlah Bagi Hasil</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $row)
            <tr>
                <td>{{ $row->no_cabang }}</td>
                <td>{{ $row->bimba_unit }}</td>
                <td>{{ \Carbon\Carbon::parse($row->bulan)->format('F Y') }}</td>
                <td>{{ $row->nama_bank }}</td>
                <td>{{ $row->no_rekening }}</td>
                <td>{{ $row->atas_nama }}</td>

                {{-- biMBA-AIUEO --}}
                <td>{{ $row->bimba_murid_aktif_lalu }}</td>
                <td>{{ $row->bimba_murid_baru }}</td>
                <td>{{ $row->bimba_murid_kembali }}</td>
                <td>{{ $row->bimba_murid_keluar }}</td>
                <td>{{ $row->bimba_murid_aktif_ini }}</td>
                <td>{{ $row->bimba_murid_dhuafa }}</td>
                <td>{{ $row->bimba_murid_bnf }}</td>
                <td>{{ $row->bimba_murid_garansi }}</td>
                <td>{{ $row->bimba_murid_deposit }}</td>
                <td>{{ $row->bimba_murid_piutang }}</td>
                <td>{{ $row->bimba_murid_wajib_spp }}</td>
                <td>{{ $row->bimba_murid_bayar_spp }}</td>
                <td>{{ $row->bimba_murid_belum_bayar }}</td>
                <td>{{ number_format($row->bimba_total_penerimaan_spp) }}</td>
                <td>{{ $row->bimba_persentase_bagi_hasil }}%</td>
                <td>{{ number_format($row->bimba_jumlah_bagi_hasil) }}</td>

                {{-- English biMBA --}}
                <td>{{ $row->eng_murid_aktif_lalu }}</td>
                <td>{{ $row->eng_murid_baru }}</td>
                <td>{{ $row->eng_murid_kembali }}</td>
                <td>{{ $row->eng_murid_keluar }}</td>
                <td>{{ $row->eng_murid_aktif_ini }}</td>
                <td>{{ $row->eng_murid_dhuafa }}</td>
                <td>{{ $row->eng_murid_bnf }}</td>
                <td>{{ $row->eng_murid_garansi }}</td>
                <td>{{ $row->eng_murid_deposit }}</td>
                <td>{{ $row->eng_murid_piutang }}</td>
                <td>{{ $row->eng_murid_wajib_spp }}</td>
                <td>{{ $row->eng_murid_bayar_spp }}</td>
                <td>{{ $row->eng_murid_belum_bayar }}</td>
                <td>{{ number_format($row->eng_total_penerimaan_spp) }}</td>
                <td>{{ $row->eng_persentase_bagi_hasil }}%</td>
                <td>{{ number_format($row->eng_jumlah_bagi_hasil) }}</td>

                <td>
                    <a href="{{ route('laporan.edit', $row->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                    <form action="{{ route('laporan.destroy', $row->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
