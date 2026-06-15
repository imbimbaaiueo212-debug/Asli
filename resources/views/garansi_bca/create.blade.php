@extends('layouts.app')

@section('title', 'Tambah Garansi BCA')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Tambah Garansi BCA 372 Bebas</h5>
        </div>

        <div class="card-body">

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

            {{-- ================= FILTER UNIT (ADMIN ONLY) ================= --}}
            @if(auth()->user()->isAdminUser())
                <form method="GET" class="mb-4">
                    <label class="fw-bold">Pilih Unit</label>
                    <select name="bimba_unit" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Unit --</option>
                        @foreach($listUnit as $unit)
                            <option value="{{ $unit }}"
                                {{ request('bimba_unit') == $unit ? 'selected' : '' }}>
                                {{ $unit }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            {{-- ================= FORM SIMPAN ================= --}}
            <form action="{{ route('garansi-bca.store') }}" method="POST">
                @csrf

                {{-- ================= PILIH MURID ================= --}}
                <div class="mb-3">
                    <label class="fw-bold">Pilih Murid <span class="text-danger">*</span></label>
                    <select name="nim" id="muridSelect" class="form-select" required>
                        <option value="">-- Pilih Murid --</option>
                        @foreach($listMurid as $m)
                            <option
                                value="{{ $m->nim }}"
                                data-nama="{{ $m->nama }}"
                                data-lahir="{{ trim(($m->tmpt_lahir ?? '-') . ', ' . optional($m->tgl_lahir)->format('d-m-Y')) }}"
                                data-masuk="{{ optional($m->tgl_masuk)->format('Y-m-d') }}"
                                data-ortu="{{ $m->orangtua }}"
                            >
                                {{ $m->nama }} ({{ $m->nim }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ================= PREVIEW DATA (READONLY) ================= --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Nama Murid</label>
                        <input type="text" id="nama_murid" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Tempat / Tanggal Lahir</label>
                        <input type="text" id="ttl" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Masuk</label>
                        <input type="date" id="tgl_masuk" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Nama Orang Tua / Wali</label>
                        <input type="text" id="ortu" class="form-control bg-light" readonly>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Diberikan</label>
                        <input type="date"
                               class="form-control bg-light"
                               value="{{ now()->format('Y-m-d') }}"
                               readonly>
                        <small class="text-muted">
                            Otomatis diisi tanggal hari ini
                        </small>
                    </div>
                </div>

                {{-- ================= VIRTUAL ACCOUNT ================= --}}
                <div class="mt-3">
                    <label>No. Virtual Account</label>
                    <input type="text" name="virtual_account" class="form-control">
                </div>

                {{-- ================= ACTION ================= --}}
                <div class="text-end mt-4">
                    <a href="{{ route('garansi-bca.index') }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-success">
                        Simpan Garansi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('muridSelect')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];

    document.getElementById('nama_murid').value = opt.dataset.nama  || '';
    document.getElementById('ttl').value        = opt.dataset.lahir || '';
    document.getElementById('tgl_masuk').value  = opt.dataset.masuk || '';
    document.getElementById('ortu').value       = opt.dataset.ortu  || '';
});
</script>
@endpush
