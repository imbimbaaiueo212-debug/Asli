@extends('layouts.app')

@section('content')
    <div class="container py-4 py-md-5">
        <div class="row">
            <div class="col-12">
                <h1 class="h4 mb-4 fw-bold text-center text-md-start px-3 px-md-0">Ajukan Cash Advance</h1>

                <div class="card shadow mx-3 mx-md-0">
                    <div class="card-body p-4 p-md-5">

                        @php
                            $profiles = \App\Models\Profile::with('unit')->orderBy('nama')->get();
                        @endphp

                        <!-- Data Pribadi Karyawan -->
                        <h2 class="h6 fw-semibold mb-4 text-primary">Data Pribadi Karyawan</h2>
                        <form action="{{ route('cash-advance.store') }}" method="POST" id="cashAdvanceForm">
                            @csrf

                            <div class="row g-3 mb-5">
                                <div class="col-md-6">
                                    <label for="profile_id" class="form-label fw-medium">Nama Karyawan <span
                                            class="text-danger">*</span></label>
                                    <select name="profile_id" id="profile_id"
                                        class="form-select @error('profile_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Karyawan --</option>
                                        @foreach($profiles as $profile)
                                            <option value="{{ $profile->id }}" {{ old('profile_id') == $profile->id ? 'selected' : '' }}>
                                                {{ $profile->nama }}
                                                ({{ $profile->jabatan ?? 'Tanpa Jabatan' }}
                                                - {{ $profile->unit?->biMBA_unit ?? $profile->bimba_unit ?? 'Tanpa Unit' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('profile_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Jabatan</label>
                                    <input type="text" id="jabatan" class="form-control bg-light" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">No. Telepon</label>
                                    <input type="text" id="no_telp" class="form-control bg-light" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Unit</label>
                                    <input type="text" id="unit" class="form-control bg-light" readonly>
                                </div>
                            </div>

                            <!-- Detail Pengajuan -->
                            <h2 class="h6 fw-semibold mb-4 text-primary">Detail Pengajuan</h2>

                            <div class="row g-3">
                               <div class="col-md-3">
    <label for="bulan" class="form-label fw-medium">
        Bulan <span class="text-danger">*</span>
    </label>
    <select name="bulan" id="bulan" class="form-select @error('bulan') is-invalid @enderror" required>
        <option value="">-- Pilih Bulan --</option>
        @php
            $bulanIndonesia = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];
        @endphp
        @foreach($bulanIndonesia as $nomor => $nama)
            <option value="{{ $nomor }}" {{ old('bulan') == $nomor ? 'selected' : '' }}>
                {{ $nama }}
            </option>
        @endforeach
    </select>
    @error('bulan')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="col-md-3">
    <label for="tahun" class="form-label fw-medium">
        Tahun <span class="text-danger">*</span>
    </label>
    <select name="tahun" id="tahun" class="form-select @error('tahun') is-invalid @enderror" required>
        <option value="">-- Pilih Tahun --</option>
        @php
            $tahunSekarang = now()->year;
        @endphp
        @for($y = $tahunSekarang; $y <= $tahunSekarang + 2; $y++)
            <option value="{{ $y }}" {{ old('tahun') == $y ? 'selected' : '' }}>
                {{ $y }}
            </option>
        @endfor
    </select>
    @error('tahun')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                                <div class="col-md-6">
                                    <label for="nominal_pinjam_display" class="form-label fw-medium">Nominal Pinjaman (Rp)
                                        <span class="text-danger">*</span></label>
                                    <input type="text" id="nominal_pinjam_display"
                                        class="form-control @error('nominal_pinjam') is-invalid @enderror"
                                        placeholder="Contoh: 3.000.000" required autocomplete="off">
                                    <input type="hidden" name="nominal_pinjam" id="nominal_pinjam"
                                        value="{{ old('nominal_pinjam') }}">
                                    <small class="text-muted">Gunakan titik sebagai pemisah ribuan</small>
                                    @error('nominal_pinjam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 position-relative">
                                    <label for="jangka_waktu" class="form-label fw-medium">Jangka Waktu (bulan) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="jangka_waktu" id="jangka_waktu"
                                        value="{{ old('jangka_waktu') }}" min="1" max="36"
                                        class="form-control @error('jangka_waktu') is-invalid @enderror" required
                                        placeholder="1 - 36 bulan">
                                    <div id="angsuran-info" class="form-text text-danger mt-1 small"></div>
                                    @error('jangka_waktu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="keperluan" class="form-label fw-medium">Keperluan <span
                                            class="text-danger">*</span></label>
                                    <textarea name="keperluan" id="keperluan" rows="5"
                                        class="form-control @error('keperluan') is-invalid @enderror" required
                                        placeholder="Jelaskan keperluan pinjaman secara singkat">{{ old('keperluan') }}</textarea>
                                    @error('keperluan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-5 d-flex flex-column flex-sm-row justify-content-end gap-3">
                                <a href="{{ route('cash-advance.index') }}"
                                    class="btn btn-outline-secondary order-2 order-sm-1 px-5 py-2">
                                    Batal
                                </a>
                                <button type="submit" class="btn btn-primary order-1 order-sm-2 px-5 py-2">
                                    Ajukan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Format rupiah
            function formatRupiah(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            function unformatRupiah(str) {
                return str.replace(/\./g, '');
            }

            const nominalDisplay = document.getElementById('nominal_pinjam_display');
            const nominalHidden = document.getElementById('nominal_pinjam');
            const jangkaWaktu = document.getElementById('jangka_waktu');
            const angsuranInfo = document.getElementById('angsuran-info');

            // Format nominal saat ketik
            nominalDisplay.addEventListener('input', function (e) {
                let value = this.value.replace(/\./g, '');
                if (!/^\d*$/.test(value)) {
                    value = value.replace(/[^\d]/g, '');
                }
                if (value) {
                    this.value = formatRupiah(value);
                    nominalHidden.value = value;
                } else {
                    this.value = '';
                    nominalHidden.value = '';
                }
                hitungAngsuran();
            });

            // Hitung angsuran real-time
            function hitungAngsuran() {
                const nominal = parseInt(nominalHidden.value) || 0;
                const jangka = parseInt(jangkaWaktu.value) || 0;

                if (nominal > 0 && jangka > 0) {
                    const angsuran = Math.round(nominal / jangka);
                    if (angsuran < 300000) {
                        angsuranInfo.textContent = `Angsuran per bulan: Rp ${formatRupiah(angsuran)} → Minimal Rp 300.000`;
                        angsuranInfo.classList.add('text-danger');
                    } else {
                        angsuranInfo.textContent = `Angsuran per bulan: Rp ${formatRupiah(angsuran)}`;
                        angsuranInfo.classList.remove('text-danger');
                    }
                } else {
                    angsuranInfo.textContent = '';
                }
            }

            jangkaWaktu.addEventListener('input', hitungAngsuran);

            // Trigger saat load (old value + auto-fill karyawan)
            window.addEventListener('load', function () {
                if (nominalHidden.value) {
                    nominalDisplay.value = formatRupiah(nominalHidden.value);
                }
                hitungAngsuran();
                document.getElementById('profile_id').dispatchEvent(new Event('change'));
            });

            // Auto-fill data karyawan
            document.getElementById('profile_id').addEventListener('change', function () {
                const selectedId = this.value;
                const profiles = @json($profiles);

                const selectedProfile = profiles.find(p => p.id == selectedId);

                if (selectedProfile) {
                    document.getElementById('jabatan').value = selectedProfile.jabatan || '-';
                    document.getElementById('no_telp').value = selectedProfile.no_telp || '-';

                    let unitName = '-';
                    if (selectedProfile.unit && selectedProfile.unit.biMBA_unit) {
                        unitName = selectedProfile.unit.biMBA_unit;
                        if (selectedProfile.unit.no_cabang && selectedProfile.unit.no_cabang.trim() !== '' && selectedProfile.unit.no_cabang !== '-') {
                            unitName += ' (' + selectedProfile.unit.no_cabang + ')';
                        }
                    } else if (selectedProfile.bimba_unit) {
                        unitName = selectedProfile.bimba_unit;
                    }
                    document.getElementById('unit').value = unitName;
                } else {
                    document.getElementById('jabatan').value = '';
                    document.getElementById('no_telp').value = '';
                    document.getElementById('unit').value = '';
                }
            });
        </script>
    @endpush
@endsection