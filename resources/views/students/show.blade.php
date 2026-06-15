@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Detail Murid - <strong>{{ $student->nama }}</strong></h2>
        <div>
            <a href="{{ route('students.edit', $student) }}" class="btn btn-warning">Edit Data</a>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Informasi Murid</strong>
                </div>
                <div class="card-body">

                    @php
                        // Logic Jadwal
                        $hariTampil = $student->hari ?: null;
                        $jamTampil = $student->jam 
                            ? str_replace('.', ':', preg_replace('/^jam\s*/i', '', $student->jam)) 
                            : null;
                        $jadwal = trim(implode(', ', array_filter([$hariTampil, $jamTampil]))) ?: '—';

                        // No Telepon
                        $telp = $student->hp_ayah ?: ($student->hp_ibu ?: ($student->no_telp ?: '—'));
                    @endphp

                    <div class="row g-4">

                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">NIM</label>
                                <p class="fw-bold mb-0">{{ $student->nim ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Tanggal Lahir</label>
                                <p class="mb-0">{{ $student->tgl_lahir ? \Carbon\Carbon::parse($student->tgl_lahir)->format('d F Y') : '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Jadwal</label>
                                <p class="mb-0">{{ $jadwal }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Alamat Lengkap</label>
                                <p class="mb-0">
                                    {{ $student->alamat ?? '' }}
                                    @if($student->rt || $student->rw)
                                        <br>RT {{ $student->rt ?? '-' }} / RW {{ $student->rw ?? '-' }}
                                    @endif
                                    @if($student->kelurahan)
                                        <br>Kel. {{ $student->kelurahan }}
                                    @endif
                                    @if($student->kecamatan)
                                        <br>Kec. {{ $student->kecamatan }}
                                    @endif
                                    @if($student->kota || $student->kabupaten)
                                        <br>{{ $student->kota ?? $student->kabupaten }}
                                    @endif
                                    @if($student->provinsi)
                                        , {{ $student->provinsi }}
                                    @endif
                                </p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Ayah</label>
                                <p class="mb-0">{{ $student->nama_ayah ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">No. Telepon</label>
                                <p class="mb-0">{{ $telp }}</p>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Nama Murid</label>
                                <p class="fw-bold mb-0 fs-5">{{ $student->nama }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Sumber Pendaftaran</label>
                                <p class="mb-0">{{ $student->sumber_pendaftaran ?: $student->source ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Informasi</label>
                                <p class="mb-0">{{ $student->informasi_bimba ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Nama Humas</label>
                                <p class="mb-0 fw-medium">{{ $student->informasi_humas_nama ?? '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Tanggal Daftar</label>
                                <p class="mb-0">{{ $student->tanggal_masuk ? \Carbon\Carbon::parse($student->tanggal_masuk)->format('d F Y') : '—' }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Ibu</label>
                                <p class="mb-0">{{ $student->nama_ibu ?? '—' }}</p>
                                @if($student->hp_ibu)
                                    <small class="text-muted">HP: {{ $student->hp_ibu }}</small>
                                @endif
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-bold">Email</label>
                                <p class="mb-0">{{ $student->email ?? '—' }}</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection