@extends('layouts.app')

@section('title', 'Tambah Pendapatan & Tunjangan')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Data Pendapatan & Tunjangan</h4>
                </div>

                <div class="card-body">

                    <a href="{{ route('pendapatan-tunjangan.index') }}" class="btn btn-secondary mb-4">
                        Kembali ke Daftar
                    </a>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('pendapatan-tunjangan.store') }}" method="POST">
                        @csrf

                        <!-- RELAWAN -->
                        <div class="row mb-4">
                            <div class="col-md-8">

                                <label class="form-label fw-bold text-primary">
                                    Pilih Relawan <span class="text-danger">*</span>
                                </label>

                                <select name="nama" id="profileSelect" class="form-select form-select-lg" required>
                                    <option value="">-- Pilih Relawan --</option>

                                    @foreach($profiles as $profile)
                                    <option value="{{ $profile->nama }}"
                                        data-nik="{{ $profile->nik }}"
                                        data-jabatan="{{ $profile->jabatan }}"
                                        data-status="{{ $profile->status_karyawan }}"
                                        data-masa-kerja="{{ $profile->masa_kerja_format ?? $profile->masa_kerja . ' bulan' }}"
                                        data-bimba-unit="{{ $profile->bimba_unit ?? $profile->nama_unit ?? '' }}"
                                        data-no-cabang="{{ $profile->no_cabang ?? $profile->kode_cabang ?? '' }}"
                                        data-profile-id="{{ $profile->id }}">
                                        {{ $profile->nama }} → {{ $profile->jabatan }} ({{ $profile->status_karyawan }})
                                    </option>
                                    @endforeach
                                </select>

                                <!-- Hidden NIK -->
                                <input type="hidden" name="nik" id="nik">

                            </div>
                        </div>

                        <!-- INFO OTOMATIS -->
                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label class="form-label">Jabatan</label>
                                <input type="text" id="jabatan" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <input type="text" id="status" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Masa Kerja</label>
                                <input type="text" id="masa_kerja" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label text-success fw-bold">THP Dasar</label>
                                <input type="text" id="thpDisplay"
                                    class="form-control fw-bold text-success bg-warning-subtle"
                                    value="Rp 0"
                                    readonly>
                                <small class="text-muted">Dari Skim</small>
                            </div>

                        </div>

                        <!-- UNIT -->
                        <div class="row mb-4">

                            <div class="col-md-6">
                                <label class="form-label">Unit biMBA</label>
                                <input type="text" id="bimba_unit" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No Cabang</label>
                                <input type="text" id="no_cabang" class="form-control bg-light" readonly>
                            </div>

                        </div>

                        <hr class="my-4">

                        <!-- TUNJANGAN -->
                        <div class="row g-3 mb-4">

                            <div class="col-md-4">
                                <label>Kerajinan</label>
                                <input type="number" name="kerajinan" class="form-control" value="0" min="0">
                            </div>

                            <div class="col-md-4">
                                <label>English</label>
                                <input type="number" name="english" class="form-control" value="0" min="0">
                            </div>

                            <div class="col-md-4">
                                <label>Mentor</label>
                                <input type="number" name="mentor" class="form-control" value="0" min="0">
                            </div>

                            <div class="col-md-4">
                                <label>Kekurangan</label>
                                <input type="number" name="kekurangan" class="form-control" value="0" min="0">
                            </div>

                            <div class="col-md-4">
                                <label>Bulan Kekurangan</label>
                                <input type="month" name="bulan_kekurangan" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label>Tunjangan Keluarga</label>
                                <input type="number" name="tj_keluarga" class="form-control" value="0" min="0">
                            </div>

                            <div class="col-md-6">
                                <label>Bulan Gaji *</label>
                                <input type="month" name="bulan" class="form-control"
                                    value="{{ old('bulan', now()->format('Y-m')) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label>Lain-lain</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="lain_lain_display" class="form-control" value="0">
                                    <input type="hidden" name="lain_lain" id="lain_lain" value="0">
                                </div>
                            </div>

                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                Simpan Data
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const select = document.getElementById('profileSelect');

    const nikInput = document.getElementById('nik');
    const jabatan = document.getElementById('jabatan');
    const status = document.getElementById('status');
    const masaKerja = document.getElementById('masa_kerja');

    const bimbaUnit = document.getElementById('bimba_unit');
    const noCabang = document.getElementById('no_cabang');

    const thpDisplay = document.getElementById('thpDisplay');

    function updateInfo() {

        const option = select.options[select.selectedIndex];

        if (!option || !option.value) {

            nikInput.value = '';
            jabatan.value = '';
            status.value = '';
            masaKerja.value = '';
            bimbaUnit.value = '';
            noCabang.value = '';
            thpDisplay.value = 'Rp 0';

            return;
        }

        nikInput.value = option.dataset.nik || '';

        jabatan.value = option.dataset.jabatan || '';
        status.value = option.dataset.status || '';
        masaKerja.value = option.dataset.masaKerja || '';

        bimbaUnit.value = option.dataset.bimbaUnit || '';
        noCabang.value = option.dataset.noCabang || '';

        const profileId = option.dataset.profileId;

        if (!profileId) {
            thpDisplay.value = 'Rp 0';
            return;
        }

        fetch(`/pendapatan-tunjangan/skim-value/${profileId}`)
            .then(res => res.json())
            .then(data => {

                const thp = data.thp || 0;

                thpDisplay.value = 'Rp ' + Number(thp).toLocaleString('id-ID');

            })
            .catch(() => {

                thpDisplay.value = 'Rp 0';

            });

    }

    select.addEventListener('change', updateInfo);

    updateInfo();

    const display = document.getElementById('lain_lain_display');
    const hidden = document.getElementById('lain_lain');

    display.addEventListener('input', function () {

        let value = this.value.replace(/\D/g, '');

        if (value === '') {

            this.value = '';
            hidden.value = 0;

        } else {

            this.value = 'Rp ' + parseInt(value).toLocaleString('id-ID');
            hidden.value = value;

        }

    });

});

</script>

@endsection