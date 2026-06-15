@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h4>Edit Voucher Lama</h4>
            <a href="{{ route('voucher.index') }}" class="btn btn-light">Kembali</a>
        </div>

        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('voucher.update', $voucher->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Voucher Dasar -->
                <div class="row g-3">
                    <div class="col-md-3">
                    <label class="form-label">Histori Spin</label>

                    <input type="text"
                        name="voucher"
                        class="form-control"
                        value="{{ old('voucher', $voucher->voucher) }}"
                        readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">No. Voucher</label>

                    <input type="text"
                        name="no_voucher"
                        class="form-control"
                        value="{{ old('no_voucher', $voucher->no_voucher) }}">
                </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah Voucher</label>
                        <input type="number" name="jumlah_voucher" class="form-control" 
                               value="{{ old('jumlah_voucher', $voucher->jumlah_voucher ?? 1) }}" min="1" required readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Tipe Voucher</label>
                        <select name="tipe_voucher" class="form-select" required>
                            <option value="regular" {{ old('tipe_voucher', $voucher->tipe_voucher) == 'regular' ? 'selected' : '' }}>Humas</option>
                            <option value="event" {{ old('tipe_voucher', $voucher->tipe_voucher) == 'event' ? 'selected' : '' }}>Event</option>
                            <option value="lainnya" {{ old('tipe_voucher', $voucher->tipe_voucher) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Status</label>

                        <select name="status" id="status" class="form-select">
                            <option value="belum_diserahkan"
                                {{ old('status', $voucher->status) == 'belum_diserahkan' ? 'selected' : '' }}>
                                Belum Diserahkan
                            </option>

                            <option value="penyerahan"
                                {{ old('status', $voucher->status) == 'penyerahan' ? 'selected' : '' }}>
                                Penyerahan
                            </option>

                            <option value="Digunakan"
                                {{ old('status', $voucher->status) == 'Digunakan' ? 'selected' : '' }}>
                                Digunakan
                            </option>
                        </select>
                    </div>

                    <!-- TANGGAL PENYERAHAN -->
                    <div class="col-md-3" id="tanggalPenyerahanWrapper" style="display:none;">
                        <label class="form-label">Tanggal Penyerahan</label>

                        <input type="date"
                            name="tanggal_penyerahan"
                            class="form-control"
                            value="{{ old('tanggal_penyerahan', $voucher->tanggal_penyerahan) }}">
                    </div>
                </div>

                <hr>

                <!-- Detail Humas -->
                    <h5>Detail Humas</h5>

                    <div class="row g-3">

                       <div class="col-md-4">
                            <label>NIM Humas</label>

                            <select class="form-select" disabled>
                                <option value="">-- Pilih --</option>

                                @foreach($bukuInduk as $bi)
                                    <option value="{{ $bi->nim }}"
                                        {{ old('nim', $voucher->nim) == $bi->nim ? 'selected' : '' }}>

                                        {{ $bi->nim }} | {{ $bi->nama }}

                                    </option>
                                @endforeach
                            </select>

                            <!-- supaya tetap terkirim -->
                            <input type="hidden" name="nim"
                                value="{{ old('nim', $voucher->nim) }}">
                        </div>

                        <div class="col-md-4">
                            <label>Nama Humas</label>

                            <input type="text"
                                name="nama_murid"
                                class="form-control"
                                value="{{ old('nama_murid', $voucher->nama_murid) }}" readonly>
                        </div>

                        <div class="col-md-4">
                            <label>biMBA Unit</label>

                            <input type="text"
                                name="bimba_unit"
                                class="form-control"
                                value="{{ old('bimba_unit', $voucher->bimba_unit) }}" readonly>
                        </div>

                    </div>

                    <!-- TAMBAHAN -->
                    <div class="row g-3 mt-2">

                        <div class="col-md-6">
                            <label>Orangtua Humas</label>

                            <input type="text"
                                name="orangtua"
                                class="form-control"
                                value="{{ old('orangtua', $voucher->orangtua) }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label>Telp/HP Humas</label>

                            <input type="text"
                                name="telp_hp"
                                class="form-control"
                                value="{{ old('telp_hp', $voucher->telp_hp) }}" readonly>
                        </div>

                    </div>

                <!-- Detail Murid Baru -->
                <h5>Detail Murid Baru</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>NIM Murid Baru</label>
                        <input type="text" name="nim_murid_baru" class="form-control" 
                               value="{{ old('nim_murid_baru', $voucher->nim_murid_baru) }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label>Nama Murid Baru</label>
                        <input type="text" name="nama_murid_baru" class="form-control" 
                               value="{{ old('nama_murid_baru', $voucher->nama_murid_baru) }}" readonly>
                    </div>

                    <div class="col-md-5">
                        <label>Orangtua Murid Baru</label>
                        <input type="text" name="orangtua_murid_baru" class="form-control" 
                               value="{{ old('orangtua_murid_baru', $voucher->orangtua_murid_baru) }}" readonly>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label>Telp/HP Murid Baru</label>
                        <input type="text" name="telp_hp_murid_baru" class="form-control" 
                               value="{{ old('telp_hp_murid_baru', $voucher->telp_hp_murid_baru) }}" readonly>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {

    const statusSelect = document.getElementById('status');
    const tanggalWrapper = document.getElementById('tanggalPenyerahanWrapper');

    function toggleTanggalPenyerahan() {

        if (statusSelect.value === 'penyerahan') {
            tanggalWrapper.style.display = 'block';
        } else {
            tanggalWrapper.style.display = 'none';
        }
    }

    toggleTanggalPenyerahan();

    statusSelect.addEventListener('change', toggleTanggalPenyerahan);

});
</script>