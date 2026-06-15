@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Pembayaran Tunjangan</h2>
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

        <form action="{{ route('pembayaran.update', $pembayaran->id) }}" method="POST" id="form-pembayaran">
            @csrf
            @method('PUT')

            {{-- IDENTITAS DASAR --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">NIK</label>
                    <input type="text"
                           name="nik"
                           value="{{ old('nik', $pembayaran->nik) }}"
                           class="form-control"
                           required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama</label>
                    <input type="text"
                           name="nama"
                           value="{{ old('nama', $pembayaran->nama) }}"
                           class="form-control"
                           required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jabatan</label>
                    <input type="text"
                           name="jabatan"
                           value="{{ old('jabatan', $pembayaran->jabatan) }}"
                           class="form-control">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <input type="text"
                           name="status"
                           value="{{ old('status', $pembayaran->status) }}"
                           class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Departemen</label>
                    <input type="text"
                           name="departemen"
                           value="{{ old('departemen', $pembayaran->departemen) }}"
                           class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Masa Kerja (bulan)</label>
                    <input type="number"
                           name="masa_kerja"
                           value="{{ old('masa_kerja', $pembayaran->masa_kerja) }}"
                           class="form-control"
                           min="0">
                    @php
                        $mk = old('masa_kerja', $pembayaran->masa_kerja ?? 0);
                        $y  = intdiv((int)$mk, 12);
                        $m  = ((int)$mk) % 12;
                    @endphp
                    <small class="text-muted">
                        Perkiraan: {{ $y > 0 ? $y.' th ' : '' }}{{ $m }} bln
                    </small>
                </div>
            </div>

            {{-- UNIT & CABANG --}}
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Unit biMBA</label>
                    <input type="text"
                           name="bimba_unit"
                           value="{{ old('bimba_unit', $pembayaran->bimba_unit ?? '') }}"
                           class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Cabang</label>
                    <input type="text"
                           name="no_cabang"
                           value="{{ old('no_cabang', $pembayaran->no_cabang ?? '') }}"
                           class="form-control">
                </div>
            </div>

            {{-- BANK --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">No Rekening</label>
                    <input type="text"
                           name="no_rekening"
                           value="{{ old('no_rekening', $pembayaran->no_rekening) }}"
                           class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bank</label>
                    <input type="text"
                           name="bank"
                           value="{{ old('bank', $pembayaran->bank) }}"
                           class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Atas Nama</label>
                    <input type="text"
                           name="atas_nama"
                           value="{{ old('atas_nama', $pembayaran->atas_nama) }}"
                           class="form-control">
                </div>
            </div>

            {{-- NILAI UANG --}}
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Pendapatan</label>
                    <input type="number"
                           name="pendapatan"
                           id="pendapatan"
                           value="{{ old('pendapatan', $pembayaran->pendapatan) }}"
                           class="form-control"
                           step="0.01"
                           required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Potongan</label>
                    <input type="number"
                           name="potongan"
                           id="potongan"
                           value="{{ old('potongan', $pembayaran->potongan) }}"
                           class="form-control"
                           step="0.01">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Dibayarkan</label>
                    <input type="number"
                           id="dibayarkan"
                           class="form-control"
                           value="{{ $pembayaran->dibayarkan }}"
                           readonly>
                    <small class="text-muted">Otomatis: Pendapatan - Potongan</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pendapatanInput = document.getElementById('pendapatan');
            const potonganInput   = document.getElementById('potongan');
            const dibayarkanInput = document.getElementById('dibayarkan');

            function recalc() {
                const pendapatan = parseFloat(pendapatanInput.value || 0);
                const potongan   = parseFloat(potonganInput.value || 0);
                const dibayarkan = pendapatan - potongan;

                dibayarkanInput.value = isNaN(dibayarkan) ? 0 : dibayarkan;
            }

            pendapatanInput.addEventListener('input', recalc);
            potonganInput.addEventListener('input', recalc);
        });
    </script>
@endsection
