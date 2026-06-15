@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Data Pendapatan Tunjangan</h1>

        <a href="{{ route('pendapatan-tunjangan.index') }}" class="btn btn-secondary mb-3">Kembali</a>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pendapatan-tunjangan.update', $pendapatan->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nama Karyawan (readonly) --}}
            <div class="mb-3">
                <label>Nama Karyawan</label>
                <input type="text" class="form-control"
                    value="{{ optional($pendapatan->profile)->nama ?? $pendapatan->nama }}" readonly>
            </div>

            {{-- NIK (readonly) --}}
            <div class="mb-3">
                <label>NIK</label>
                <input type="text" class="form-control"
                    value="{{ optional($pendapatan->profile)->nik ?? $pendapatan->nik }}" readonly>
            </div>

            {{-- Jabatan (readonly) --}}
            <div class="mb-3">
                <label>Jabatan</label>
                <input type="text" class="form-control" value="{{ $pendapatan->jabatan }}" readonly>
            </div>

            {{-- Status (readonly) --}}
            <div class="mb-3">
                <label>Status</label>
                <input type="text" class="form-control" value="{{ $pendapatan->status }}" readonly>
            </div>

            {{-- Departemen (readonly) --}}
            <div class="mb-3">
                <label>Departemen</label>
                <input type="text" class="form-control" value="{{ $pendapatan->departemen }}" readonly>
            </div>

            {{-- Unit biMBA (readonly) --}}
            <div class="mb-3">
                <label>Unit biMBA</label>
                <input type="text" class="form-control"
                    value="{{ $pendapatan->bimba_unit ?? optional($pendapatan->profile)->bimba_unit ?? optional($pendapatan->profile)->nama_unit ?? '-' }}"
                    readonly>
            </div>

            {{-- No. Cabang (readonly) --}}
            <div class="mb-3">
                <label>No. Cabang</label>
                <input type="text" class="form-control"
                    value="{{ $pendapatan->no_cabang ?? optional($pendapatan->profile)->no_cabang ?? optional($pendapatan->profile)->kode_cabang ?? '-' }}"
                    readonly>
            </div>

            {{-- Masa Kerja (readonly, format dari model) --}}
            <div class="mb-3">
                <label>Masa Kerja</label>
                <input type="text" class="form-control" value="{{ $pendapatan->masa_kerja_format }}" readonly>
            </div>

            {{-- THP (readonly, dihitung dari Skim) --}}
            <div class="mb-3">
                <label>THP</label>
                <input type="number" name="thp" class="form-control"
                    value="{{ old('thp', $pendapatan->thp) }}" readonly>
            </div>

            <div class="mb-3">
                <label>Kerajinan</label>
                <input type="number" name="kerajinan" class="form-control"
                    value="{{ old('kerajinan', $pendapatan->kerajinan) }}" step="0.01" min="0">
            </div>

            <div class="mb-3">
                <label>English</label>
                <input type="number" name="english" class="form-control"
                    value="{{ old('english', $pendapatan->english) }}" step="0.01" min="0">
            </div>

            <div class="mb-3">
                <label>Mentor</label>
                <input type="number" name="mentor" class="form-control"
                    value="{{ old('mentor', $pendapatan->mentor) }}" step="0.01" min="0">
            </div>

            {{-- START: PERUBAHAN KEKURANGAN + BULAN KEKURANGAN --}}
            <div class="mb-3">
                <label>Pembayaran Kekurangan</label>
                <div class="input-group">
                    {{-- Input Nominal Kekurangan --}}
                    <input type="number" name="kekurangan" class="form-control @error('kekurangan') is-invalid @enderror"
                        value="{{ old('kekurangan', $pendapatan->kekurangan) }}" step="0.01" min="0" placeholder="Nominal">
                    
                    {{-- Dropdown Bulan Kekurangan --}}
                    @php
                        $months = [
                            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                        ];
                        $selectedMonth = old('bulan_kekurangan', $pendapatan->bulan_kekurangan ?? null);
                    @endphp
                    <select name="bulan_kekurangan" id="bulan_kekurangan" 
                            class="form-select @error('bulan_kekurangan') is-invalid @enderror">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach($months as $month)
                            <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>

                    @error('kekurangan')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                    @error('bulan_kekurangan')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
            {{-- END: PERUBAHAN KEKURANGAN + BULAN KEKURANGAN --}}

            {{-- Bulan pakai input month (Tetap dipertahankan) --}}
            <div class="mb-3">
                <label>Bulan (Gaji)</label>
                <input type="month" name="bulan" class="form-control"
                    value="{{ old('bulan', $pendapatan->bulan) }}" readonly>
            </div>

            <div class="mb-3">
                <label>Tunjangan Keluarga</label>
                <input type="number" name="tj_keluarga" class="form-control"
                    value="{{ old('tj_keluarga', $pendapatan->tj_keluarga) }}" step="0.01" min="0">
            </div>

            <div class="mb-3">
                <label>Lain-lain</label>
                <input type="number" name="lain_lain" class="form-control"
                    value="{{ old('lain_lain', $pendapatan->lain_lain) }}" step="0.01" min="0">
            </div>

            <button type="submit" class="btn btn-success">Update</button>
        </form>
    </div>
@endsection
