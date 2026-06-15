@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Tambah Transaksi Petty Cash</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pettycash.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label>No Bukti</label>
                <input type="text" name="no_bukti" class="form-control" value="{{ old('no_bukti') }}" required>
            </div>

            <div class="mb-3">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}" required>
            </div>

            {{-- 🔹 biMBA Unit --}}
            <div class="mb-3">
                <label>biMBA Unit</label>
                <input type="text" class="form-control" value="{{ auth()->user()->bimba_unit
        ?? auth()->user()->unit?->biMBA_unit
        ?? '-' }}" readonly>
            </div>

            <div class="mb-3">
    <label>No Cabang</label>
    <input type="text" class="form-control"
           value="{{ $currentUnit->no_cabang ?? '-' }}"
           readonly>
</div>

            {{-- hidden agar tetap terkirim ke controller --}}
            <input type="hidden" name="bimba_unit" value="{{ auth()->user()->bimba_unit
        ?? auth()->user()->unit?->biMBA_unit }}">

            <input type="hidden" name="no_cabang" value="{{ auth()->user()->no_cabang
        ?? auth()->user()->unit?->no_cabang }}">


            <div class="mb-3">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($kategoris as $kategori)
                        <option value="{{ $kategori }}" {{ old('kategori') == $kategori ? 'selected' : '' }}>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Keterangan</label>
                <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}" required>
            </div>

            <div class="mb-3">
                <label>Debit</label>
                <input type="number" name="debit" class="form-control" value="{{ old('debit', 0) }}">
            </div>

            <div class="mb-3">
                <label>Kredit</label>
                <input type="number" name="kredit" class="form-control" value="{{ old('kredit', 0) }}">
            </div>

            <div class="mb-3">
                <label>Bukti (jpg, png, pdf)</label>
                <input type="file" name="bukti" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('pettycash.index') }}" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectUnit = document.getElementById('bimba_unit');
            const noCabangInput = document.getElementById('no_cabang');

            if (!selectUnit || !noCabangInput) return;

            function syncNoCabang() {
                const opt = selectUnit.options[selectUnit.selectedIndex];
                if (!opt) {
                    noCabangInput.value = '';
                    return;
                }
                const noCabang = opt.getAttribute('data-no-cabang') || '';
                noCabangInput.value = noCabang;
            }

            // set awal (kalau old() ada)
            if (selectUnit.options && selectUnit.selectedIndex >= 0) {
                syncNoCabang();
            }

            selectUnit.addEventListener('change', syncNoCabang);
        });
    </script>
@endsection