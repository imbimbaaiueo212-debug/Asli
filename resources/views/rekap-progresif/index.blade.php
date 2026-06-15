@extends('layouts.app')

@section('title', 'Rekap Progresif')

@section('content')
<div class="container-fluid py-4 card card-body">

    <h2 class="mb-4">Rekap Progresif</h2>

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        {{-- Tombol Tambah hanya Admin --}}
        @if($isAdmin ?? false)
            <a href="{{ route('rekap-progresif.create') }}" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Tambah Data
            </a>
        @endif

        {{-- Form Filter --}}
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small text-muted">Filter Periode</label>
                <input type="month" name="periode" class="form-control" 
                       value="{{ old('periode', $periode ?? '') }}">
            </div>

            <div class="col-auto">
                <label class="form-label small text-muted">Nama</label>
                <select name="nama" class="form-select">
                    <option value="">-- Semua --</option>
                    @foreach($allProfiles ?? [] as $n)
                        <option value="{{ $n }}" {{ ($nama ?? '') == $n ? 'selected' : '' }}>
                            {{ $n }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <button type="submit" class="btn btn-info text-white mt-4">
                    <i class="fas fa-filter me-1"></i> Tampilkan
                </button>
                <a href="{{ route('rekap-progresif.index') }}" class="btn btn-secondary mt-4">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">

        @php
            $canEditDelete = $isAdmin ?? false;
            $isAdminView   = auth()->check() && (auth()->user()->is_admin ?? false);
        @endphp

        <table class="table table-bordered table-hover align-middle" style="min-width:950px;font-size:13px">

            <thead>
                <tr class="text-center" style="background:#cfe8ff;font-weight:600">
                    <th colspan="13">REKAP PROGRESIF</th>
                </tr>

                <tr class="table-light text-center">
                    <th rowspan="2">NO</th>
                    <th rowspan="2">NAMA</th>

                    @if($isAdminView)
                        <th rowspan="2">biMBA UNIT</th>
                        <th rowspan="2">NO CABANG</th>
                    @endif

                    <th rowspan="2">JABATAN</th>
                    <th rowspan="2">STATUS</th>
                    <th rowspan="2">DEPARTEMEN</th>
                    <th rowspan="2">MASA KERJA</th>

                    <th colspan="2">SPP</th>
                    <th rowspan="2">TOTAL FM</th>
                    <th rowspan="2">PROGRESIF</th>
                    <th rowspan="2">KOMISI</th>
                    <th rowspan="2">DIBAYARKAN</th>

                    @if($canEditDelete)
                        <th rowspan="2">AKSI</th>
                    @endif
                </tr>

                <tr class="text-center">
                    <th>biMBA</th>
                    <th>ENGLISH</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rekapProgresifs as $key => $rekap)
                    <tr @if(strtolower($rekap->jabatan ?? '') === 'kepala unit') class="table-warning" @endif>
                        <td class="text-center">{{ $key + 1 }}</td>

                        <td class="fw-medium">{{ $rekap->nama ?? '-' }}</td>

                        @if($isAdminView)
                            <td class="text-center">{{ $rekap->bimba_unit ?? '-' }}</td>
                            <td class="text-center">{{ $rekap->no_cabang ?? '-' }}</td>
                        @endif

                        <td class="text-center">{{ $rekap->jabatan ?? '-' }}</td>
                        <td class="text-center">{{ $rekap->status ?? '-' }}</td>
                        <td class="text-center">{{ $rekap->departemen ?? '-' }}</td>
                        <td class="text-center">{{ $rekap->masa_kerja ?? '-' }}</td>

                        <td class="text-end">{{ number_format($rekap->spp_bimba ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rekap->spp_english ?? 0, 0, ',', '.') }}</td>

                        <td class="text-end">{{ number_format($rekap->total_fm ?? 0, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rekap->progresif ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($rekap->komisi ?? 0, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold text-success">
                            {{ number_format($rekap->dibayarkan ?? 0, 0, ',', '.') }}
                        </td>

                       @if($canEditDelete)
    <td class="text-center">
        <div class="btn-group">
            @if(!empty($rekap->id))
                <!-- EDIT -->
                <a href="{{ route('rekap-progresif.edit', ['rekap_progresif' => $rekap->id]) }}" 
                   class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                </a>

                <!-- DELETE -->
                <form action="{{ route('rekap-progresif.destroy', ['rekap_progresif' => $rekap->id]) }}" 
                      method="POST" style="display:inline;"
                      onsubmit="return confirm('Yakin hapus data {{ addslashes($rekap->nama ?? '') }} ?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @else
                <span class="text-muted small">Belum ada rekap</span>
            @endif
        </div>
    </td>
@endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center py-5 text-muted">
                            Tidak ada data rekap progresif untuk periode tersebut
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection