@extends('layouts.app')
@section('title', 'Edit Data Murid')

@section('content')
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Edit Data Murid — {{ $student->nama }}</h4>
            </div>

            <div class="card-body">
                <form action="{{ route('students.update', $student->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Bagian Informasi Dasar -->
                    <h5 class="mt-2 mb-3 text-muted">Informasi Dasar Murid</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">NIM</label>
                            <input type="text" class="form-control" value="{{ $student->nim ?? '—' }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Source</label>
                            <input type="text" class="form-control" value="{{ ucfirst($student->source ?? '—') }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status Buku Induk</label>
                            @isset($bi)
                                <input type="text" class="form-control" value="{{ $bi->status ?? '—' }}" readonly>
                            @else
                                <input type="text" class="form-control" value="Belum terdaftar" readonly>
                            @endisset
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama" class="form-label">Nama Lengkap Murid <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $student->nama) }}"
                                class="form-control @error('nama') is-invalid @enderror" required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
    <label for="kelas" class="form-label">Kelas</label>
    <select name="kelas" id="kelas" class="form-select @error('kelas') is-invalid @enderror">
        <option value="">— Pilih Kelas —</option>
        @foreach($kelasList as $kelas)
            <option value="{{ $kelas }}" 
                    {{ old('kelas', $student->kelas ?? 'biMBA-AIUEO') == $kelas ? 'selected' : '' }}>
                {{ $kelas }}
            </option>
        @endforeach
    </select>
    @error('kelas') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
    <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
        <option value="">— Pilih —</option>
        <!-- Perbaikan: dukung nilai teks penuh dari database -->
        @php
            $jk = old('jenis_kelamin', $student->jenis_kelamin);
            $isL = in_array($jk, ['L', 'Laki-laki', 'Laki laki']);
            $isP = in_array($jk, ['P', 'Perempuan', 'perempuan']);
        @endphp
        <option value="L" {{ $isL ? 'selected' : '' }}>Laki-laki</option>
        <option value="P" {{ $isP ? 'selected' : '' }}>Perempuan</option>
    </select>
