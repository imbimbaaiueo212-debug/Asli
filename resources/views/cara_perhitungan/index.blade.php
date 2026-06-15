@extends('layouts.app')

@section('title', 'Cara Perhitungan Progresif')

@section('content')
<div class="card card-body">
    <h2>Cara Perhitungan</h2>

    {{-- ===================== RUMUS ===================== --}}
    <div class="mb-4">
        <h5>A. RUMUS</h5>
        <pre>
PROGRESSIVE = FM biMBA + FM BEA & GARANSI biMBA + FM ENGLISH
            = (Total SPP biMBA : Tarif S3 biMBA) x 1,17 + (Murid MDF + BNF + MGRS) x 1 + (Total SPP English : Tarif S3 English)
            = Total FM  -----> Lihat Tabel FM
            = Nilai Uang Progressive

KOMISI = KOMISI MB/MT biMBA + KOMISI MB/MT ENGLISH + KOMISI ASISTEN KU
       = (Jumlah MB x Rp 50.000) + (Jumlah MT x Rp 30.000) + Rp 800.000
       = Total Komisi

DIBAYARKAN = NILAI UANG PROGRESSIVE + TOTAL KOMISI
(Pada Tgl 15)
        </pre>
    </div>

    <div class="row g-4">
        {{-- ===================== GURU ===================== --}}
        <div class="col-md-4">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th colspan="2">Guru</th>
                    </tr>
                    <tr>
                        <th>Total FM</th>
                        <th>Tarif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($caraPerhitungan->where('kategori', 'Guru') as $item)
                        <tr>
                            <td>{{ $item->range_fm }}</td>
                            <td>{{ number_format($item->tarif, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ===================== KEPALA UNIT ===================== --}}
        <div class="col-md-4">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-info">
                    <tr>
                        <th colspan="2">Kepala Unit</th>
                    </tr>
                    <tr>
                        <th>Total FM</th>
                        <th>Tarif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($caraPerhitungan->where('kategori', 'Kepala Unit') as $item)
                        <tr>
                            <td>{{ $item->range_fm }}</td>
                            <td>{{ number_format($item->tarif, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ===================== KEPALA UA ===================== --}}
        <div class="col-md-4">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead class="table-warning">
                    <tr>
                        <th colspan="2">Kepala UA</th>
                    </tr>
                    <tr>
                        <th>Total FM</th>
                        <th>Tarif</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($caraPerhitungan->where('kategori', 'Kepala UA') as $item)
                        <tr>
                            <td>{{ $item->range_fm }}</td>
                            <td>{{ number_format($item->tarif, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
