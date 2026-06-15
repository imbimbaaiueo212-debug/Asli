@extends('layouts.app')

@section('title', 'Murid Trial')

@section('content')
    <div class="container">
        <h2>Data Murid Mutasi</h2>

        <div class="d-flex justify-content-between mb-3">
            <!-- Tombol Tambah Murid Trial (Google Form) -->
            <a href="https://docs.google.com/forms/d/e/1FAIpQLSeayY3YYyUmVyWz1HTlnM1DPrB5PsLHLbyqsH-tX_lPYK0BLA/viewform?usp=pp_url&entry.660813711=biMBA+AIUEO"
                target="_blank" class="btn btn-warning">
                Tambah Murid Trial
            </a>

            <!-- Tombol Sinkronisasi -->
            @if (Auth::check())
                <form action="{{ route('murid_trials.sync') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Update MTB</button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- NEW: tampilkan info & error bila ada --}}
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (session('form_url'))
            <div class="alert alert-info">
                Link Komitmen untuk orang tua:
                <a href="{{ session('form_url') }}" target="_blank" rel="noopener">Buka Google Form</a>
            </div>
        @endif

        <form action="{{ route('murid_trials.syncCommitment') }}" method="POST" class="mb-3">
              @csrf
              <input type="hidden" name="sheet" value="LEMBAR KOMITMEN">
              <button type="submit" class="btn btn-success"
                onclick="return confirm('Tarik data komitmen dari Google Sheet sekarang? Proses ini akan membaca semua baris form dan mungkin memakan waktu beberapa detik.')">
                    <i class="fas fa-sync-alt"></i> Sinkron Komitmen Orang Tua
                  </button>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="14" class="text-center bg-info">DATA MURID TRIAL BARU</th> {{-- +1 kolom aksi tambahan --}}
                </tr>
                <tr>
                    <th>No</th>
                    <th>Tgl Mulai</th>
                    <th>Kelas</th>
                    <th>Nama</th>
                    <th>Tgl Lahir</th>
                    <th>Usia</th>
                    <th>Guru Trial</th>
                    <th>Info</th>
                    <th>Orangtua</th>
                    <th>No Telp/HP</th>
                    <th>Alamat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                    {{-- NEW: Aksi Mutasi --}}
                    <th>Aksi Mutasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach (($murid_trials ?? []) as $i => $murid)
                    <tr>
                        <td>{{ $murid_trials->firstItem() + $i }}</td>
                        <td>{{ $murid->tgl_mulai ? \Illuminate\Support\Carbon::parse($murid->tgl_mulai)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $murid->kelas }}</td>
                        <td>{{ $murid->nama }}</td>
                        <td>{{ $murid->tgl_lahir ? \Illuminate\Support\Carbon::parse($murid->tgl_lahir)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $murid->usia }}</td>
                        <td>{{ $murid->guru_trial }}</td>
                        <td>{{ $murid->info }}</td>
                        <td>{{ $murid->orangtua }}</td>
                        <td>{{ $murid->no_telp }}</td>
                        <td>{{ $murid->alamat }}</td>

                        <td>
                            <form action="{{ route('murid_trials.updateStatus', $murid->id) }}" method="POST" class="d-flex gap-2">
                                @csrf @method('PATCH')
                                <select name="status_trial" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                    {{-- Placeholder: tampil bila status masih NULL --}}
                                    <option value="" disabled {{ is_null($murid->status_trial) ? 'selected' : '' }}>— Pilih status —</option>

                                    <option value="aktif" @selected($murid->status_trial === 'aktif')>Trial Aktif</option>
                                    <option value="lanjut_daftar" @selected($murid->status_trial === 'lanjut_daftar')>Lanjut Daftar</option>
                                    <option value="batal" @selected($murid->status_trial === 'batal')>Batal</option>

                                    {{-- NEW: aksi cepat mutasi dari dropdown (akan di-intersep di controller) --}}
                                    <option value="tambah_mutasi">➕ Tambah Mutasi (Masuk)</option>
                                </select>
                            </form>
                        </td>

                        <td>
                            @if ($murid->student)
                                <a href="{{ route('registrations.create', [
                                        'student_id' => $murid->student->id,
                                        'tahun_ajaran' => \App\Models\Registration::currentAcademicYear()
                                ]) }}" class="btn btn-success btn-sm">
                                    Pendaftaran
                                </a>
                            @else
                                <a href="{{ route('murid_trials.create_registration', $murid->id) }}" class="btn btn-success btn-sm">
                                    Pendaftaran
                                </a>
                            @endif

                            <form action="{{ route('murid_trials.destroy', $murid->id) }}" method="POST" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">
                                    Hapus
                                </button>
                            </form>
                        </td>

                        {{-- NEW: Tombol langsung Mutasi Masuk (alternatif dari dropdown) --}}
                        <td>
                            <form action="{{ route('murid_trials.updateStatus', $murid->id) }}" method="POST" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status_trial" value="tambah_mutasi">
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    Mutasi Masuk
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

@if (isset($murid_trials) && method_exists($murid_trials, 'links'))
    {{ $murid_trials->links() }}
@endif
    </div>
@endsection
