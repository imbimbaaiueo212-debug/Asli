@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Pembayaran Tunjangan</h2>
    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pembayaran.store') }}" method="POST">
        @csrf

        {{-- IDENTITAS DASAR --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">NIK</label>
                <input type="text"
                       name="nik"
                       class="form-control"
                       value="{{ old('nik') }}">
            </div>
            <div class="col-md-5">
                <label class="form-label">Nama</label>
                <input type="text"
                       name="nama"
                       class="form-control"
                       value="{{ old('nama') }}"
                       required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Jabatan</label>
                <input type="text"
                       name="jabatan"
                       class="form-control"
                       value="{{ old('jabatan') }}">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <input type="text"
                       name="status"
                       class="form-control"
                       value="{{ old('status') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Departemen</label>
                <input type="text"
                       name="departemen"
                       class="form-control"
                       value="{{ old('departemen') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Masa Kerja (dalam bulan)</label>
                <input type="number"
                       name="masa_kerja"
                       class="form-control"
                       min="0"
                       value="{{ old('masa_kerja', $defaultMasaKerja ?? 0) }}">
                <small class="text-muted">
                    @php
                        $mk = old('masa_kerja', $defaultMasaKerja ?? 0);
                        $y  = intdiv((int)$mk, 12);
                        $m  = ((int)$mk) % 12;
                    @endphp
                    Perkiraan: {{ $y > 0 ? $y.' th ' : '' }}{{ $m }} bln
                </small>
            </div>
        </div>

        {{-- PERIODE GAJI --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Bulan Gaji <span class="text-danger">*</span></label>
                @php
                    $listBulan = [
                        'Januari','Februari','Maret','April','Mei','Juni',
                        'Juli','Agustus','September','Oktober','November','Desember'
                    ];
                    $oldBulan = old('bulan');
                @endphp
                <select name="bulan" class="form-select" required>
                    <option value="">-- Pilih Bulan --</option>
                    @foreach($listBulan as $b)
                        <option value="{{ $b }}" {{ $oldBulan == $b ? 'selected' : '' }}>
                            {{ $b }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Bisa diisi nama bulan (misal: Oktober)</small>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun</label>
                <input type="number"
                       name="tahun"
                       class="form-control"
                       value="{{ old('tahun', now()->year) }}">
            </div>
            <div class="col-md-5">
                <label class="form-label">Tanggal Masuk (opsional)</label>
                <input type="date"
                       name="tgl_masuk"
                       class="form-control"
                       value="{{ old('tgl_masuk') }}">
                <small class="text-muted">
                    Jika masa kerja kosong, sistem akan hitung dari tanggal masuk s.d. akhir bulan gaji.
                </small>
            </div>
        </div>

        {{-- INFO BANK --}}
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">No Rekening</label>
                <input type="text"
                       name="no_rekening"
                       class="form-control"
                       value="{{ old('no_rekening') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Bank</label>
                <input type="text"
                       name="bank"
                       class="form-control"
                       value="{{ old('bank') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Atas Nama</label>
                <input type="text"
                       name="atas_nama"
                       class="form-control"
                       value="{{ old('atas_nama') }}">
            </div>
        </div>

        {{-- NILAI PENDAPATAN & POTONGAN --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Pendapatan (Total)</label>
                <input type="number"
                       name="pendapatan"
                       class="form-control"
                       value="{{ old('pendapatan') }}"
                       step="0.01">
                <small class="text-muted">
                    Jika dikosongkan, sistem akan mengambil dari rekap PendapatanTunjangan (nama + bulan).
                </small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Potongan (Total)</label>
                <input type="number"
                       name="potongan"
                       class="form-control"
                       value="{{ old('potongan') }}"
                       step="0.01">
                <small class="text-muted">
                    Jika dikosongkan, sistem akan mengambil dari PotonganTunjangan (nama + bulan).
                </small>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
