@extends('layouts.app')

@section('title', 'MBC Murid')

@section('content')
<div class="card card=body">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="mb-4">Daftar MBC Murid</h1>

            <!-- Tombol Tambah -->
            <a href="{{ route('mbc-murid.create') }}" class="btn btn-primary mb-3">
                Tambah Data
            </a>

            <!-- Notifikasi Sukses -->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Tabel Daftar MBC Murid -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No Cabang</th>
                            <th>Nama Unit</th>
                            <th>No Pembayaran</th>
                            <th>Nama Murid</th>
                            <th>Kelas</th>
                            <th>Gol & Kode</th>
                            <th>SPP</th>
                            <th>Wali Murid</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($murid as $index => $m)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $m->no_cabang ?? '-' }}</td>
                                <td>{{ $m->nama_unit ?? '-' }}</td>
                                <td>{{ $m->no_pembayaran ?? '-' }}</td>
                                <td>{{ $m->nama_murid ?? '-' }}</td>
                                <td>{{ $m->kelas ?? '-' }}</td>
                                <td>{{ $m->golongan_kode ?? '-' }}</td>
                                <td>{{ $m->spp ?? '-' }}</td>
                                <td>{{ $m->wali_murid ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('mbc-murid.edit', $m->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('mbc-murid.destroy', $m->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data MBC Murid.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Informasi Virtual Account -->
    <div class="card mt-5 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">VIRTUAL ACCOUNT (VIA BANK LAIN)</h5>

            <strong>a) Format Kode Nomor Pembayaran</strong>

            <div class="table-responsive mt-2">
                <table class="table table-bordered w-75">
                    <thead class="table-light">
                        <tr>
                            <th>Keterangan</th>
                            <th>Kode Bank</th>
                            <th>Kode Kelas</th>
                            <th>No Cabang</th>
                            <th>NIM</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jumlah</td>
                            <td>5 Digit</td>
                            <td>2 Digit</td>
                            <td>5 Digit</td>
                            <td>4 Digit</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-center fw-bold">Virtual Account</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <strong>b) Cara Pembayaran (Via Bank Mandiri)</strong>
            <pre class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.9em;">
→ Masuk ke menu Pembayaran (Bayar/Beli)
→ Pilih KE MENU PENDIDIKAN
→ Masukkan kode Perusahaan 88912 (jika diminta)
→ Atau Pilih AIUEO biMBA VA
→ Masukkan nomor REKENING MURID
→ Masukkan jumlah TAGIHAN SPP
→ Jika profil murid yang tertera sesuai maka tekan Benar/Lanjut,
   jika tidak tekan Salah/Cancel
→ Simpan struk ATM sebagai bukti pembayaran
            </pre>

            <strong>c) Cara Pembayaran (Via Bank Lain)</strong>
            <pre class="bg-light p-3 rounded" style="white-space: pre-wrap; font-size: 0.9em;">
→ Masuk ke menu TRANSFER
→ Pilih KE REKENING BANK LAIN
→ Masukkan kode BANK MANDIRI (008)
→ Masukkan jumlah TAGIHAN SPP
→ Masukkan nomor REKENING MURID
→ Jika profil murid yang tertera sesuai maka tekan Benar/Lanjut,
   jika tidak tekan Salah/Cancel
→ Simpan struk ATM sebagai bukti pembayaran
            </pre>
        </div>
    </div>
</div>
@endsection
