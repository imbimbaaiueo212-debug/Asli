{{-- resources/views/students/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Pendaftaran')

@push('head')
    {{-- Pastikan meta CSRF tersedia untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="card card-body">
        <h2 class="mb-4">Data Pendaftaran</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filter & Search --}}
<form id="filterStudents" 
      class="card card-body mb-4" 
      method="GET" 
      action="{{ route('students.index') }}">

    <div class="row g-3 align-items-end">

        <!-- Unit biMBA: paling kiri di md+ -->
        <div class="col-12 col-md-5 col-lg-3 order-md-1">
            <label for="unit_id" class="form-label">Unit biMBA</label>
            <select name="unit_id" id="unit_id" class="form-select"
                {{ !in_array(auth()->user()->role ?? '', ['admin','superadmin']) ? 'disabled' : '' }}>
                
                <option value="">Semua Unit</option>

                @foreach($unitOptions as $opt)
                    <option value="{{ $opt['value'] }}"
                        {{ $unitId == $opt['value'] ? 'selected' : '' }}>
                        {{ $opt['label'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Cari (Nama / NIM): kedua -->
        <div class="col-12 col-md-6 col-lg-3 order-md-2">
            <label for="q" class="form-label">Cari (Nama / NIM)</label>
            <select name="q" id="q" class="form-select select2-students"
                data-placeholder="Ketik untuk mencari Nama/NIM">
                <option value=""></option>
                @foreach(($studentOptions ?? []) as $opt)
                    <option value="{{ $opt['value'] }}" {{ request('q') == $opt['value'] ? 'selected' : '' }}>
                        {{ $opt['label'] }}
                    </option>
                @endforeach
                @if (request('q') && !collect($studentOptions ?? [])->pluck('value')->contains(request('q')))
                    <option value="{{ request('q') }}" selected>{{ request('q') }}</option>
                @endif
            </select>
        </div>

        <!-- Filter Status Trial: ketiga -->
        <div class="col-12 col-md-4 col-lg-3 order-md-3">
            <label for="status_trial" class="form-label">Filter Status Trial</label>
            <select id="status_trial" name="status_trial" class="form-select">
                <option value="">-- Semua Status --</option>
                @php
                    $trialStatusOptions = [
                        'tanpa trial' => 'Tanpa Trial',
                        'aktif' => 'Trial Aktif',
                        'baru' => 'Trial Baru',
                        'lanjut_daftar' => 'Lanjut Daftar',
                        'batal' => 'Trial Batal',
                    ];
                @endphp
                @foreach ($trialStatusOptions as $key => $label)
                    <option value="{{ $key }}" @selected(strtolower(request('status_trial')) == $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <!-- Tombol Filter -->
        <div class="col-auto order-md-4">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>

        <!-- Tombol Reset (jika ada filter aktif) -->
        @if (request()->filled('q') || request()->filled('status_trial') || request()->filled('no_cabang') || request()->filled('bimba_unit') || request()->filled('unit_id'))
            <div class="col-auto order-md-5">
                <a href="{{ route('students.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        @endif

    </div>

    <div class="mt-4">
        <p>
            • Jika terdapat murid yang ingin Daftar, kirim URL ini
            <a href="javascript:void(0)"
               onclick="copyLink(this)"
               data-url="https://docs.google.com/forms/d/e/1FAIpQLSd3ba8htzRvNUKyqlNEJYSEBO_r5WAbObTJY3dxsS1wSwFNgw/viewform?usp=pp_url&entry.170339497=Pendaftaran+Langsung&entry.1207635293=biMBA-AIUEO"
               style="color:#0066cc; text-decoration:underline; cursor:pointer; font-weight:500;">
               Salin Link
            </a>
            ke orang tua murid.
        </p>
    </div>

</form>

        {{-- Tarik Registrasi dari Sheet --}}
        <form action="{{ route('students.import') }}" method="POST" class="mb-3">
            @csrf
            <input type="hidden" name="sheet" value="Registrasi">
            <button type="submit" class="btn btn-success"
                onclick="return confirm('Tarik data dari Sheet Registrasi sekarang?')">
                Perbarui Data
            </button>
        </form>

        @php
            $fmtRupiah = function ($n) {
                if ($n === null)
                    return '—';
                $num = is_numeric($n) ? (float) $n : null;
                return $num === null ? '—' : 'Rp ' . number_format($num, 0, ',', '.');
            };
        @endphp

        <div class="table-responsive">
            <table class="card-body table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>NIM</th>
                        <th>Nama Murid</th>
                        <th>Info Humas</th>
                        <th>Status Pendaftaran</th>
                        <!-- TAMBAHAN BARU: FOTO KK -->
                        <th class="text-center">Foto KK</th>
                        <th class="text-center">Foto Mutasi</th>
                        <th width="280" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        @php
                            $sumberRaw = $student->sumber_pendaftaran ?: ucfirst($student->source ?? '');
                            $sumberBadgeClass = match (strtolower($student->source ?? '')) {
                                'trial' => 'bg-info text-dark',
                                'direct' => 'bg-secondary text-dark',
                                default => 'bg-light text-dark',
                            };

                            $hariTampil = $student->hari ?: null;
                            $jamTampil = $student->jam
                                ? str_replace('.', ':', preg_replace('/^jam\s*/i', '', $student->jam))
                                : null;
                            $jadwal = trim(implode(', ', array_filter([$hariTampil, $jamTampil]))) ?: '—';

                            $telp = $student->hp_ayah ?: ($student->hp_ibu ?: ($student->no_telp ?: null));

                            $raw = $student->murid_trial_id ? optional($student->muridTrial)->status_trial : 'tanpa_trial';
                            $labelStatus = $student->status_trial_label;
                            $statusClass = match ($raw) {
                                'aktif' => 'bg-warning text-dark',
                                'lanjut_daftar' => 'bg-success text-white',
                                'batal' => 'bg-danger text-white',
                                'tanpa_trial' => 'bg-secondary text-dark',
                                default => 'bg-dark text-white',
                            };
                        @endphp

                        <tr>
                            <td>{{ $student->nim ?? '—' }}</td>
                            <td>{{ $student->nama }}</td>

                            {{-- Info Humas (100% sama seperti asli kamu) --}}
                            <td>
                                @if (!empty($student->informasi_humas_nama) && trim($student->informasi_humas_nama) !== '')
                                    @php
                                        $refName = trim($student->informasi_humas_nama);
                                        $rowHash = md5('stu:' . $student->id);
                                        $signed = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                            'wheels.public.index',
                                            now()->addDays(7),
                                            ['row_hash' => $rowHash]
                                        );
                                    @endphp

                                    <div class="d-flex align-items-center gap-2">
                                        <div>
                                            <strong>{{ $refName }}</strong>
                                            @if(trim($student->nama))
                                                <div class="text-muted small">{{ $student->nama }}</div>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                id="copyBtn{{ $loop->index ?? '' }}"
                                                onclick="copySpinLink('{{ $loop->index ?? '' }}')"
                                                title="Klik untuk menyalin link spin">
                                                📋 Salin Link
                                            </button>

                                            <input type="text" readonly value="{{ $signed }}" id="spinLink{{ $loop->index ?? '' }}"
                                                style="opacity: 0; position: absolute; left: -9999px;">
                                        </div>

                                        <small class="text-muted">Salin link.</small>

                                        @push('scripts')
                                            <script>
                                                async function copySpinLink(id) {
                                                    const input = document.getElementById('spinLink' + id);
                                                    const btn = document.getElementById('copyBtn' + id);

                                                    try {
                                                        if (navigator.clipboard && window.isSecureContext) {
                                                            await navigator.clipboard.writeText(input.value);
                                                        } else {
                                                            input.focus();
                                                            input.select();
                                                            document.execCommand('copy');
                                                        }

                                                        const original = btn.innerHTML;
                                                        btn.innerHTML = '✅ Tersalin!';
                                                        btn.classList.remove('btn-outline-primary');
                                                        btn.classList.add('btn-success');

                                                        setTimeout(() => {
                                                            btn.innerHTML = original;
                                                            btn.classList.remove('btn-success');
                                                            btn.classList.add('btn-outline-primary');
                                                        }, 2000);

                                                    } catch (err) {
                                                        console.error('Gagal menyalin:', err);
                                                        alert('⚠️ Tidak bisa menyalin link secara otomatis. Silakan salin manual.');
                                                    }
                                                }
                                            </script>
                                        @endpush

                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 btn-open-wheel"
                                            data-row-hash="{{ $rowHash }}" data-name="{{ $refName }}">
                                            Wheel (Admin)
                                        </button>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                           

                            {{-- Status Trial --}}
                            {{-- STATUS PENDAFTARAN (Diperbaiki) --}}
<td class="text-center align-middle">
    @php
        $statusText = 'Tanpa Trial';
        $statusClass = 'bg-secondary text-dark';

        if ($student->source === 'trial') {

    switch(strtolower($student->trial_status ?? '')) {

        case 'baru':
            $statusText = 'Trial Baru';
            $statusClass = 'bg-warning text-dark';
            break;

        case 'aktif':
            $statusText = 'Trial Aktif';
            $statusClass = 'bg-info text-dark';
            break;

        case 'batal':
            $statusText = 'Trial Batal';
            $statusClass = 'bg-danger text-white';
            break;

        case 'lanjut_daftar':
            $statusText = 'Lanjut Daftar';
            $statusClass = 'bg-success text-white';
            break;

        default:
            $statusText = 'Trial';
            $statusClass = 'bg-primary text-white';
            break;
    }
}
    @endphp

    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
</td>
                            <!-- FOTO KK -->
                            <!-- FOTO KK -->
                            <td class="text-center">
                                @if($student->foto_kk)
                                    <a href="{{ asset('public/storage/' . $student->foto_kk) }}" 
                                    target="_blank" 
                                    class="btn btn-sm btn-success mb-2"
                                    title="Lihat Foto KK">
                                        <i class="bi bi-file-image"></i> Lihat KK
                                    </a>
                                    
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-x-circle"></i> Belum ada KK
                                    </span>
                                @endif
                            </td>

                            <!-- FOTO MUTASI -->
                            <td class="text-center">
                                @if($student->foto_mutasi)
                                    <a href="{{ asset('public/storage/' . $student->foto_mutasi) }}" 
                                    target="_blank" 
                                    class="btn btn-sm btn-warning mb-2"
                                    title="Lihat Surat Mutasi">
                                        <i class="bi bi-file-earmark-text"></i> Lihat Mutasi
                                    </a>
                                    
                                    @if(str_contains($student->foto_mutasi, '.pdf'))
                                        <p class="text-muted small mb-0">📄 PDF File</p>
                                    @else
                                        <div>
                                            <img src="{{ asset('public/storage/' . $student->foto_mutasi) }}" 
                                                alt="Foto Mutasi {{ $student->nama }}"
                                                class="img-thumbnail shadow-sm"
                                                style="max-width: 140px; max-height: 160px; object-fit: cover;"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <span class="text-danger small" style="display:none;">Gagal memuat gambar</span>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted small">
                                        <i class="bi bi-x-circle"></i> Belum ada Mutasi
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">
                            {{-- TOMBOL DETAIL BARU --}}
                            <a href="{{ route('students.show', $student) }}" 
                            class="btn btn-sm btn-primary me-1"
                            title="Lihat Detail Murid">
                                <i class="bi bi-eye"></i> Detail
                            </a>

                            <button type="button" class="btn btn-sm btn-info text-white me-1 btn-show-history"
                                data-bs-toggle="modal" data-bs-target="#historyModal" 
                                data-student-id="{{ $student->id }}"
                                data-student-name="{{ $student->nama }}"
                                data-history-url="{{ route('students.history.json', $student) }}">
                                Histori
                            </button>

                            @if(auth()->check() && auth()->user()->is_admin)
                                <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus murid {{ $student->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            @endif
                        </td>
                                                </tr>
                    @empty
                        <tr>
                            <td colspan="17" class="text-center text-muted py-4">Tidak ada data murid yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $students->links() }}
        </div>
    </div>

    {{-- Modal Histori Perubahan Murid --}}
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Histori Perubahan Murid: <span id="studentName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="historyContent">
                        <p class="text-center text-muted">Memuat data histori...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Wheel (Admin lookup + spin) --}}
    <div class="modal fade" id="wheelModal" tabindex="-1" aria-labelledby="wheelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wheelModalLabel">Wheel: <span id="wheelModalName"></span></h5>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="wheelModalBody">
                        <p class="text-center text-muted">Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="wheelSpinButton" type="button" class="btn btn-success" disabled>Spin</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Select2 + Bootstrap 5 theme --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5.min.css">
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: .375rem;
            display: flex;
            align-items: center;
        }

        .select2-container .select2-dropdown {
            z-index: 2000;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const historyContent = document.getElementById('historyContent');
            const studentNameDisplay = document.getElementById('studentName');

            document.querySelectorAll('.btn-show-history').forEach(btn => {
                btn.addEventListener('click', function () {
                    const historyUrl = this.getAttribute('data-history-url');
                    const studentName = this.getAttribute('data-student-name');

                    studentNameDisplay.textContent = studentName;
                    historyContent.innerHTML = '<p class="text-center text-muted">Memuat data histori...</p>';

                    fetch(historyUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                        .then(resp => {
                            if (!resp.ok) throw new Error('HTTP ' + resp.status);
                            return resp.json();
                        })
                        .then(json => {
                            const items = Array.isArray(json.data) ? json.data : [];
                            historyContent.innerHTML = renderHistory(items);
                        })
                        .catch(err => {
                            console.error(err);
                            historyContent.innerHTML = '<div class="alert alert-danger">Gagal memuat histori. Silakan coba lagi.</div>';
                        });
                });
            });

            function labelField(field) {
                return field.replaceAll('_', ' ').replace(/\b\w/g, c => c.toUpperCase());
            }
            function escapeHtml(str) {
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }
            function renderDiffList(diffObj) {
                if (!diffObj || typeof diffObj !== 'object') {
                    return '<p class="mb-0 text-muted">Tidak ada detail perubahan.</p>';
                }
                let html = '<ul class="mb-0">';
                for (const [field, change] of Object.entries(diffObj)) {
                    const oldVal = (change && 'old' in change) ? (change.old ?? '∅') : '∅';
                    const newVal = (change && 'new' in change) ? (change.new ?? '∅') : '∅';
                    html += `<li><strong>${escapeHtml(labelField(field))}</strong>: <em>${escapeHtml(oldVal)}</em> → <em>${escapeHtml(newVal)}</em></li>`;
                }
                html += '</ul>';
                return html;
            }
            function renderHistory(data) {
                if (!data.length) {
                    return '<p class="text-center text-muted">Tidak ada riwayat perubahan yang tercatat.</p>';
                }
                let html = '<ul class="timeline">';
                data.forEach(item => {
                    const dateBadge = item.date ?? '-';
                    const userLabel = item.user ?? 'System';
                    const ipLabel = item.ip ? ' • ' + escapeHtml(item.ip) : '';
                    html += `
                            <li class="timeline-item pb-4">
                                <span class="timeline-icon bg-primary"></span>
                                <div class="timeline-body">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold">Perubahan
                                            <span class="badge bg-primary ms-2">${escapeHtml(dateBadge)}</span>
                                        </h6>
                                        <p class="text-muted small mb-0">${escapeHtml(userLabel)}${ipLabel}</p>
                                    </div>
                                    ${renderDiffList(item.diff)}
                                </div>
                            </li>
                        `;
                });
                html += '</ul>';
                html += `
                        <style>
                            .timeline { list-style: none; padding-left: 0; position: relative; }
                            .timeline:before { content: ''; position: absolute; top: 0; bottom: 0; width: 2px; background: #dee2e6; left: 10px; }
                            .timeline-item { position: relative; padding-left: 30px; }
                            .timeline-icon { position: absolute; top: 6px; left: 0; width: 20px; height: 20px; border-radius: 50%; display: inline-block; border: 3px solid #f8f9fa; }
                        </style>
                    `;
                return html;
            }
        });
    </script>

    {{-- jQuery + Select2 --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>
    <script>
        $(function () {
            $('.select2-students').select2({
                placeholder: $('.select2-students').data('placeholder') || 'Cari…',
                allowClear: true,
                minimumResultsForSearch: 0,
                width: 'resolve',
                dropdownParent: $('#filterStudents')
            });
        });
    </script>

    {{-- Wheel lookup + spin script (unchanged) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function getCsrfToken() {
                const m = document.querySelector('meta[name="csrf-token"]');
                return m ? m.getAttribute('content') : null;
            }

            const wheelModalEl = document.getElementById('wheelModal');
            const wheelModal = wheelModalEl ? new bootstrap.Modal(wheelModalEl) : null;
            const wheelModalBody = document.getElementById('wheelModalBody');
            const wheelModalName = document.getElementById('wheelModalName');
            const wheelSpinButton = document.getElementById('wheelSpinButton');

            function resetWheelModal() {
                if (!wheelModalBody) return;
                wheelModalBody.innerHTML = '<p class="text-center text-muted">Memuat data...</p>';
                if (wheelModalName) wheelModalName.textContent = '';
                if (wheelSpinButton) {
                    wheelSpinButton.disabled = true;
                    wheelSpinButton.dataset.rowHash = '';
                    wheelSpinButton.dataset.name = '';
                }
            }

            document.querySelectorAll('.btn-open-wheel').forEach(btn => {
                btn.addEventListener('click', async function (ev) {
                    ev.preventDefault();
                    const rowHash = this.getAttribute('data-row-hash') || '';
                    const name = this.getAttribute('data-name') || '';

                    resetWheelModal();
                    if (wheelModalName) wheelModalName.textContent = name;
                    if (wheelModal) wheelModal.show();

                    try {
                        const baseLookup = '{{ route("wheels.lookup") }}';
                        const lookupUrl = baseLookup + (rowHash ? '?row_hash=' + encodeURIComponent(rowHash) : '?referrer=' + encodeURIComponent(name));
                        const res = await fetch(lookupUrl, {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });

                        if (!res.ok) {
                            const txt = await res.text();
                            if (res.status === 404) {
                                wheelModalBody.innerHTML = `<div class="alert alert-warning">Lookup: data tidak ditemukan untuk <strong>${escapeHtml(name)}</strong>.</div>`;
                            } else if (res.status === 419) {
                                wheelModalBody.innerHTML = `<div class="alert alert-danger">Lookup gagal: sesi kadaluarsa (HTTP 419). Muat ulang halaman.</div>`;
                            } else {
                                wheelModalBody.innerHTML = `<div class="alert alert-warning">Lookup gagal (HTTP ${res.status}).</div><pre style="white-space:pre-wrap">${escapeHtml(txt)}</pre>`;
                            }
                            return;
                        }

                        const json = await res.json();

                        let html = '<dl class="row">';
                        html += `<dt class="col-sm-4">Nama</dt><dd class="col-sm-8">${escapeHtml(json.referrer_name ?? json.name ?? name)}</dd>`;
                        if (json.student && json.student.nim) {
                            html += `<dt class="col-sm-4">NIM</dt><dd class="col-sm-8">${escapeHtml(json.student.nim)}</dd>`;
                        }
                        html += `<dt class="col-sm-4">Last Voucher (Rp)</dt><dd class="col-sm-8">${json.last_voucher_amount ? ('Rp ' + numberWithDots(json.last_voucher_amount)) : '—'}</dd>`;
                        html += `<dt class="col-sm-4">Voucher Count (saran)</dt><dd class="col-sm-8">${json.voucher_count ?? '—'}</dd>`;
                        if (json.suggested_voucher_numbers && Array.isArray(json.suggested_voucher_numbers)) {
                            html += `<dt class="col-sm-4">Suggested Vouchers</dt><dd class="col-sm-8"><ul class="mb-0">`;
                            json.suggested_voucher_numbers.forEach(v => { html += `<li><code>${escapeHtml(v)}</code></li>`; });
                            html += '</ul></dd>';
                        }
                        html += '</dl>';

                        wheelModalBody.innerHTML = html;
                        if (wheelSpinButton) {
                            wheelSpinButton.disabled = false;
                            wheelSpinButton.dataset.rowHash = json.row_hash ?? rowHash ?? '';
                            wheelSpinButton.dataset.name = json.referrer_name ?? json.name ?? name;
                        }
                    } catch (err) {
                        console.error(err);
                        if (wheelModalBody) wheelModalBody.innerHTML = '<div class="alert alert-danger">Terjadi error saat melakukan lookup. Cek console.</div>';
                    }
                });
            });

            if (wheelSpinButton) {
                wheelSpinButton.addEventListener('click', async function (ev) {
                    ev.preventDefault();
                    const rh = this.dataset.rowHash || '';
                    const nm = this.dataset.name || '';

                    this.disabled = true;
                    const originalText = this.textContent;
                    this.textContent = 'Memproses...';

                    try {
                        const payload = new URLSearchParams();
                        if (rh) payload.append('row_hash', rh);
                        if (nm) payload.append('name', nm);

                        const spinUrl = '{{ route("wheels.spin") }}';
                        const res = await fetch(spinUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': getCsrfToken() || '',
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: payload.toString()
                        });

                        const text = await res.text();

                        if (!res.ok) {
                            if (res.status === 419) {
                                wheelModalBody.innerHTML = `<div class="alert alert-danger">Spin gagal: sesi kadaluarsa / CSRF mismatch (HTTP 419). Silakan muat ulang halaman.</div>`;
                                return;
                            }
                            let msg = `Spin gagal (HTTP ${res.status}).`;
                            try {
                                const j = JSON.parse(text);
                                msg += ' ' + (j.error || j.message || JSON.stringify(j));
                            } catch (_) {
                                msg += ' ' + text;
                            }
                            wheelModalBody.innerHTML = `<div class="alert alert-danger">${escapeHtml(msg)}</div>`;
                            return;
                        }

                        let j;
                        try { j = JSON.parse(text); } catch (e) {
                            wheelModalBody.innerHTML = `<div class="alert alert-warning">Respon tidak valid: <pre>${escapeHtml(text)}</pre></div>`;
                            return;
                        }

                        let out = '<div class="text-center">';
                        out += `<h4 class="mt-2">Pemenang: <strong>${escapeHtml(j.name ?? nm)}</strong></h4>`;
                        out += `<p class="lead">Voucher: <strong>${escapeHtml(j.voucher ?? '')}</strong></p>`;
                        if (j.voucher_amount) out += `<p>Nilai: <strong>Rp ${numberWithDots(j.voucher_amount)}</strong></p>`;
                        if (j.voucher_count) out += `<p>Jumlah Voucher: <strong>${j.voucher_count}</strong></p>`;
                        out += `<p class="small text-muted">Waktu: ${escapeHtml(j.won_at ?? '')}</p>`;
                        out += '</div>';
                        wheelModalBody.innerHTML = out;

                    } catch (err) {
                        console.error(err);
                        wheelModalBody.innerHTML = '<div class="alert alert-danger">Terjadi error saat spin. Cek console.</div>';
                    } finally {
                        this.textContent = originalText;
                        this.disabled = true;
                    }
                });
            }

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }
            function numberWithDots(n) {
                n = parseInt(n) || 0;
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
        });
        function copyLink(el) {
        const url = el.dataset.url;
        if (!url) {
            alert('Link tidak tersedia');
            return;
        }

        // Browser modern
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url)
                .then(() => showCopied(el))
                .catch(() => fallbackCopy(url, el));
        } else {
            fallbackCopy(url, el);
        }
    }

    function fallbackCopy(text, el) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();

        try {
            document.execCommand('copy');
            showCopied(el);
        } catch (e) {
            alert('Gagal menyalin link, silakan salin manual.');
        }

        document.body.removeChild(textarea);
    }

    function showCopied(el) {
        const original = el.textContent;
        el.textContent = '✅ Tersalin';
        el.style.fontWeight = '600';

        setTimeout(() => {
            el.textContent = original;
        }, 2000);
    }

    document.addEventListener('DOMContentLoaded', function () {

    const unitSelect = document.getElementById('unit_id');

    if (unitSelect) {
        unitSelect.addEventListener('change', function () {
            this.form.submit();
        });
    }

});
    </script>
@endpush