</div>

                        <div class="col-md-4">
    <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
    <input type="date" name="tgl_lahir" id="tgl_lahir"
           value="{{ old('tgl_lahir') ?? ($student->tgl_lahir ? $student->tgl_lahir->format('Y-m-d') : '') }}"
           class="form-control @error('tgl_lahir') is-invalid @enderror">
    @error('tgl_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

                        <div class="col-md-4">
                            <label class="form-label">Usia</label>
                            <input type="text" class="form-control" value="{{ $student->usia ?? '—' }}" readonly>
                        </div>
                    </div>

                    <!-- Orang Tua & Kontak -->
                    <h5 class="mt-4 mb-3 text-muted">Orang Tua & Kontak Darurat</h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nama_ayah" class="form-label">Nama Ayah</label>
                            <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $student->nama_ayah ?? '') }}"
                                class="form-control @error('nama_ayah') is-invalid @enderror">
                        </div>

                        <div class="col-md-6">
                            <label for="nama_ibu" class="form-label">Nama Ibu</label>
                            <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $student->nama_ibu ?? '') }}"
                                class="form-control @error('nama_ibu') is-invalid @enderror">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="hp_ayah" class="form-label">HP Ayah</label>
                            <input type="tel" name="hp_ayah" id="hp_ayah" value="{{ old('hp_ayah', $student->hp_ayah) }}"
                                class="form-control @error('hp_ayah') is-invalid @enderror" placeholder="08xx-xxxx-xxxx">
                        </div>

                        <div class="col-md-4">
                            <label for="hp_ibu" class="form-label">HP Ibu</label>
                            <input type="tel" name="hp_ibu" id="hp_ibu" value="{{ old('hp_ibu', $student->hp_ibu) }}"
                                class="form-control @error('hp_ibu') is-invalid @enderror" placeholder="08xx-xxxx-xxxx">
                        </div>

                        <div class="col-md-4">
                            <label for="no_telp" class="form-label">No. Telepon Lain</label>
                            <input type="tel" name="no_telp" id="no_telp" value="{{ old('no_telp', $student->no_telp) }}"
                                class="form-control @error('no_telp') is-invalid @enderror" placeholder="021 / 08xx...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Preview Nomor yang Ditampilkan</label>
                        <div id="telpPreview" class="form-control-plaintext fw-bold text-primary">
                            {{ old('hp_ayah', $student->hp_ayah) ?: (old('hp_ibu', $student->hp_ibu) ?: (old('no_telp', $student->no_telp) ?: '—')) }}
                        </div>
                        <small class="form-text text-muted">Prioritas: HP Ayah → HP Ibu → No. Telepon lain</small>
                    </div>

                    <!-- Jadwal & Unit -->
                    <h5 class="mt-4 mb-3 text-muted">Jadwal & Unit</h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="hari" class="form-label">Hari Belajar</label>
                            <input type="text" name="hari" value="{{ old('hari', $student->hari) }}"
                                class="form-control @error('hari') is-invalid @enderror" placeholder="Senin, Rabu, Jumat">
                        </div>

                        <div class="col-md-4">
                            <label for="jam" class="form-label">Jam Belajar</label>
                            <input type="text" name="jam" value="{{ old('jam', $student->jam) }}"
                                class="form-control @error('jam') is-invalid @enderror" placeholder="14:00 - 15:30">
                        </div>

                        <div class="col-md-4">
                            <label for="bimba_unit" class="form-label">Unit / Cabang</label>
                            <input type="text" name="bimba_unit" value="{{ old('bimba_unit', $student->bimba_unit) }}"
                                class="form-control @error('bimba_unit') is-invalid @enderror" readonly>
                        </div>
                    </div>

                    <!-- Alamat & Informasi Tambahan -->
                    <h5 class="mt-4 mb-3 text-muted">Alamat & Informasi Lain</h5>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror">{{ old('alamat', $student->alamat) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="informasi_bimba" class="form-label">Sumber Informasi BiMBA</label>
                            <select name="informasi_bimba" id="informasi_bimba" 
                                    class="form-control @error('informasi_bimba') is-invalid @enderror">
                                <option value="">-- Pilih Sumber Informasi --</option>
                                @foreach($sumberInformasi as $key => $label)
                                    <option value="{{ $key }}" 
                                        {{ old('informasi_bimba', $student->informasi_bimba) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('informasi_bimba')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Field HUMAS -->
                        <div class="col-md-6" id="humas_field" style="display: none;">
                            <label for="informasi_humas_nama" class="form-label">Nama Petugas HUMAS</label>
                            <input type="text" name="informasi_humas_nama" id="informasi_humas_nama"
                                value="{{ old('informasi_humas_nama', $student->informasi_humas_nama) }}"
                                class="form-control @error('informasi_humas_nama') is-invalid @enderror"
                                placeholder="Nama lengkap petugas HUMAS">
                        </div>

                        <!-- Field Lainnya -->
                        <div class="col-md-12" id="lainnya_field" style="display: none;">
                            <label for="informasi_lainnya" class="form-label">Keterangan Lainnya</label>
                            <textarea name="informasi_lainnya" id="informasi_lainnya" rows="2"
                                    class="form-control @error('informasi_lainnya') is-invalid @enderror"
                                    placeholder="Jelaskan sumber informasinya...">{{ old('informasi_lainnya', $student->informasi_lainnya ?? '') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                            <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                                value="{{ old('tanggal_masuk') ?? ($student->tanggal_masuk ? $student->tanggal_masuk->format('Y-m-d') : '') }}"
                                class="form-control @error('tanggal_masuk') is-invalid @enderror">
                            @error('tanggal_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-between mt-5">
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputs = [
        document.getElementById('hp_ayah'),
        document.getElementById('hp_ibu'),
        document.getElementById('no_telp')
    ];

    const preview = document.getElementById('telpPreview');

    function updatePreview() {
        let val = '—';
        for (const el of inputs) {
            if (el && el.value.trim()) {
                val = el.value.trim();
                break;
            }
        }
        preview.textContent = val;
    }

    inputs.forEach(el => {
        if (el) el.addEventListener('input', updatePreview);
    });

    // Jalankan sekali di awal
    updatePreview();
});

$(document).ready(function() {
    const $select = $('#informasi_bimba');
    const $humasField = $('#humas_field');
    const $lainnyaField = $('#lainnya_field');

    function toggleFields() {
        const value = $select.val();

        if (value === 'HUMAS') {
            $humasField.show();
            $lainnyaField.hide();
        } else if (value === 'Lainnya') {
            $humasField.hide();
            $lainnyaField.show();
        } else {
            $humasField.hide();
            $lainnyaField.hide();
        }
    }

    // Jalankan saat halaman load
    toggleFields();

    // Jalankan saat select berubah
    $select.on('change', toggleFields);
});
</script>
@endpush