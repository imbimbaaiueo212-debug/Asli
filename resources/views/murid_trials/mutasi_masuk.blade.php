@extends('layouts.app')

@section('title', 'Mutasi Masuk dari Murid Trial')

@section('content')
    <div class="container py-4">
        <h3 class="mb-3">Mutasi Masuk: {{ $student->nama }}</h3>

        @if (session('info'))
        <div class="alert alert-info">{{ session('info') }}</div> @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Periksa input:</strong>
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @php
            $pref = $prefill ?? [];
            $tahapanOptions = $tahapanOptions ?? ['Persiapan', 'Lanjutan'];
            $golOptions = $golOptions ?? [];
            $kdOptions = $kdOptions ?? ['A', 'B', 'C', 'D', 'E', 'F'];
            $sppMapping = $sppMapping ?? [];
        @endphp

        <form action="{{ route('murid_trials.mutation_store', $murid_trial->id) }}" method="POST" class="card shadow-sm">
            @csrf
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Murid</label>
                    <div class="input-group">
                        <span class="input-group-text">Nama</span>
                        <input type="text" class="form-control" value="{{ $student->nama }}" readonly>
                        <span class="input-group-text">NIM</span>
                        <input type="text" class="form-control" value="{{ $student->nim }}" readonly>
                    </div>
                    <div class="form-text">ID: {{ $student->id }}</div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mutasi</label>
                        <input id="tanggalMutasi" type="date" name="tanggal_mutasi" class="form-control"
                            value="{{ old('tanggal_mutasi', $pref['tanggal_mutasi'] ?? now()->toDateString()) }}" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Asal Unit (biMBA sebelumnya)</label>
                        <input type="text" name="asal_unit" class="form-control"
                            value="{{ old('asal_unit', $pref['asal_unit'] ?? '') }}"
                            placeholder="Contoh: biMBA AIUEO Unit ABC">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kode Unit Asal</label>
                        <input type="text" name="asal_kode" class="form-control"
                            value="{{ old('asal_kode', $pref['asal_kode'] ?? '') }}" placeholder="Mis: 01045">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="alasan" class="form-control"
                            value="{{ old('alasan', $pref['alasan'] ?? '') }}"
                            placeholder="Pindah domisili / dekat rumah / dll">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="form-control"
                            placeholder="Keterangan tambahan...">{{ old('keterangan', $pref['keterangan'] ?? '') }}</textarea>
                    </div>
                </div>

                {{-- ====================== Tambahan: Data Lahir & Pindah ====================== --}}
                <hr>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control"
                               value="{{ old('tempat_lahir') }}" placeholder="mis: Jakarta">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" class="form-control"
                               value="{{ old('tgl_lahir', isset($student->tgl_lahir) ? \Illuminate\Support\Carbon::parse($student->tgl_lahir)->format('Y-m-d') : '') }}">
                        <div class="form-text">Terisi otomatis bila data murid sudah ada.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status Pindah</label>
                        <input type="text" name="status_pindah" class="form-control"
                               value="{{ old('status_pindah','Pindah Masuk') }}" placeholder="mis: Pindah Masuk">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Pindah</label>
                        <input id="tanggalPindah" type="date" name="tanggal_pindah" class="form-control"
                               value="{{ old('tanggal_pindah', $pref['tanggal_mutasi'] ?? now()->toDateString()) }}" readonly>
                        <div class="form-text">Otomatis mengikuti Tanggal Mutasi.</div>
                    </div>
                </div>
                {{-- ==================== /Tambahan: Data Lahir & Pindah ====================== --}}

                {{-- ====================== Tahapan, Kelas, Gol, KD, SPP ====================== --}}
                <hr>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tahapan</label>
                        <select name="tahap" class="form-select" required>
                            <option value="">— pilih tahapan —</option>
                            @foreach($tahapanOptions as $t)
                                <option value="{{ $t }}" @selected(old('tahap') == $t)>{{ $t }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Contoh: Persiapan / Lanjutan.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Kelas</label>
                        <input type="text" name="kelas" class="form-control" value="{{ old('kelas', $student->kelas) }}"
                            placeholder="mis: A / B / C" readonly>
                        <div class="form-text">Kelas ditentukan dari data murid dan tidak dapat diubah.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Gol</label>
                        <select id="selGol" name="gol" class="form-select" required>
                            <option value="">— pilih gol —</option>
                            @foreach($golOptions as $g)
                                <option value="{{ $g }}" @selected(old('gol') == $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Diambil dari kolom <em>kode</em> pada Harga Sapta Taruna.</div>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">KD</label>
                        <select id="selKd" name="kd" class="form-select" required>
                            <option value="">—</option>
                            @foreach($kdOptions as $k)
                                <option value="{{ $k }}" @selected(old('kd') == $k)>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">SPP</label>
                        <input id="inpSpp" type="number" name="spp" class="form-control" value="{{ old('spp') }}"
                            placeholder="otomatis dari Gol + KD" readonly>
                        <div class="form-text">Nilai terisi otomatis dari Gol + KD.</div>
                    </div>
                </div>
                {{-- ==================== /Tahapan, Kelas, Gol, KD, SPP ====================== --}}

                <hr>
                <div class="small text-muted">
                    Sumber: Infinite Management
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('murid_trials.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
            </div>
        </form>
    </div>

    {{-- Script: auto-hit SPP dari mapping Harga Sapta Taruna + sinkron tanggal_pindah --}}
    <script>
        // Bentuk: { "GOL": { "A": 50000, "B": 60000, ... }, ... }
        const SPP_MAP = @json($sppMapping);

        const selGol = document.getElementById('selGol');
        const selKd = document.getElementById('selKd');
        const inpSpp = document.getElementById('inpSpp');

        const tanggalMutasi = document.getElementById('tanggalMutasi');
        const tanggalPindah = document.getElementById('tanggalPindah');

        function updateSPP() {
            const g = selGol.value || '';
            const k = (selKd.value || '').toUpperCase();
            const harga = (SPP_MAP[g] && SPP_MAP[g][k]) ? SPP_MAP[g][k] : '';
            if (harga !== '') inpSpp.value = harga;
        }

        function syncTanggalPindah() {
            if (tanggalMutasi && tanggalPindah) {
                tanggalPindah.value = tanggalMutasi.value || '';
            }
        }

        selGol.addEventListener('change', updateSPP);
        selKd.addEventListener('change', updateSPP);
        tanggalMutasi.addEventListener('change', syncTanggalPindah);

        // Saat halaman selesai muat
        window.addEventListener('load', () => {
            updateSPP();
            syncTanggalPindah();
        });
    </script>
@endsection
