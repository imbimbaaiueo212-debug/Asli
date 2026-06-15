@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Transaksi Petty Cash</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pettycash.update', $pettycash->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>No Bukti</label>
            <input type="text" class="form-control" value="{{ $pettycash->no_bukti }}" disabled>
        </div>

        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control"
                   value="{{ old('tanggal', $pettycash->tanggal) }}" required>
        </div>

        {{-- 🔹 Unit & No Cabang --}}
        <div class="mb-3">
            <label>biMBA Unit</label>
            <select name="bimba_unit" id="bimba_unit" class="form-control">
                <option value="">-- Pilih Unit --</option>
                @foreach($units as $u)
                    <option value="{{ $u->biMBA_unit }}"
                            data-no-cabang="{{ $u->no_cabang ?? '' }}"
                            {{ old('bimba_unit', $pettycash->bimba_unit) == $u->biMBA_unit ? 'selected' : '' }}>
                        {{ strtoupper($u->biMBA_unit) }}
                        @if($u->no_cabang && $u->no_cabang !== '-')
                            ({{ $u->no_cabang }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>No Cabang</label>
            <input type="text" name="no_cabang" id="no_cabang" class="form-control"
                   value="{{ old('no_cabang', $pettycash->no_cabang) }}" readonly>
        </div>

        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="kategori" class="form-control"
                   value="{{ old('kategori', $pettycash->kategori) }}" required>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control"
                   value="{{ old('keterangan', $pettycash->keterangan) }}" required>
        </div>

        <div class="mb-3">
            <label>Debit</label>
            <input type="number" name="debit" class="form-control"
                   value="{{ old('debit', $pettycash->debit) }}">
        </div>

        <div class="mb-3">
            <label>Kredit</label>
            <input type="number" name="kredit" class="form-control"
                   value="{{ old('kredit', $pettycash->kredit) }}">
        </div>

        <div class="mb-3">
            <label>Bukti Baru (jpg, png, pdf)</label>
            <input type="file" name="bukti" class="form-control">

            @if($pettycash->bukti)
                <p class="mt-2">Bukti saat ini: 
                    <a href="{{ asset('storage/bukti/'.$pettycash->bukti) }}" target="_blank">
                        Lihat File
                    </a>
                </p>
            @endif
        </div>

        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('pettycash.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>

{{-- JS kecil untuk auto-isi no_cabang dari pilihan unit --}}
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

    // set awal
    syncNoCabang();

    // update saat ganti unit
    selectUnit.addEventListener('change', syncNoCabang);
});
</script>
@endsection
