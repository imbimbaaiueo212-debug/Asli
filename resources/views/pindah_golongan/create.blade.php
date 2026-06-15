@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Data Pindah Golongan</h2>

    <a href="{{ route('pindah-golongan.index') }}" class="btn btn-secondary mb-3">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan!</strong><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pindah-golongan.store') }}" method="POST">
        @csrf

        <!-- Pilih Buku Induk (Data Lama) -->
        <div class="col-md-12 mb-3">
            <label for="buku_induk_id" class="form-label">Pilih Buku Induk</label>
            <select name="buku_induk_nim" id="buku_induk_nim" class="form-control">
    <option value="">-- Pilih Murid/Guru --</option>
    @foreach($bukuInduk as $bi)
        <option value="{{ $bi->nim }}"
            data-nim="{{ $bi->nim }}"
            data-nama="{{ $bi->nama }}"
            data-gol="{{ $bi->gol }}"
            data-kd="{{ $bi->kd }}"
            data-spp="{{ $bi->spp }}"
            data-guru="{{ $bi->guru }}">
            {{ $bi->nama }} ({{ $bi->nim }})
        </option>
    @endforeach
</select>

        </div>

        <div class="row mt-3">
            <!-- Data Lama -->
            <div class="col-md-6 mb-3">
                <label for="nim" class="form-label">NIM</label>
                <input type="text" name="nim" id="nim" class="form-control" placeholder="Masukkan NIM">
            </div>

            <div class="col-md-6 mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" name="nama" id="nama" class="form-control" placeholder="Masukkan Nama">
            </div>

            <div class="col-md-6 mb-3">
                <label for="gol" class="form-label">Golongan Lama</label>
                <input type="text" name="gol" id="gol" class="form-control" placeholder="Masukkan Golongan Lama">
            </div>

            <div class="col-md-6 mb-3">
                <label for="kd" class="form-label">Kode Lama</label>
                <input type="text" name="kd" id="kd" class="form-control" placeholder="Masukkan Kode Lama">
            </div>

            <div class="col-md-6 mb-3">
                <label for="spp" class="form-label">SPP Lama</label>
                <input type="text" name="spp" id="spp" class="form-control" placeholder="Masukkan SPP Lama">
            </div>

            <div class="col-md-6 mb-3">
                <label for="guru" class="form-label">Guru</label>
                <input type="text" name="guru" id="guru" class="form-control" placeholder="Masukkan Nama Guru">
            </div>

            <!-- Data Baru -->
            <div class="col-md-6 mb-3">
                <label for="gol_baru" class="form-label">Golongan Baru</label>
                <select name="gol_baru" id="gol_baru" class="form-control">
                    <option value="">-- Pilih Golongan Baru --</option>
                    @foreach($hargaSaptataruna->unique('kode') as $harga)
                        <option value="{{ $harga->kode }}">{{ $harga->kode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="kd_baru" class="form-label">Kode Baru</label>
                <select name="kd_baru" id="kd_baru" class="form-control">
                    <option value="">-- Pilih Kode Baru --</option>
                    @foreach(['A','B','C','D','F'] as $kode)
                        <option value="{{ $kode }}">{{ $kode }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="spp_baru" class="form-label">SPP Baru</label>
                <input type="text" name="spp_baru" id="spp_baru" class="form-control" placeholder="SPP Baru" readonly>
            </div>

            <!-- Lain-lain -->
            <div class="col-md-6 mb-3">
                <label for="tanggal_pindah_golongan" class="form-label">Tanggal Pindah Golongan</label>
                <input type="date" name="tanggal_pindah_golongan" id="tanggal_pindah_golongan" class="form-control">
            </div>

            <div class="col-md-12 mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <input type="text" name="keterangan" id="keterangan" class="form-control" placeholder="Masukkan Keterangan">
            </div>

            <div class="col-md-12 mb-3">
                <label for="alasan_pindah" class="form-label">Alasan Pindah Golongan</label>
                <textarea name="alasan_pindah" id="alasan_pindah" rows="3" class="form-control" placeholder="Masukkan Alasan Pindah Golongan"></textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Simpan
        </button>
    </form>
</div>

<!-- Auto-fill Data Lama dari Buku Induk -->
<script>
document.getElementById('buku_induk_nim').addEventListener('change', function() {
    var option = this.options[this.selectedIndex];
    document.getElementById('nim').value = option.getAttribute('data-nim') || '';
    document.getElementById('nama').value = option.getAttribute('data-nama') || '';
    document.getElementById('gol').value = option.getAttribute('data-gol') || '';
    document.getElementById('kd').value = option.getAttribute('data-kd') || '';
    document.getElementById('spp').value = Number(option.getAttribute('data-spp') || 0).toLocaleString('id-ID');
    document.getElementById('guru').value = option.getAttribute('data-guru') || '';
});

// Auto-fill SPP baru berdasarkan Golongan & Kode Baru
var hargaData = @json($hargaSaptataruna);

function updateSppBaru() {
    var gol = document.getElementById('gol_baru').value;
    var kd  = document.getElementById('kd_baru').value;
    var sppInput = document.getElementById('spp_baru');

    if(!gol || !kd){
        sppInput.value = '';
        sppInput.dataset.raw = '';
        return;
    }

    var record = hargaData.find(h => h.kode === gol);
    if(record){
        var rawValue = Number(record[kd.toLowerCase()] || 0);
        sppInput.value = rawValue.toLocaleString('id-ID'); // tampilkan 300.000
        sppInput.dataset.raw = rawValue; // simpan angka asli
    } else {
        sppInput.value = '';
        sppInput.dataset.raw = '';
    }
}

// listener
document.getElementById('gol_baru').addEventListener('change', updateSppBaru);
document.getElementById('kd_baru').addEventListener('change', updateSppBaru);

// pastikan nilai asli dikirim ke server
document.querySelector('form').addEventListener('submit', function(e){
    var sppInput = document.getElementById('spp_baru');
    if(sppInput.dataset.raw){
        sppInput.value = sppInput.dataset.raw;
    }

    var sppLama = document.getElementById('spp');
    if(sppLama){
        // hapus format ribuan sebelum submit
        sppLama.value = sppLama.value.replace(/\./g,'');
    }
});
</script>

@endsection
