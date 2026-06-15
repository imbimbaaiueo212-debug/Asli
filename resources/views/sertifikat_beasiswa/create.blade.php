@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Tambah Sertifikat Beasiswa</h3>

    <a href="{{ route('sertifikat-beasiswa.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- PESAN KHUSUS JIKA UNIT KOSONG (NON-ADMIN) --}}
    @if (!auth()->user()->isAdminUser() && empty(auth()->user()->bimba_unit))
        <div class="alert alert-warning">
            <strong>Unit Anda belum diatur!</strong><br>
            Hubungi admin untuk mengisi field "bimba_unit" di profil Anda agar bisa menambahkan sertifikat.
        </div>
    @endif

    {{-- PILIH UNIT (HANYA ADMIN) --}}
    @if(auth()->user()->isAdminUser())
        <form method="GET" action="{{ route('sertifikat-beasiswa.create') }}" class="mb-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Unit</label>
                <select name="bimba_unit" class="form-select" onchange="this.form.submit()" required>
                    <option value="">-- Pilih Unit Terlebih Dahulu --</option>
                    @foreach($listUnit as $value => $label)
                        <option value="{{ $value }}"
                            {{ request('bimba_unit') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    @endif

    {{-- FORM SIMPAN --}}
    <form method="POST" action="{{ route('sertifikat-beasiswa.store') }}">
        @csrf

        {{-- HIDDEN BIMBA_UNIT – SELALU ADA & TERISI OTOMATIS --}}
        <input type="hidden" name="bimba_unit" 
               value="{{ 
                   auth()->user()->isAdminUser() 
                       ? (request('bimba_unit') ?? '') 
                       : (auth()->user()->bimba_unit ?? '') 
               }}">

        {{-- MURID --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Nama Murid <span class="text-danger">*</span></label>
            <select name="murid" id="murid" class="form-select" required>
                <option value="">-- Pilih Murid --</option>
                @foreach($listMurid as $key => $label)
                    <option value="{{ $key }}" {{ old('murid') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            @if(auth()->user()->isAdminUser() && !request('bimba_unit'))
                <small class="text-danger d-block mt-1">
                    Pilih unit terlebih dahulu untuk melihat daftar murid
                </small>
            @endif
        </div>

        {{-- AUTO-FILL FIELDS (readonly) --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" 
                       class="form-control bg-light" value="{{ old('tanggal_lahir') }}" readonly>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Golongan</label>
                <input type="text" name="golongan" id="golongan" 
                       class="form-control bg-light" value="{{ old('golongan') }}" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" id="alamat" class="form-control bg-light" 
                      rows="2" readonly tabindex="-1">{{ old('alamat') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Orang Tua</label>
            <input type="text" name="nama_orang_tua" id="nama_orang_tua" 
                   class="form-control bg-light" value="{{ old('nama_orang_tua') }}" readonly>
        </div>

        {{-- FIELD LAIN --}}
        <div class="mb-3">
            <label class="form-label">Virtual Account</label>
            <input type="text" name="virtual_account" class="form-control" 
                   value="{{ old('virtual_account') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Jumlah Beasiswa <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="jumlah_beasiswa" 
                   class="form-control" value="{{ old('jumlah_beasiswa') }}" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_mulai" class="form-control" 
                       value="{{ old('tanggal_mulai') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_selesai" class="form-control" 
                       value="{{ old('tanggal_selesai') }}" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Periode Bea Ke- <span class="text-danger">*</span></label>
            <input type="text" name="periode_bea_ke" class="form-control" 
                   value="{{ old('periode_bea_ke') }}" required>
        </div>

        {{-- TOMBOL SIMPAN – DISABLE JIKA UNIT KOSONG --}}
        <button type="submit" class="btn btn-success" 
                {{ (auth()->user()->isAdminUser() && !request('bimba_unit')) || empty(auth()->user()->bimba_unit) ? 'disabled' : '' }}>
            Simpan Sertifikat
        </button>
    </form>
</div>

{{-- JS AUTO-FILL MURID DETAIL --}}
<script>
document.getElementById('murid')?.addEventListener('change', function () {
    if (!this.value) return;

    const nim = this.value.split('|')[0].trim();

    fetch(`/ajax/murid-detail/${nim}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            document.getElementById('tanggal_lahir').value   = data.tgl_lahir   ?? '';
            document.getElementById('alamat').value          = data.alamat      ?? '';
            document.getElementById('nama_orang_tua').value  = data.orang_tua   ?? '';
            document.getElementById('golongan').value        = data.golongan    ?? '';
        })
        .catch(err => {
            console.error(err);
            alert('Gagal mengambil detail murid. Coba lagi atau hubungi admin.');
        });
});
</script>
@endsection