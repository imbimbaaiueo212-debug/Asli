@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h4><i class="fas fa-edit"></i> Edit Data Lemburan</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('lembur.update', $lembur->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">

                    <!-- Nama Karyawan (Langsung tampil, tanpa Bimba Unit) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Karyawan <span class="text-danger">*</span></label>
                        <select name="profile_id" id="profile_id" class="form-select select2" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($profiles as $p)
                            <option value="{{ $p->id }}" 
                                {{ $lembur->profile_id == $p->id ? 'selected' : '' }}>
                                {{ $p->nik }} | {{ $p->nama }} 
                                <small>({{ $p->jabatan }}) | {{ $p->biMBA_unit }}</small>
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Lembur -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_lembur" class="form-control" 
                               value="{{ old('tgl_lembur', $lembur->tgl_lembur->format('Y-m-d')) }}" required>
                    </div>

                    <!-- Jam Mulai -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jam Mulai Lembur <span class="text-danger">*</span></label>
                        <input type="time" name="jam_awal" class="form-control" 
                               value="{{ old('jam_awal', $lembur->jam_awal->format('H:i')) }}" required>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jam Selesai Lembur <span class="text-danger">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control" 
                               value="{{ old('jam_selesai', $lembur->jam_selesai->format('H:i')) }}" required>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Diajukan" {{ old('status', $lembur->status ?? 'Diajukan') == 'Diajukan' ? 'selected' : '' }}>Diajukan</option>
                            <option value="Disetujui" {{ old('status', $lembur->status ?? '') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
                            <option value="Ditolak" {{ old('status', $lembur->status ?? '') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Keterangan / Uraian Pekerjaan</label>
                        <textarea name="keterangan" class="form-control" rows="4">{{ old('keterangan', $lembur->keterangan) }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('lembur.index') }}" class="btn btn-secondary">← Kembali</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Data Lembur
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#profile_id').select2({
        placeholder: "-- Pilih Karyawan --",
        allowClear: true
    });
});
</script>
@endpush