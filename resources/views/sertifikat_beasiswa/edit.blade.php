@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Edit Sertifikat Beasiswa</h3>

    <a href="{{ route('sertifikat-beasiswa.index') }}"
       class="btn btn-secondary mb-3">Kembali</a>

    {{-- ================= ERROR ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sertifikat-beasiswa.update', $beasiswa->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- ================= UNIT (READONLY) ================= --}}
        <div class="mb-3">
            <label>Nama Unit</label>
            <input type="text"
                   class="form-control bg-light"
                   value="{{ $beasiswa->bimba_unit }}"
                   readonly>
            <input type="hidden" name="bimba_unit" value="{{ $beasiswa->bimba_unit }}">
        </div>

        {{-- ================= MURID ================= --}}
        <div class="mb-3">
            <label>Nama Murid</label>
            <select name="murid" id="murid" class="form-select" required>
                @foreach($listMurid as $key => $label)
                    <option value="{{ $key }}"
                        {{ old('murid', $beasiswa->nim.' | '.$beasiswa->nama) == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= READONLY AUTO-FILL ================= --}}
        <div class="mb-3">
            <label>Tanggal Lahir</label>
            <input type="date"
                   name="tanggal_lahir"
                   id="tanggal_lahir"
                   class="form-control bg-light"
                   value="{{ old('tanggal_lahir', optional($beasiswa->tanggal_lahir)->format('Y-m-d')) }}"
                   readonly>
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat"
                      id="alamat"
                      class="form-control bg-light"
                      readonly
                      tabindex="-1">{{ old('alamat', $beasiswa->alamat) }}</textarea>
        </div>

        <div class="mb-3">
            <label>Nama Orang Tua</label>
            <input type="text"
                   name="nama_orang_tua"
                   id="nama_orang_tua"
                   class="form-control bg-light"
                   value="{{ old('nama_orang_tua', $beasiswa->nama_orang_tua) }}"
                   readonly>
        </div>

        <div class="mb-3">
            <label>Golongan</label>
            <input type="text"
                   name="golongan"
                   id="golongan"
                   class="form-control bg-light"
                   value="{{ old('golongan', $beasiswa->golongan) }}"
                   readonly>
        </div>

        {{-- ================= FIELD YANG BOLEH DIUBAH ================= --}}
        <div class="mb-3">
            <label>Virtual Account</label>
            <input type="text"
                   name="virtual_account"
                   class="form-control"
                   value="{{ old('virtual_account', $beasiswa->virtual_account) }}">
        </div>

        <div class="mb-3">
            <label>Jumlah Beasiswa <span class="text-danger">*</span></label>
            <input type="number"
                   step="0.01"
                   name="jumlah_beasiswa"
                   class="form-control"
                   value="{{ old('jumlah_beasiswa', $beasiswa->jumlah_beasiswa) }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Tanggal Mulai <span class="text-danger">*</span></label>
            <input type="date"
                   name="tanggal_mulai"
                   class="form-control"
                   value="{{ old('tanggal_mulai', optional($beasiswa->tanggal_mulai)->format('Y-m-d')) }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Tanggal Selesai <span class="text-danger">*</span></label>
            <input type="date"
                   name="tanggal_selesai"
                   class="form-control"
                   value="{{ old('tanggal_selesai', optional($beasiswa->tanggal_selesai)->format('Y-m-d')) }}"
                   required>
        </div>

        <div class="mb-3">
            <label>Periode Bea Ke-</label>
            <input type="text"
                   name="periode_bea_ke"
                   class="form-control"
                   value="{{ old('periode_bea_ke', $beasiswa->periode_bea_ke) }}">
        </div>

        <button type="submit" class="btn btn-primary">
            Update
        </button>
    </form>
</div>

{{-- ================= JS AUTO-FILL (OPTIONAL, KALAU GANTI MURID) ================= --}}
<script>
document.getElementById('murid')?.addEventListener('change', function () {
    if (!this.value) return;

    const nim = this.value.split('|')[0].trim();

    fetch(`/ajax/murid-detail/${nim}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('tanggal_lahir').value = data.tgl_lahir ?? '';
            document.getElementById('alamat').value        = data.alamat ?? '';
            document.getElementById('nama_orang_tua').value= data.orang_tua ?? '';
            document.getElementById('golongan').value      = data.golongan ?? '';
        });
});
</script>
@endsection
