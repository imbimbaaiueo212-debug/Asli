{{-- resources/views/rekap-progresif/create.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Tambah Data Rekap Progresif</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('rekap-progresif.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <h5>Data Utama</h5>

                    {{-- Bulan --}}
                    <div class="mb-3">
                        <label for="bulanSelect">Bulan</label>
                        <select id="bulanSelect" name="bulan" class="form-control" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $bulan)
                                <option value="{{ $bulan }}" {{ old('bulan') == $bulan ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tahun --}}
                    <div class="mb-3">
                        <label for="tahunSelect">Tahun</label>
                        <input type="number"
                               id="tahunSelect"
                               name="tahun"
                               class="form-control"
                               value="{{ old('tahun', date('Y')) }}"
                               required>
                    </div>

                    {{-- Nama (Profile) --}}
                    <div class="mb-3">
                        <label for="profileSelect">Nama</label>
                        <select id="profileSelect" name="profile_id" class="form-control" required>
                            <option value="">-- Pilih Nama --</option>
                            @foreach ($profiles as $profile)
                                <option value="{{ $profile->id }}" {{ old('profile_id') == $profile->id ? 'selected' : '' }}>
                                    {{ $profile->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 🔹 biMBA Unit --}}
                    <div class="mb-3">
                        <label>biMBA Unit</label>
                        <input type="text"
                               id="bimba_unit"
                               name="bimba_unit"
                               class="form-control"
                               readonly
                               value="{{ old('bimba_unit') }}">
                    </div>

                    {{-- 🔹 No Cabang --}}
                    <div class="mb-3">
                        <label>No Cabang</label>
                        <input type="text"
                               id="no_cabang"
                               name="no_cabang"
                               class="form-control"
                               readonly
                               value="{{ old('no_cabang') }}">
                    </div>

                    {{-- Jabatan (auto dari profile + calculate) --}}
                    <div class="mb-3">
                        <label>Jabatan</label>
                        <input type="text"
                               id="jabatan"
                               name="jabatan"
                               class="form-control"
                               readonly
                               value="{{ old('jabatan') }}">
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <input type="text"
                               id="status"
                               name="status"
                               class="form-control"
                               readonly
                               value="{{ old('status') }}">
                    </div>

                    <div class="mb-3">
                        <label>Departemen</label>
                        <input type="text"
                               id="departemen"
                               name="departemen"
                               class="form-control"
                               readonly
                               value="{{ old('departemen') }}">
                    </div>

                    <div class="mb-3">
                        <label>Masa Kerja</label>
                        <input type="text"
                               id="masa_kerja"
                               name="masa_kerja"
                               class="form-control"
                               readonly
                               value="{{ old('masa_kerja') }}">
                    </div>

                    {{-- --- Field perhitungan (dari Controller) --- --}}
                    <div class="mb-3">
                        <label>SPP biMBA (Nominal Rupiah)</label>
                        <input type="text"
                               id="spp_bimba"
                               name="spp_bimba"
                               class="form-control text-end"
                               value="{{ old('spp_bimba', '0') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Total FM</label>
                        <input type="text"
                               id="total_fm"
                               name="total_fm"
                               class="form-control text-end"
                               value="{{ old('total_fm', '0.00') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>Progresif</label>
                        <input type="text"
                               id="progresif_display"
                               class="form-control text-end"
                               value="{{ old('progresif', '0') }}"
                               readonly>
                        {{-- Hidden field untuk nilai progresif mentah yang akan disubmit --}}
                        <input type="hidden"
                               id="progresif"
                               name="progresif"
                               value="{{ old('progresif', 0) }}">
                    </div>

                    {{-- AM1 & AM2 --}}
                    <div class="mb-3">
                        <label>AM1 (Total Murid / per-guru)</label>
                        <input type="text"
                               id="am1"
                               name="am1"
                               class="form-control text-end"
                               value="{{ old('am1', '0') }}"
                               readonly>
                    </div>

                    <div class="mb-3">
                        <label>AM2 (Murid Bayar - Jumlah Murid)</label>
                        <input type="text"
                               id="am2"
                               name="am2"
                               class="form-control text-end"
                               value="{{ old('am2', '0') }}"
                               readonly>
                    </div>

                    {{-- --- Field tambahan (Input User) --- --}}
                    <div class="mb-3">
                        <label for="sppEnglish">SPP English</label>
                        <input type="number"
                               id="sppEnglish"
                               name="spp_english"
                               class="form-control text-end"
                               value="{{ old('spp_english', 0) }}">
                    </div>

                    <div class="mb-3">
                        <label for="komisi">Komisi</label>
                        <input type="number"
                               id="komisi"
                               name="komisi"
                               class="form-control text-end"
                               value="{{ old('komisi', 0) }}">
                    </div>

                    {{-- --- Field Final (Perhitungan Lokal) --- --}}
                    <div class="mb-3">
                        <label>Dibayarkan (Progresif + SPP English + Komisi)</label>
                        <input type="text"
                               id="dibayarkan"
                               name="dibayarkan"
                               class="form-control text-end"
                               value="{{ old('dibayarkan', '0') }}"
                               readonly>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Simpan</button>
            <a href="{{ route('rekap-progresif.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')
                ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                : '{{ csrf_token() }}';

            // Elemen input
            const profileSelect   = document.getElementById('profileSelect');
            const bulanSelect     = document.getElementById('bulanSelect');
            const tahunSelect     = document.getElementById('tahunSelect');
            const sppEnglishInput = document.getElementById('sppEnglish');
            const komisiInput     = document.getElementById('komisi');

            // Elemen output
            const progresifRawInput = document.getElementById('progresif');          // hidden
            const progresifDisplay  = document.getElementById('progresif_display');  // tampilan
            const dibayarkanInput   = document.getElementById('dibayarkan');

            // Field tambahan: biMBA Unit & No Cabang
            const bimbaUnitInput = document.getElementById('bimba_unit');
            const noCabangInput  = document.getElementById('no_cabang');

            function formatNumber(num) {
                if (num === null || num === undefined) return '0';
                if (typeof num === 'number' && num % 1 !== 0) {
                    return new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 2
                    }).format(num);
                }
                return new Intl.NumberFormat('id-ID').format(Number(num));
            }

            function calculateFinalDibayarkan() {
                const progresif  = parseFloat(progresifRawInput.value) || 0;
                const sppEnglish = parseFloat(sppEnglishInput.value) || 0;
                const komisi     = parseFloat(komisiInput.value) || 0;
                const total      = progresif + sppEnglish + komisi;
                dibayarkanInput.value = formatNumber(total);
            }

            let timer = null;
            function scheduleUpdateForm() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(updateForm, 300);
            }

            async function updateForm() {
                const profileId = profileSelect.value;
                const bulanRaw  = bulanSelect.value;
                const tahun     = tahunSelect.value;

                if (!profileId || !bulanRaw || !tahun) {
                    document.getElementById('jabatan').value    = '';
                    document.getElementById('status').value     = '';
                    document.getElementById('departemen').value = '';
                    document.getElementById('masa_kerja').value = '';
                    document.getElementById('spp_bimba').value  = '0';
                    document.getElementById('total_fm').value   = '0.00';
                    progresifDisplay.value  = '0';
                    progresifRawInput.value = 0;
                    document.getElementById('am1').value        = '0';
                    document.getElementById('am2').value        = '0';
                    dibayarkanInput.value   = '0';

                    // 🔹 reset biMBA unit & cabang
                    bimbaUnitInput.value = '';
                    noCabangInput.value  = '';
                    return;
                }

                const bulan = bulanRaw.toLowerCase();
                const body  = new FormData();
                body.append('profile_id', profileId);
                body.append('bulan', bulan);
                body.append('tahun', tahun);

                try {
                    const res = await fetch("{{ route('rekap-progresif.calculate') }}", {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken,
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        },
                        body: body
                    });

                    if (res.status === 422) {
                        const json = await res.json().catch(() => null);
                        const msg = json && json.errors
                            ? Object.values(json.errors).flat().join('\n')
                            : 'Validasi gagal';
                        console.warn('Validation failed:', msg);
                        return;
                    }

                    if (!res.ok) {
                        const text = await res.text();
                        console.error('Server error:', res.status, text);
                        return;
                    }

                    const data = await res.json();

                    // Data utama
                    document.getElementById('jabatan').value    = data.jabatan    ?? '';
                    document.getElementById('status').value     = data.status     ?? '';
                    document.getElementById('departemen').value = data.departemen ?? '';
                    document.getElementById('masa_kerja').value = data.masa_kerja ?? '';

                    // 🔹 biMBA Unit & No Cabang dari controller
                    bimbaUnitInput.value = data.bimba_unit ?? '';
                    noCabangInput.value  = data.no_cabang  ?? '';

                    // Perhitungan
                    document.getElementById('spp_bimba').value =
                        formatNumber(data.spp_bimba ?? 0);

                    document.getElementById('total_fm').value =
                        (data.total_fm !== undefined && data.total_fm !== null)
                            ? parseFloat(data.total_fm).toFixed(2)
                            : '0.00';

                    const progresifValue = data.progresif ?? 0;
                    progresifRawInput.value = progresifValue;
                    progresifDisplay.value  = formatNumber(progresifValue);

                    document.getElementById('am1').value = data.am1 ?? 0;
                    document.getElementById('am2').value = data.am2 ?? 0;

                    calculateFinalDibayarkan();

                } catch (err) {
                    console.error('AJAX error', err);
                }
            }

            // Event
            profileSelect.addEventListener('change', scheduleUpdateForm);
            bulanSelect.addEventListener('change', scheduleUpdateForm);
            tahunSelect.addEventListener('input', scheduleUpdateForm);

            sppEnglishInput.addEventListener('input', calculateFinalDibayarkan);
            komisiInput.addEventListener('input', calculateFinalDibayarkan);

            scheduleUpdateForm();
        });
    </script>
@endsection
