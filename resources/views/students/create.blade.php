@extends('layouts.app')
@section('title','Tambah Murid Baru')

@section('content')

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Formulir Pendaftaran Murid Baru</h4>
        </div>

        <div class="card-body">
            {{-- Error summary (opsional tapi membantu) --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-1">Periksa kembali isian berikut:</div>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('students.store') }}" method="POST" novalidate>
                @csrf

                {{-- Sumber Pendaftaran --}}
                <div class="row g-3 mb-2">
                    <div class="col-md-6">
                        <label for="source" class="form-label">Sumber Pendaftaran</label>
                        <select name="source" id="source" class="form-select @error('source') is-invalid @enderror">
                            {{-- default "direct" --}}
                            <option value="direct" {{ old('source','direct') === 'direct' ? 'selected' : '' }}>
                                Pendaftaran Langsung
                            </option>
                            <option value="trial" {{ old('source') === 'trial' ? 'selected' : '' }}>
                                Dari Trial
                            </option>
                        </select>
                        @error('source')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Nilai valid: <code>direct</code> atau <code>trial</code>.</small>
                    </div>
                </div>

                <div class="row g-3">
                    {{-- Nama Murid (Wajib) --}}
                    <div class="col-md-6">
                        <label for="nama" class="form-label required">Nama Lengkap Murid</label>
                        <input type="text" name="nama" id="nama"
                               class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama') }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kelas --}}
<div class="col-md-6">
    <label for="kelas" class="form-label">Kelas / Jenjang</label>
    <select name="kelas" id="kelas" 
            class="form-select @error('kelas') is-invalid @enderror">
        <option value="">-- Pilih Kelas --</option>
        @foreach ($kelasList as $kelas)
            <option value="{{ $kelas }}" {{ old('kelas') == $kelas ? 'selected' : '' }}>
                {{ $kelas }}
            </option>
        @endforeach
    </select>
    @error('kelas')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


                <div class="row g-3 mt-3">
                    {{-- Tanggal Lahir --}}
                    <div class="col-md-4">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" id="tgl_lahir"
                               class="form-control @error('tgl_lahir') is-invalid @enderror"
                               value="{{ old('tgl_lahir') }}">
                        <small class="form-text text-muted">
                            Usia akan dihitung otomatis.
                            <span id="usiaHint" class="fw-semibold"></span>
                        </small>
                        @error('tgl_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Guru Wali --}}
                    <div class="col-md-8">
                        <label for="guru_wali" class="form-label">Guru Wali (Tentatif)</label>
                        <input type="text" name="guru_wali" id="guru_wali"
                               class="form-control @error('guru_wali') is-invalid @enderror"
                               value="{{ old('guru_wali') }}">
                        @error('guru_wali')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h5 class="mt-4 mb-3 border-bottom pb-1">Data Kontak Wali/Orang Tua</h5>

                <div class="row g-3">
                    {{-- Nama Orang Tua --}}
                    <div class="col-md-6">
                        <label for="orangtua" class="form-label">Nama Wali/Orang Tua</label>
                        <input type="text" name="orangtua" id="orangtua"
                               class="form-control @error('orangtua') is-invalid @enderror"
                               value="{{ old('orangtua') }}">
                        @error('orangtua')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="col-md-6">
                        <label for="no_telp" class="form-label">Nomor Telepon Kontak</label>
                        <input type="text" name="no_telp" id="no_telp"
                               class="form-control @error('no_telp') is-invalid @enderror"
                               value="{{ old('no_telp') }}">
                        @error('no_telp')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat --}}
                    <div class="col-12">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" id="alamat" rows="3"
                                  class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Daftarkan Murid Baru <i class="bi bi-send-fill ms-1"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styling untuk label wajib */
    .form-label.required:after {
        content: " *";
        color: red;
    }
</style>

@endsection

@push('scripts')
<script>
// Hint usia sederhana (client-side). Server tetap hitung ulang untuk akurat.
(function() {
    const input = document.getElementById('tgl_lahir');
    const hint  = document.getElementById('usiaHint');

    function updateAge() {
        if (!input || !hint) return;
        const v = input.value;
        if (!v) { hint.textContent = ''; return; }
        const dob = new Date(v + 'T00:00:00');
        if (isNaN(dob.getTime())) { hint.textContent = ''; return; }
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        hint.textContent = (isFinite(age) && age >= 0) ? `(≈ ${age} tahun)` : '';
    }

    input && input.addEventListener('change', updateAge);
    // jika ada nilai lama (old) saat reload setelah error, tampilkan
    updateAge();
})();
</script>
@endpush
