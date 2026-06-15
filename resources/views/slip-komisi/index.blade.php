@extends('layouts.app')

@section('title', 'Slip Komisi Murid Baru & Trial')

@section('content')
    <div class="container-fluid px-3 px-md-4 py-3 py-md-4">

        {{-- HEADER + TOMBOL PREVIEW PDF --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4">
            <h3 class="text-center text-md-start fw-bold text-primary mb-0">
                SLIP PEMBAYARAN KOMISI MURID BARU & TRIAL
            </h3>
            @if(!empty($selectedKomisi))
                <button type="button" class="btn btn-outline-danger btn-lg shadow-sm" data-bs-toggle="modal"
                    data-bs-target="#pdfModal" id="previewPdfBtn">
                    <i class="fas fa-file-pdf me-2"></i> Preview PDF
                </button>
            @endif
        </div>

        {{-- MODAL PREVIEW PDF --}}
        <div class="modal fade" id="pdfModal" tabindex="-1">
            <div class="modal-dialog modal-fullscreen-md-down modal-xl">
                <div class="modal-content rounded-4 shadow-lg">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Preview Slip Komisi - {{ $selectedKomisi->profile?->nama ?? 'Staff' }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0 bg-light">
                        @if(!empty($selectedKomisi))
                            {{-- iframe src akan di-set oleh JS agar dapat menambahkan unit_id jika dipilih --}}
                            <iframe id="pdfPreviewFrame"
                                src="{{ route('slip-komisi.preview-pdf') }}?staff_id={{ $selectedKomisi->id }}"
                                class="w-100 border-0" style="height:85vh;"></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('slip-komisi.index') }}">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Pilih Staff</label>
                            <select name="staff_id" class="form-select form-select-lg rounded-3"
                                onchange="this.form.submit()">
                                <option value="">-- Pilih Staff --</option>
                                @foreach($data as $k)
                                    @php
                                        // label jelas: (NIK) Nama - Departemen
                                        $nikLabel = $k->profile?->nik ? ' (' . $k->profile->nik . ')' : '';
                                        $label = trim(($k->profile?->nama ?? $k->nama ?? 'Tanpa Nama') . $nikLabel . ' - ' . ($k->departemen ?? '-'));
                                    @endphp
                                    <option value="{{ $k->id }}" {{ ($selectedKomisi?->id == $k->id) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Bulan</label>
                            <select name="bulan" class="form-select form-select-lg rounded-3" onchange="this.form.submit()">
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ ((int) $bulan === $i) ? 'selected' : '' }}>
                                        {{ $namaBulan[$i - 1] }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" name="tahun" class="form-control form-control-lg rounded-3 text-center"
                                value="{{ $tahun }}" onchange="this.form.submit()">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(empty($selectedKomisi))
            <div class="text-center py-5">
                <i class="fas fa-users fa-5x text-muted mb-4"></i>
                <h5 class="text-muted">Silakan pilih staff untuk menampilkan slip komisi</h5>
            </div>
        @else
            @php
                // Hitung total transfer yang benar (tidak dobel KU)
                $transfer = ($selectedKomisi->komisi_mb_bimba ?? 0)
                    + ($selectedKomisi->komisi_mt_bimba ?? 0)
                    + ($selectedKomisi->komisi_mb_english ?? 0)
                    + ($selectedKomisi->komisi_mt_english ?? 0)
                    + ($selectedKomisi->mb_insentif_ku ?? 0)
                    + ($selectedKomisi->insentif_bimba ?? 0)
                    + ($selectedKomisi->insentif_tambahan ?? 0)
                    + ($selectedKomisi->lebih_bimba ?? 0)
                    + ($selectedKomisi->lebih_bayar ?? 0)
                    - ($selectedKomisi->kurang_bimba ?? 0)
                    - ($selectedKomisi->kurang_bayar ?? 0);
            @endphp

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">

                    {{-- HEADER DENGAN LOGO --}}
                    <div class="text-center mb-5 pb-4 border-bottom border-3 border-primary">
                        <div class="row align-items-center g-3">
                            <div class="col-3 col-md-2">
                                <img src="{{ asset('template/img/logoslip.png') }}" class="img-fluid" style="max-width:80px;"
                                    alt="Logo">
                            </div>
                            <div class="col-6 col-md-8">
                                <h4 class="fw-bold mb-1">biMBA AIUEO</h4>
                                <p class="mb-2 fw-bold text-danger">Infinite Management - Meraih Bahagia Sejati</p>
                                <h4 class="fw-bold text-primary mt-2">SLIP PEMBAYARAN KOMISI MURID BARU & TRIAL</h4>
                            </div>
                            <div class="col-3 col-md-2 text-end">
                                <img src="{{ asset('template/img/jajal.png') }}" class="img-fluid" style="max-width:75px;"
                                    alt="Logo Kanan">
                            </div>
                        </div>
                    </div>

                    {{-- INFO STAFF --}}
                    <div class="row g-4 mb-4 small">
                        <div class="col-12 col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="130"><strong>No. Induk</strong></td>
                                    <td>: {{ $selectedKomisi->profile?->nik ?? $selectedKomisi->nik ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Staff</strong></td>
                                    <td>:
                                        <strong>{{ $selectedKomisi->profile?->nama ?? ($selectedKomisi->nama ?? '-') }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Jabatan</strong></td>
                                    <td>: {{ $selectedKomisi->jabatan ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-12 col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <td width="130"><strong>biMBA Unit</strong></td>
                                    <td>:
                                        @php
                                            // Nama unit prioritas:
                                            // 1) snapshot di komisi (bimba_unit)
                                            // 2) relasi unit terpilih ($selectedUnit)
                                            // 3) departemen sebagai fallback
                                            $unitName = $selectedKomisi->bimba_unit
                                                ?? ($selectedUnit->biMBA_unit ?? null)
                                                ?? ($selectedKomisi->departemen ?? '-');

                                            // No cabang prioritas:
                                            // 1) snapshot di komisi (no_cabang)
                                            // 2) relasi unit ($selectedUnit)
                                            $noCabang = $selectedKomisi->no_cabang
                                                ?? ($selectedUnit->no_cabang ?? '-');
                                        @endphp

                                        <span id="unit_display">
                                            {{ strtoupper($unitName) }}
                                        </span>
                                        &nbsp;|&nbsp;
                                        <span id="no_cabang_display">
                                            {{ $noCabang ?: '-' }}
                                        </span>
                                    </td>
                                </tr>

                    @php
                        $tglMasuk = $selectedKomisi->profile?->tgl_masuk;
                    @endphp

                                <tr>
                                    <td width="130"><strong>Tanggal Masuk</strong></td>
                                    <td>:
                                        @if($tglMasuk)
                                            {{ \Illuminate\Support\Carbon::parse($tglMasuk)->locale('id')->isoFormat('DD MMMM YYYY') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td><strong>Bulan Bayar</strong></td>
                                    <td>: {{ $namaBulan[$selectedKomisi->bulan - 1] }} {{ $selectedKomisi->tahun }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- KOLOM UTAMA: Kiri & Kanan --}}
                    <div class="row g-4 mb-5">
                        <!-- KIRI: Rincian Murid & SPP -->
                        <div class="col-12 col-lg-6">
                            <h5 class="text-success fw-bold mb-3">RINCIAN MURID & PENDAPATAN</h5>
                            <div class="table-responsive rounded-3 shadow-sm">
                                <table class="table table-sm table-hover mb-3">
                                    <thead class="table-primary">
                                        <tr>
                                            <th></th>
                                            <th class="text-center">biMBA</th>
                                            <th class="text-center">English</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Murid Aktif (AM 1)</td>
                                            <td class="text-center">{{ $selectedKomisi->am1_bimba ?? 0 }}</td>
                                            <td class="text-center">{{ $selectedKomisi->am1_english ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td>Murid Aktif Bayar SPP (AM 2)</td>
                                            <td class="text-center">{{ $selectedKomisi->am2_bimba ?? 0 }}</td>
                                            <td class="text-center">{{ $selectedKomisi->am2_english ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td>Murid Baru (MB)</td>
                                            <td class="text-center">
                                                {{ $selectedKomisi->murid_mb_bimba ?? $selectedKomisi->mb_bimba ?? 0 }}</td>
                                            <td class="text-center">{{ $selectedKomisi->mb_english ?? 0 }}</td>
                                        </tr>
                                        <tr>
                                            <td>Murid Trial (MT)</td>
                                            <td class="text-center">
                                                {{ $selectedKomisi->murid_mt_bimba ?? $selectedKomisi->mt_bimba ?? 0 }}</td>
                                            <td class="text-center">{{ $selectedKomisi->mt_english ?? 0 }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <tr>
                                            <td>Penerimaan SPP biMBA</td>
                                            <td class="text-end fw-bold">
                                                Rp{{ number_format($selectedKomisi->spp_bimba ?? $selectedKomisi->total_spp_bimba ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Penerimaan SPP English</td>
                                            <td class="text-end fw-bold">
                                                Rp{{ number_format($selectedKomisi->total_spp_english ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- KANAN: Komisi & Adjustment + Rekening -->
                        <div class="col-12 col-lg-6">
                            <h5 class="text-danger fw-bold mb-3">KOMISI & ADJUSTMENT</h5>
                            <div class="table-responsive rounded-3 shadow-sm mb-3">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <td>Komisi MB biMBA</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->komisi_mb_bimba ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Komisi MT biMBA</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->komisi_mt_bimba ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Komisi MB English</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->komisi_mb_english ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Komisi MT English</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->komisi_mt_english ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Insentif KU</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->mb_insentif_ku ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Insentif Tambahan</td>
                                            <td class="text-end">
                                                Rp{{ number_format($selectedKomisi->insentif_bimba ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kekurangan</td>
                                            <td class="text-end text-danger">-
                                                Rp{{ number_format($selectedKomisi->kurang_bimba ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td>Kelebihan</td>
                                            <td class="text-end text-success">+
                                                Rp{{ number_format($selectedKomisi->lebih_bimba ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="table-success">
                                            <td><strong>JUMLAH DIBAYARKAN</strong></td>
                                            <td class="text-end fw-bold fs-3 text-success">
                                                Rp{{ number_format($transfer, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h5 class="text-dark fw-bold mb-2">Data Rekening</h5>
                            <div class="p-3 bg-light rounded-3 border-start border-primary border-5">
                                <small class="d-block">
                                    <strong>Bank :</strong> {{ $selectedKomisi->profile?->bank ?? '-' }}<br>
                                    <strong>No. Rekening :</strong> {{ $selectedKomisi->profile?->no_rekening ?? '-' }}<br>
                                    <strong>A/N :</strong>
                                    {{ $selectedKomisi->profile?->atas_nama_rekening ?? $selectedKomisi->profile?->nama }}
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- TANDA TANGAN --}}
                    <div class="mt-5 pt-4">
                        <div class="row g-3 g-md-5 justify-content-center">
                            <div class="col-6 col-sm-5 col-md-4">
                                <div class="text-center">
                                    <p class="mb-5 fw-medium text-dark">Yang Menyerahkan,</p>
                                    <div class="mb-4"></div>
                                    <div class="mx-auto border-top border-3 border-dark pt-3"
                                        style="width:220px; max-width:94%;"></div>
                                    <small class="text-muted d-block mt-3">(Keuangan Unit)</small>
                                </div>
                            </div>
                            <div class="col-6 col-sm-5 col-md-4">
                                <div class="text-center">
                                    <p class="mb-5 fw-medium text-dark">Penerima,</p>
                                    <div class="mb-4"></div>
                                    <div class="mx-auto border-top border-3 border-dark pt-3"
                                        style="width:220px; max-width:94%;"></div>
                                    <p class="mt-3 fw-bold">{{ $selectedKomisi->profile?->nama }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

    {{-- CSS RESPONSIF --}}
    <style>
        @media (max-width: 576px) {
            h3 {
                font-size: 1.3rem !important;
            }

            .btn-lg {
                font-size: 1rem;
                padding: 0.6rem 1rem;
            }

            .table {
                font-size: 0.82rem;
            }

            .fs-3 {
                font-size: 1.5rem !important;
            }
        }

        .table-hover tr:hover {
            background-color: #f8f9fa !important;
        }

        .rounded-4 {
            border-radius: 1rem !important;
        }
    </style>

    <script>
        (function () {
            // safe staff id (null jika tidak ada)
            const staffId = @json($selectedKomisi?->id);

            const select = document.getElementById('bimba_unit_select');
            const unitDisplay = document.getElementById('unit_display');
            const noCabangDisplay = document.getElementById('no_cabang_display');
            const iframe = document.getElementById('pdfPreviewFrame');
            const previewModal = document.getElementById('pdfModal');

            function setDisplaysFromOption(opt) {
                if (!opt) return;
                const text = (opt.textContent || opt.innerText || opt.label || opt.value).trim();
                const noCabang = opt.getAttribute('data-no-cabang') || '';
                if (unitDisplay) unitDisplay.textContent = text;
                if (noCabangDisplay) noCabangDisplay.textContent = (noCabang && noCabang !== '-') ? noCabang : '-';
            }

            // jika ada select unit pada halaman
            if (select) {
                // initial set safe
                if (select.options && select.selectedIndex >= 0) {
                    setDisplaysFromOption(select.options[select.selectedIndex]);
                }

                select.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];
                    setDisplaysFromOption(opt);

                    // update iframe src hanya jika iframe ada dan staff dipilih
                    if (iframe && staffId) {
                        const unitId = opt.value;
                        const base = "{{ route('slip-komisi.preview-pdf') }}";
                        iframe.src = base + '?staff_id=' + encodeURIComponent(staffId) + '&unit_id=' + encodeURIComponent(unitId);
                    }
                });
            }

            // saat modal dibuka, pastikan iframe memakai staffId (jika ada) dan unit terpilih
            if (previewModal) {
                previewModal.addEventListener('show.bs.modal', function () {
                    if (!iframe) return;
                    if (!staffId) {
                        // kalau tidak ada staffId, kosongkan iframe atau tampilkan pesan
                        iframe.src = 'about:blank';
                        return;
                    }
                    const unitId = (select && select.value) ? select.value : '';
                    const base = "{{ route('slip-komisi.preview-pdf') }}";
                    iframe.src = base + '?staff_id=' + encodeURIComponent(staffId) + (unitId ? '&unit_id=' + encodeURIComponent(unitId) : '');
                });
            }

            // juga ketika modal sudah terbuka, kalau user klik tombol preview (opsional)
            document.getElementById('previewPdfBtn')?.addEventListener('click', function () {
                if (!staffId) return;
                // open modal handled by bootstrap, iframe di-set pada event show.bs.modal
            });

        })();
    </script>

@endsection