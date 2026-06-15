{{-- resources/views/potongan/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Potongan: {{ $potongan->nama }}</h4>
    <table class="table table-bordered">
        <tr><th>Bulan</th><td>{{ $potongan->bulan }}</td></tr>
        <tr><th>NIK</th><td>{{ $potongan->nik }}</td></tr>
        <tr><th>Jabatan</th><td>{{ $potongan->jabatan }}</td></tr>
        <tr><th>Status</th><td>{{ $potongan->status }}</td></tr>
        <tr><th>Departemen</th><td>{{ $potongan->departemen }}</td></tr>
        <tr><th>Masa Kerja</th><td>{{ $potongan->masa_kerja }}</td></tr>

        {{-- Rekap Absensi Bulan Ini --}}
        <tr>
            <th>Jumlah Absen ({{ $potongan->bulan }})</th>
            <td>{{ $totalAbsen }}</td>
        </tr>
        <tr>
            <th>Rincian Absen</th>
            <td>
                <ul class="mb-0">
                    <li>Hadir: {{ $rekap['Hadir'] ?? 0 }}</li>
                    <li>Sakit: {{ $rekap['Sakit'] ?? 0 }}</li>
                    <li>Izin: {{ $rekap['Izin'] ?? 0 }}</li>
                    <li>Alpa: {{ $rekap['Alpa'] ?? 0 }}</li>
                    <li>Datang Terlambat: {{ $rekap['Datang Terlambat'] ?? 0 }}</li>
                    <li>Tidak Aktif: {{ $rekap['Tidak Aktif'] ?? 0 }}</li>
                    @php
                        // Tampilkan status tak terduga lainnya
                        $known = ['Hadir','Sakit','Izin','Alpa','Datang Terlambat','Tidak Aktif'];
                    @endphp
                    @foreach($rekap as $status => $jml)
                        @if(!in_array($status, $known))
                            <li>{{ $status }}: {{ $jml }}</li>
                        @endif
                    @endforeach
                </ul>
            </td>
        </tr>

        <tr><th>Sakit (potongan)</th><td>{{ $potongan->sakit }}</td></tr>
        <tr><th>Izin (potongan)</th><td>{{ $potongan->izin }}</td></tr>
        <tr><th>Alpa (potongan)</th><td>{{ $potongan->alpa }}</td></tr>
        <tr><th>Tidak Aktif (potongan)</th><td>{{ $potongan->tidak_aktif }}</td></tr>
        <tr><th>Kelebihan (potongan)</th><td>{{ $potongan->kelebihan }}</td></tr>
        <tr><th>Lain-lain (potongan)</th><td>{{ $potongan->lain_lain }}</td></tr>
         {{-- Cash Advance --}}
        <tr>
            <th>Cash Advance (nominal)</th>
            <td>
                @if(!empty($potongan->cash_advance_nominal) && $potongan->cash_advance_nominal > 0)
                    Rp {{ number_format($potongan->cash_advance_nominal, 0, ',', '.') }}
                @else
                    -
                @endif
            </td>
        </tr>
        <tr>
            <th>Cash Advance (catatan)</th>
            <td>{{ $potongan->cash_advance_note ?? '-' }}</td>
        </tr>
        <tr><th>Total (potongan)</th><td>{{ $potongan->total }}</td></tr>
    </table>

    <a href="{{ route('potongan.index') }}" class="btn btn-secondary">Kembali</a>
    <a href="{{ route('potongan.edit', $potongan) }}" class="btn btn-primary">Edit</a>
</div>
@endsection
