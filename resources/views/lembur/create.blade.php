@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-plus"></i> Tambah Data Lemburan</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('lembur.store') }}" method="POST">
                @csrf

                <div class="row">

                    @if($isAdmin)
                    <!-- Pilih Bimba Unit (Hanya untuk Admin) -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Bimba Unit <span class="text-danger">*</span></label>
                        <select name="bimba_unit" id="bimba_unit" class="form-select" required>
                            <option value="">-- Pilih Bimba Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit }}">{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Nama Karyawan -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Karyawan <span class="text-danger">*</span></label>
                        <select name="profile_id" id="profile_id" class="form-select select2" required>
                            <option value="">-- Pilih Karyawan --</option>
                            @foreach($profiles as $p)
                            <option value="{{ $p->id }}" data-unit="{{ $p->biMBA_unit }}">
                                {{ $p->nik }} - {{ $p->nama }} 
                                <small>({{ $p->jabatan }})</small>
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal Lembur -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Lembur <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_lembur" class="form-control" 
                               value="{{ old('tgl_lembur', now()->format('Y-m-d')) }}" required>
                    </div>

                    <!-- Jam Awal -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jam Mulai Lembur <span class="text-danger">*</span></label>
                        <input type="time" name="jam_awal" class="form-control" 
                               value="{{ old('jam_awal') }}" required>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Jam Selesai Lembur <span class="text-danger">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control" 
                               value="{{ old('jam_selesai') }}" required>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-bold">Keterangan / Uraian Pekerjaan</label>
                        <textarea name="keterangan" class="form-control" rows="4" 
                                  placeholder="Contoh: Membuat laporan bulanan, mengajar kelas tambahan, dll">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('lembur.index') }}" class="btn btn-secondary">← Kembali</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Simpan Data Lembur
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

    // Inisialisasi Select2
    $('#profile_id').select2({
        placeholder: "-- Pilih Karyawan --",
        allowClear: true
    });

    @if($isAdmin)
    $('#bimba_unit').on('change', function() {
        let unit = $(this).val().trim();
        let profileSelect = $('#profile_id');

        if (!unit) {
            profileSelect.html('<option value="">-- Pilih Karyawan --</option>');
            profileSelect.trigger('change'); // refresh select2
            return;
        }

        $.ajax({
            url: "{{ route('lembur.getProfilesByUnit') }}",
            type: "GET",
            data: { bimba_unit: unit },
            dataType: "json",
            success: function(response) {
                let options = '<option value="">-- Pilih Karyawan --</option>';

                if (response.length === 0) {
                    options += '<option value="" disabled>Tidak ada karyawan di unit ini</option>';
                } else {
                    $.each(response, function(i, profile) {
                        options += `<option value="${profile.id}">
                            ${profile.nik} | ${profile.nama} 
                            <small>(${profile.jabatan})</small>
                        </option>`;
                    });
                }

                profileSelect.html(options);
                profileSelect.trigger('change'); // Penting!
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Gagal memuat data karyawan. Cek console browser.");
            }
        });
    });
    @endif

});
</script>
@endpush