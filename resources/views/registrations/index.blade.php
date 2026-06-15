@extends('layouts.app')
@section('title','Daftar Semua Murid Baru')

@section('content')
<div class="card card-body">
    <h2 class="mb-3">Data Registrasi Murid Baru</h2>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-primary text-dark fw-semibold">
        <i class="fas fa-filter me-2"></i>Data Registrasi Murid Baru
    </div>

    <div class="card-body bg-light">
        <form class="row g-3 align-items-end" method="GET" action="{{ route('registrations.index') }}">

    <!-- Unit biMBA: paling kiri di md+ -->
    <div class="col-lg-3 col-md-4 order-md-1">
    <label for="unit_id" class="form-label fw-medium">Unit biMBA</label>

    <select name="unit_id" id="unit_id"
        class="form-select select2-unit"
        data-placeholder="Pilih Cabang - Unit"
        {{ !in_array(auth()->user()->role ?? '', ['admin','superadmin']) ? 'disabled' : '' }}>

        <option value=""></option>

        @foreach($unitOptions as $opt)
            <option value="{{ $opt['value'] }}"
                @selected(($unitId ?? request('unit_id')) == $opt['value'])>
                {{ $opt['label'] }}
            </option>
        @endforeach
    </select>

    {{-- ⛔ penting: kirim value saat disabled --}}
    @if(!in_array(auth()->user()->role ?? '', ['admin','superadmin']))
        <input type="hidden" name="unit_id" value="{{ $unitId }}">
    @endif
</div>

    <!-- Cari NIM / Nama: kedua (lebar agak lebih besar) -->
    <div class="col-lg-4 col-md-5 order-md-2">
        <label for="q" class="form-label fw-medium">Cari NIM / Nama</label>
        <input type="text"
               name="q"
               id="q"
               class="form-control"
               placeholder="Ketik NIM atau nama murid..."
               value="{{ request('q') }}"
               autocomplete="off">
    </div>

    <!-- Status: ketiga -->
    <div class="col-lg-2 col-md-3 order-md-3">
        <label for="status" class="form-label fw-medium">Status</label>
        <select id="status" name="status" class="form-select">
            <option value="">Semua Status</option>
            @foreach (['pending','verified','accepted','rejected'] as $st)
                <option value="{{ $st }}" @selected(request('status') === $st)>
                    {{ ucfirst($st) }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- Tombol Filter & Reset: paling kanan -->
    <div class="col-lg-3 col-md-12 order-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('registrations.index') }}" class="btn btn-outline-secondary flex-grow-1">
            <i class="fas fa-sync-alt"></i> Reset
        </a>
    </div>

</form>
    </div>
</div>


    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>NIM</th>
                            <th>Nama Murid</th>
                            <th>Unit biMBA</th> <!-- KOLOM BARU -->
                            <th>No Cabang</th> <!-- KOLOM BARU -->
                            <th>Gol</th>
                            <th>KD</th>
                            <th>SPP</th>
                            <th>Tgl Daftar</th>
                            <th>Tgl Mulai KBM (MB)</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($regs as $i => $r)
                            @php
                                $student   = $r->student;
                                $biMaster  = $student?->bukuInduk;

                                $gol = $r->gol ?? $biMaster?->gol;
                                $kd  = $r->kd  ?? $biMaster?->kd;
                                $spp = $r->spp ?? $biMaster?->spp;

                                // Ambil bimba_unit & no_cabang dari registration (draft)
                                // Kalau kosong, fallback ke student (jaga-jaga)
                                $unit    = $r->bimba_unit ?? $student?->bimba_unit ?? '-';
                                $cabang  = $r->no_cabang  ?? $student?->no_cabang  ?? '-';
                            @endphp
                            <tr>
                                <td>{{ $regs->firstItem() + $i }}</td>
                                <td><strong>{{ $student?->nim ?? '—' }}</strong></td>
                                <td>{{ $student?->nama ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $unit }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $cabang }}</small>
                                </td>
                                <td><code>{{ $gol ?? '—' }}</code></td>
                                <td><code>{{ strtoupper($kd ?? '—') }}</code></td>
                                <td>
                                    @if($spp)
                                        <strong>Rp {{ number_format((int)$spp, 0, ',', '.') }}</strong>
                                    @else
                                        <em class="text-muted">Belum ditentukan</em>
                                    @endif
                                </td>
                                <td>{{ $r->tanggal_daftar?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $r->tanggal_penerimaan?->format('d/m/Y') ?? '-'}}</td>
                                <td>
                                    @php
                                        $badge = match($r->status) {
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            'verified' => 'info',
                                            default    => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }} text-uppercase">
                                        {{ $r->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($r->attachment_path)
                                        <a href="{{ asset('public/storage/'.$r->attachment_path) }}" target="_blank"
                                           class="btn btn-sm btn-outline-primary" title="Lihat Bukti">
                                            Lihat
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                               <td>
    <div class="btn-group" role="group">
        <a href="{{ route('registrations.edit', $r) }}"
           class="btn btn-sm btn-warning" title="Edit">
            Edit
        </a>

        @if(auth()->check() && auth()->user()->is_admin)   <!-- sesuaikan field yang benar di model User -->
            <form action="{{ route('registrations.destroy', $r) }}" method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Yakin hapus registrasi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                    Hapus
                </button>
            </form>
        @endif
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    Belum ada data registrasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $regs->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Ambil semua data murid yang sedang ditampilkan di tabel
    const studentData = [];
    $('table tbody tr').not('.text-center').each(function() {
        const nim = $(this).find('td:eq(1)').text().trim();     // Kolom NIM
        const nama = $(this).find('td:eq(2)').text().trim();    // Kolom Nama
        if (nim && nama && nim !== '—') {
            studentData.push({
                id: nim,
                text: `${nim} - ${nama}`
            });
        }
    });

    // Inisialisasi Select2 pada input teks (bukan select)
    $('#q').select2({
        placeholder: "Ketik NIM atau nama murid...",
        allowClear: true,
        width: '100%',
        data: studentData,                    // Hanya data yang ada di tabel saat ini
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;

            const term = params.term.toLowerCase();
            if (data.text.toLowerCase().includes(term)) {
                return data;
            }
            return null;
        }
    });

    // Jika ada query sebelumnya (dari filter), set nilai Select2
    @if(request('q'))
        const selected = studentData.find(item => item.id === "{{ request('q') }}");
        if (selected) {
            const newOption = new Option(selected.text, selected.id, true, true);
            $('#q').append(newOption).trigger('change');
        } else if ("{{ request('q') }}") {
            // Kalau NIM tidak ada di tabel saat ini (misal dari filter lama), tetap tampilkan sebagai teks biasa
            const newOption = new Option("{{ request('q') }}", "{{ request('q') }}", true, true);
            $('#q').append(newOption).trigger('change');
        }
    @endif

    // Select2 untuk status tetap sama
    $('select[name="status"]').select2({
        minimumResultsForSearch: Infinity,
        width: '100%'
    });

    // Submit form saat pilih dari dropdown
    $('#q').on('select2:select', function () {
        $(this).closest('form').submit();
    });
});
document.addEventListener('DOMContentLoaded', function () {

    const unitSelect = document.getElementById('unit_id');

    if (unitSelect) {
        unitSelect.addEventListener('change', function () {

            // hanya admin yang auto submit
            const isAdmin = @json(in_array(auth()->user()->role ?? '', ['admin','superadmin']));

            if (isAdmin) {
                this.form.submit();
            }
        });
    }

});
</script>
@endpush