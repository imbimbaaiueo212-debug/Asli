@extends('layouts.app')

@section('content')
    <div class="container">
        <h3 class="mb-4">Tambah Data SPP</h3>

        <form action="{{ route('spp.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Pilih NIM dari data penerimaan --}}
                <div class="col-md-6 mb-3">
                    <label>NIM</label>
                    {{-- Pilih NIM (contoh memakai $penerimaan) --}}
                    <select name="nim" id="nim" class="form-control" required>
                        <option value="">-- Pilih NIM --</option>
                        @foreach ($penerimaan as $p)
                            <option value="{{ str_pad($p->nim, 4, '0', STR_PAD_LEFT) }}" data-nama="{{ $p->nama_murid }}"
                                data-kelas="{{ $p->kelas }}" data-gol="{{ $p->gol }}"
                                data-kd="{{ $p->kd }}" data-status="{{ $p->status }}"
                                data-spp="{{ $p->spp }}" data-spp="{{ $p->spp }}"
                                data-guru="{{ $p->guru }}" data-tahap="{{ $tahapMapping[$p->nim] ?? '' }}">
                                {{ str_pad($p->nim, 4, '0', STR_PAD_LEFT) }} - {{ $p->nama_murid }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nama Murid</label>
                    <input type="text" name="nama_murid" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Kelas</label>
                    <input type="text" name="kelas" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Tahap</label>
                    {{-- Dropdown Tahap --}}
                    <select name="tahap" id="tahap" class="form-control">
                        <option value="">-- Pilih Tahap --</option>
                        @foreach ($tahapanOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Gol</label>
                    <input type="text" name="gol" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>KD</label>
                    <input type="text" name="kd" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>SPP</label>
                    <input type="number" name="spp" class="form-control" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <input type="text" name="stts" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Petugas Trial</label>
                    <input type="text" name="petugas_trial" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label>Guru</label>
                    <input type="text" name="guru" class="form-control" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Note</label>
                    <input type="text" name="note" class="form-control">
                </div>
                <div class="col-md-12 mb-3">
                    <label>Keterangan SPP</label>
                    <textarea name="keterangan_spp" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('spp.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>

    {{-- script isi otomatis --}}
    <script>
        document.getElementById('nim').addEventListener('change', function() {
            let option = this.options[this.selectedIndex];

            document.querySelector('input[name="nama_murid"]').value = option.dataset.nama;
            document.querySelector('input[name="kelas"]').value = option.dataset.kelas;
            document.querySelector('input[name="gol"]').value = option.dataset.gol;
            document.querySelector('input[name="kd"]').value = option.dataset.kd;
            document.querySelector('input[name="stts"]').value = option.dataset.status;
            document.querySelector('input[name="guru"]').value = option.dataset.guru;
            document.querySelector('input[name="spp"]').value = option.dataset.spp;

            // set tahap sesuai data dari buku_induk
            let tahapSelect = document.getElementById('tahap');
            if (option.dataset.tahap) {
                tahapSelect.value = option.dataset.tahap;
            } else {
                tahapSelect.value = "";
            }
        });
    </script>
@endsection
