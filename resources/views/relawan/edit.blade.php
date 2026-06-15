@extends('layouts.app')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-white fw-bold">Menu Absensi /</span> Edit Absensi Relawan
        </h4>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 text-white">
                    Edit Absensi — {{ $absen->nama_relawan }} ({{ $absen->nik }})
                </h5>
            </div>

            <div class="card-body pt-4">
                <form action="{{ route('relawan.update', $absen->id) }}" method="POST" id="formEditAbsen">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        <!-- NIK & Nama (readonly) -->
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-control bg-light" value="{{ $absen->nik }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Relawan</label>
                            <input type="text" class="form-control bg-light" value="{{ $absen->nama_relawan }}" readonly>
                        </div>

                        <!-- Unit & No Cabang -->
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control bg-light" value="{{ $absen->bimba_unit }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No Cabang</label>
                            <input type="text" class="form-control bg-light" value="{{ $absen->no_cabang }}" readonly>
                        </div>

                        <!-- Tanggal Absen -->
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Absen <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                   value="{{ $absen->tanggal?->format('Y-m-d') }}" required>
                            @error('tanggal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Status Absensi – PERBAIKAN: mapping kode pendek ke teks panjang -->
                        <div class="col-md-6">
                            <label class="form-label">Status Absensi <span class="text-danger">*</span></label>
                            <select name="status" id="statusAbsen" class="form-select" required>
                                <option value="" disabled>-- Pilih Status Absensi --</option>

                                <!-- Helper function untuk menentukan selected -->
                                @php
                                    $currentStatus = old('status', $absen->status); // ambil dari form (jika validasi gagal) atau database
                                    
                                    // Mapping kode pendek → teks panjang yang ada di dropdown
                                    $statusMap = [
                                        'Sakit'  => 'Sakit Dengan Keterangan Dokter', // default ambil yang utama
                                        'Izin'   => 'Izin Dengan Form di ACC',     // default
                                        'Alpa'   => 'Tidak Masuk Tanpa Form',
                                        'Cuti'   => 'Cuti',
                                        'DT'     => 'Datang Terlambat',
                                        'PC'     => 'Pulang Cepat',
                                        'Minggu' => 'Minggu',
                                        'Libur Nasional' => 'Libur Nasional',
                                        'Hadir'  => 'Hadir',
                                    ];

                                    // Jika status pendek ada di map, gunakan teks panjangnya
                                    $selectedLong = $statusMap[$currentStatus] ?? $currentStatus;
                                @endphp

                                <!-- SAKIT -->
                                <optgroup label="SAKIT (dianggap SAKIT di potongan)">
                                    <option value="Sakit Dengan Keterangan Dokter" {{ $selectedLong === 'Sakit Dengan Keterangan Dokter' ? 'selected' : '' }}>
                                        Sakit Dengan Keterangan Dokter
                                    </option>
                                </optgroup>

                                <!-- IZIN -->
                                <optgroup label="IZIN (dianggap IZIN di potongan)">
                                    <option value="Izin Dengan Form di ACC" {{ $selectedLong === 'Izin Dengan Form di ACC' ? 'selected' : '' }}>
                                        Izin Dengan Form di ACC
                                    </option>
                                    <option value="Izin Tanpa Form di ACC" {{ $selectedLong === 'Izin Tanpa Form di ACC' ? 'selected' : '' }}>
                                        Izin Tanpa Form di ACC
                                    </option>
                                    <option value="Sakit Tanpa Keterangan Dokter" {{ $selectedLong === 'Sakit Tanpa Keterangan Dokter' ? 'selected' : '' }}>
                                        Sakit Tanpa Keterangan Dokter
                                    </option>
                                </optgroup>

                                <!-- ALPA -->
                                <optgroup label="ALPA (dianggap ALPA di potongan)">
                                    <option value="Tidak Mengisi Absensi Mingguan" {{ $selectedLong === 'Tidak Mengisi Absensi Mingguan' ? 'selected' : '' }}>
                                        Tidak Mengisi Absensi Mingguan
                                    </option>
                                    <option value="Tidak Masuk Tanpa Form" {{ $selectedLong === 'Tidak Masuk Tanpa Form' ? 'selected' : '' }}>
                                        Tidak Masuk Tanpa Form
                                    </option>
                                    <option value="Tidak Aktif" {{ $selectedLong === 'Tidak Aktif' ? 'selected' : '' }}>
                                        Tidak Aktif
                                    </option>
                                </optgroup>

                                <!-- CUTI & LAIN-LAIN -->
                                <optgroup label="LAIN-LAIN">
                                    <option value="Cuti Melahirkan" {{ $selectedLong === 'Cuti Melahirkan' ? 'selected' : '' }}>
                                        Cuti Melahirkan
                                    </option>
                                    <option value="Cuti" {{ $selectedLong === 'Cuti' ? 'selected' : '' }}>
                                        Cuti Tahunan / Cuti Lainnya
                                    </option>
                                    <option value="Datang Terlambat" {{ $selectedLong === 'Datang Terlambat' ? 'selected' : '' }}>
                                        Datang Terlambat
                                    </option>
                                    <option value="Pulang Cepat" {{ $selectedLong === 'Pulang Cepat' ? 'selected' : '' }}>
                                        Pulang Cepat
                                    </option>
                                    <option value="Libur Nasional" {{ $selectedLong === 'Libur Nasional' ? 'selected' : '' }}>
                                        Libur Nasional
                                    </option>
                                    <option value="Minggu" {{ $selectedLong === 'Minggu' ? 'selected' : '' }}>
                                        Hari Minggu
                                    </option>
                                </optgroup>

                                <!-- HADIR -->
                                <optgroup label="TANPA POTONGAN">
                                    <option value="Hadir" {{ $selectedLong === 'Hadir' ? 'selected' : '' }}>
                                        Hadir
                                    </option>
                                </optgroup>
                            </select>

                            <small class="text-success mt-2 d-block">
                                Status yang dipilih akan otomatis tersinkron ke potongan tunjangan
                            </small>
                            @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Jam Masuk & Jam Keluar -->
                        <div class="col-md-6">
                            <label class="form-label">Jam Masuk</label>
                            <input type="time" name="jam_masuk" class="form-control"
                                   value="{{ $absen->jam_masuk ? \Carbon\Carbon::parse($absen->jam_masuk)->format('H:i') : '' }}">
                            @error('jam_masuk') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Keluar</label>
                            <input type="time" name="jam_keluar" class="form-control"
                                   value="{{ $absen->jam_keluar ? \Carbon\Carbon::parse($absen->jam_keluar)->format('H:i') : '' }}">
                            @error('jam_keluar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Jam Lembur -->
                        <div class="col-md-6">
                            <label class="form-label">Jam Lembur (menit)</label>
                            <input type="number" name="jam_lembur" class="form-control" min="0" step="1"
                                   value="{{ $absen->jam_lembur ?? 0 }}">
                            @error('jam_lembur') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Keterangan (opsional) -->
                        <div class="col-md-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"
                                      placeholder="Catatan tambahan (opsional)">{{ old('keterangan', $absen->keterangan) }}</textarea>
                            @error('keterangan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Alasan (wajib jika bukan Hadir) -->
                        <div class="col-md-12" id="alasanContainer" style="display: {{ old('status', $absen->status) !== 'Hadir' ? 'block' : 'none' }};">
                            <label class="form-label {{ old('status', $absen->status) !== 'Hadir' ? 'text-danger' : '' }}">
                                Alasan <span class="text-danger">*</span>
                            </label>
                            <textarea name="alasan" id="inputAlasan" class="form-control" rows="4"
                                      placeholder="Jelaskan alasan secara jelas dan lengkap (wajib jika status bukan Hadir)"
                                      {{ old('status', $absen->status) !== 'Hadir' ? 'required' : '' }}>
                                {{ old('alasan', $absen->alasan) }}
                            </textarea>
                            @error('alasan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <small class="text-muted mt-1 d-block">
                                Wajib diisi untuk status selain "Hadir". Contoh: Sakit demam (lampirkan surat dokter), Izin keluarga, macet parah, dll.
                            </small>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="mt-5 text-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('relawan.index') }}" class="btn btn-secondary btn-lg px-5 ms-3">
                            Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectStatus = document.getElementById('statusAbsen');
            const alasanContainer = document.getElementById('alasanContainer');
            const inputAlasan = document.getElementById('inputAlasan');

            if (!selectStatus || !alasanContainer || !inputAlasan) return;

            function toggleAlasan() {
                const status = selectStatus.value.trim();
                const perluAlasan = status && status !== 'Hadir';

                alasanContainer.style.display = perluAlasan ? 'block' : 'none';

                if (perluAlasan) {
                    inputAlasan.setAttribute('required', 'required');
                    alasanContainer.querySelector('label').classList.add('text-danger');
                } else {
                    inputAlasan.removeAttribute('required');
                    alasanContainer.querySelector('label').classList.remove('text-danger');
                }
            }

            selectStatus.addEventListener('change', toggleAlasan);
            toggleAlasan(); // Jalankan pertama kali untuk kondisi edit (nilai lama)
        });
    </script>
    @endpush
@endsection