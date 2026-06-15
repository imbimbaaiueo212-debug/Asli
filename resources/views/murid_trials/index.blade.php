{{-- resources/views/murid_trials/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Murid Trial')

@section('content')
    <div class="card card-body">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0 text-dark">Data Murid Trial</h5>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success m-3">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="alert alert-info m-3">{{ session('info') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning m-3">{{ session('warning') }}</div>
            @endif

            <div class="card-body">
                {{-- FILTER --}}
                <form id="filterTrials" class="row g-2 align-items-end mb-4" method="GET"
      action="{{ route('murid_trials.index') }}">
    <input type="hidden" name="status" value="{{ request('status') }}">

   <!-- Unit biMBA: paling kiri di md+ -->
<div class="col-md-3 order-md-1">
    <label class="form-label">Unit biMBA</label>

    <select name="unit_id" id="unitSelect"
        class="form-select select2-unit"
        data-placeholder="Pilih Cabang - Unit"
        {{ (!auth()->user()->is_admin ?? true) ? 'disabled' : '' }}>

        <option value=""></option>

        @foreach(($unitOptions ?? []) as $opt)
            <option value="{{ $opt['value'] }}"
                @selected(request('unit_id') == $opt['value'])>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {{-- Kirim value saat disabled (untuk non-admin) --}}
    @if(!auth()->user()->is_admin ?? true)
        <input type="hidden" name="unit_id" value="{{ request('unit_id') }}">
    @endif
</div>

    <!-- Cari Nama / No HP / Unit: tengah (lebar lebih besar) -->
    <div class="col-md-6 col-lg-5 position-relative order-md-2">
        <label class="form-label">Cari Nama / No HP / Unit</label>
        <input type="text" id="searchInput" name="search" value="{{ request('search') }}"
               class="form-control" autocomplete="off" placeholder="Ketik nama / HP / unit...">

        <div id="searchDropdown" class="list-group position-absolute w-100 shadow-sm"
             style="z-index: 9999; display:none; max-height:320px; overflow:auto; top: 100%; margin-top: 4px;">
        </div>
    </div>

    <!-- Tombol Cari + Reset: paling kanan -->
    <div class="col-auto order-md-3 d-flex align-items-end gap-2">
        <button class="btn btn-primary">Cari</button>
        <a href="{{ route('murid_trials.index') }}" class="btn btn-secondary">Reset</a>
    </div>
</form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th colspan="16" class="fs-5">DETAIL MURID TRIAL</th>
                            </tr>
                            <tr>
                                <th width="50">No</th>
                                <th>Tgl Daftar</th>
                                <th>Kelas</th>
                                <th>Nama</th>
                                <th>Tgl Lahir</th>
                                <th>Usia</th>
                                <th>Unit</th>
                                <th>Guru Trial</th>
                                <th>Info</th>
                                <th>Orangtua</th>
                                <th>No Telp</th>
                                <th>Alamat</th>
                                <th width="140">Status</th>
                                <th width="140">Tanggal (TB)</th>
                                <th width="140">Tanggal (TA)</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($murid_trials as $i => $murid)
                                @php
                                    $telp = $murid->no_telp
                                        ?: optional($murid->student)->hp_ayah
                                        ?: optional($murid->student)->hp_ibu;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $murid_trials->firstItem() + $i }}</td>
                                    <td class="text-center text-nowrap">
                                        {{ $murid->tgl_mulai ? \Carbon\Carbon::parse($murid->tgl_mulai)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">biMBA AIUEO</td>
                                    <td><strong>{{ $murid->nama }}</strong></td>
                                    <td class="text-center text-nowrap">
                                        {{ $murid->tgl_lahir ? \Carbon\Carbon::parse($murid->tgl_lahir)->format('d/m/Y') : '-' }}
                                    </td>
                                    </td>
                                    <td class="text-center">{{ $murid->usia ?? '-' }} th</td>

                                    <td class="text-center">
                                        @if($murid->bimba_unit)
                                            <span class="badge bg-info text-dark">{{ $murid->bimba_unit }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- GURU TRIAL --}}
                                       <td style="min-width: 220px; width: 240px;">
                                        <form action="{{ route('murid_trials.update_guru', $murid->id) }}" 
                                            method="POST" 
                                            class="guru-form">
                                            
                                            @csrf
                                            @method('PATCH')

                                            <div class="input-group input-group-sm">
                                                <select name="guru_trial" 
                                                        class="form-select form-select-sm"
                                                        onchange="this.form.submit()"
                                                        {{ $murid->is_locked_guru ? 'disabled' : '' }}
                                                        style="{{ $murid->is_locked_guru ? 'background-color: #f8f9fa; cursor: not-allowed; opacity: 0.7;' : '' }}">

                                                    <option value="">- Pilih Guru -</option>
                                                    
                                                    @foreach($daftarGuru as $key => $label)
                                                        <option value="{{ $key }}" 
                                                                {{ old('guru_trial', $murid->guru_trial) == $key || 
                                                                $murid->guru_trial == $key ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                
                                                @if($murid->is_locked_guru)
                                                    <span class="input-group-text bg-light text-muted" 
                                                        title="Sudah masuk Buku Induk - Tidak dapat diubah">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                @endif
                                            </div>
                                        </form>
                                    </td>

                                    <td>{{ $murid->info ?: '-' }}</td>
                                    <td>{{ $murid->orangtua ?: '-' }}</td>
                                    <td class="text-nowrap">{{ $telp ?: '-' }}</td>
                                    <td style="max-width: 420px; white-space: normal;">
                                        {{ $murid->full_address }}
                                    </td>

                                    {{-- KOLOM STATUS (HANYA DROPDOWN SAJA) --}}
                                    <td class="text-center">
                                        <form action="{{ route('murid_trials.updateStatus', $murid->id) }}" method="POST">
                                            @csrf 
                                            @method('PATCH')
                                            <select name="status_trial" class="form-select form-select-sm" style="width:150px;"
                                                onchange="this.form.submit()">
                                                
                                                <option value="aktif" {{ $murid->status_trial == 'aktif' ? 'selected' : '' }}>
                                                    Trial Aktif
                                                </option>
                                                
                                                <option value="lanjut_daftar" {{ $murid->status_trial == 'lanjut_daftar' ? 'selected' : '' }}>
                                                    Lanjut Daftar
                                                </option>
                                                
                                                <option value="batal" {{ $murid->status_trial == 'batal' ? 'selected' : '' }}>
                                                    Batal
                                                </option>
                                            </select>
                                        </form>
                                    </td>

                                    <td>{{ $murid->tanggal_trial_baru_formatted ?? '—' }}</td>

                                    <td>{{ $murid->tanggal_aktif_formatted ?? '—' }}</td>

                                    {{-- AKSI --}}
                                    <td class="text-center">
                                        @if($murid->status_trial === 'lanjut_daftar')
                                            @if($murid->student)
                                                <a href="{{ route('registrations.create', ['student_id' => $murid->student->id, 'from_trial' => true]) }}"
                                                class="btn btn-success btn-sm">Pendaftaran</a>
                                            @endif
                                        @else
                                            <span class="text-muted small">Menunggu status</span>
                                        @endif

                                        @if(auth()->check() && auth()->user()->is_admin)   <!-- ← ganti sesuai field admin di model User kamu -->
                                            <form action="{{ route('murid_trials.destroy', $murid->id) }}" method="POST"
                                                class="d-inline ms-1">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="16" class="text-center text-muted py-5">Belum ada data murid trial.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $murid_trials->firstItem() }}–{{ $murid_trials->lastItem() }} dari
                        {{ $murid_trials->total() }} data
                    </div>
                    {{ $murid_trials->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .table td:nth-child(4) {  /* Sesuaikan nomor kolom Guru Trial */
        min-width: 240px !important;
        width: 250px !important;
    }
    
    .guru-form select {
        min-width: 180px;
    }
</style>
@endpush

@push('scripts')
    
    <script>
        $(function () {
            const $input = $('#searchInput');
            const $dropdown = $('#searchDropdown');
            const $unit = $('#unitSelect');
            let debounceTimer;

            function fetch(term = '') {
                $dropdown.html('<div class="list-group-item text-muted">Loading…</div>').show();

                $.get("{{ route('murid_trials.searchAjax') }}", {
                    q: term,
                    unit: $unit.val()
                })
                    .done(data => {
                        if (!data || data.length === 0) {
                            $dropdown.html('<div class="list-group-item text-muted">Tidak ada hasil.</div>');
                            return;
                        }
                        const html = data.map(item => `
                            <button type="button" class="list-group-item list-group-item-action text-start">
                                <strong>${item.nama}</strong><br>
                                <small class="text-muted">${item.no_telp || '-'} • ${item.bimba_unit || '-'}</small>
                            </button>
                        `).join('');
                        $dropdown.html(html);
                    })
                    .fail(() => $dropdown.html('<div class="list-group-item text-danger">Gagal memuat data.</div>'));
            }

            $input.on('focus input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => fetch(this.value.trim()), 300);
            });

            $unit.on('change', () => fetch($input.val().trim()));

            $(document).on('click', '#searchDropdown button', function () {
                $input.val($(this).find('strong').text());
                $dropdown.hide();
                $('#filterTrials').submit();
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#searchInput, #searchDropdown, #unitSelect').length) {
                    $dropdown.hide();
                }
            });
        });
    </script>
@endpush