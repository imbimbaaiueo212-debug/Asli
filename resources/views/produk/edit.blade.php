@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Edit Produk: {{ $produk->nama_produk }}
                    </h4>
                </div>

                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('produk.update', $produk->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Unit biMBA (Hanya untuk Admin) -->
                        @if(auth()->user()->isAdminUser())
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">
                                        Unit biMBA <span class="text-danger">*</span>
                                    </label>
                                    <select name="bimba_unit" class="form-select @error('bimba_unit') is-invalid @enderror" required>
                                        <option value="">- Pilih Unit biMBA -</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->biMBA_unit }}"
                                                {{ old('bimba_unit', $produk->unit->biMBA_unit ?? '') == $unit->biMBA_unit ? 'selected' : '' }}>
                                                {{ $unit->biMBA_unit }} - {{ $unit->nama ?? $unit->label ?? 'Unit' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bimba_unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Pilih unit biMBA tempat produk ini berlaku.</small>
                                </div>
                            </div>
                        @endif

                        <!-- Informasi Utama Produk -->
                        <h5 class="mb-4 text-primary border-bottom pb-2">
                            <i class="fas fa-info-circle me-2"></i>Informasi Utama
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kode Produk <span class="text-danger">*</span></label>
                                <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror"
                                       value="{{ old('kode', $produk->kode) }}" required>
                                @error('kode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" class="form-control @error('nama_produk') is-invalid @enderror"
                                       value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                                @error('nama_produk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                                <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror"
                                       value="{{ old('kategori', $produk->kategori) }}" required>
                                @error('kategori')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Jenis <span class="text-danger">*</span></label>
                                <input type="text" name="jenis" class="form-control @error('jenis') is-invalid @enderror"
                                       value="{{ old('jenis', $produk->jenis) }}" required>
                                @error('jenis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Label <span class="text-danger">*</span></label>
                                <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
                                       value="{{ old('label', $produk->label) }}" required>
                                @error('label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Spesifikasi -->
                        <h5 class="mb-4 mt-5 text-primary border-bottom pb-2">
                            <i class="fas fa-cogs me-2"></i>Spesifikasi
                        </h5>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                                <input type="text" name="satuan" class="form-control @error('satuan') is-invalid @enderror"
                                       value="{{ old('satuan', $produk->satuan) }}" required>
                                @error('satuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Berat (kg)</label>
                                <input type="number" step="0.01" name="berat" class="form-control @error('berat') is-invalid @enderror"
                                       value="{{ old('berat', $produk->berat) }}" min="0" required>
                                @error('berat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Harga (Rp)</label>
                                <input type="number" name="harga" class="form-control @error('harga') is-invalid @enderror"
                                       value="{{ old('harga', $produk->harga) }}" min="0" required>
                                @error('harga')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Tipe Produk <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">- Pilih Tipe -</option>
                                    <option value="Satuan" {{ old('status', $produk->status) == 'Satuan' ? 'selected' : '' }}>Satuan</option>
                                    <option value="Paket" {{ old('status', $produk->status) == 'Paket' ? 'selected' : '' }}>Paket</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih apakah produk dijual per satuan atau paket</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Isi / Varian</label>
                                <input type="text" name="isi" class="form-control @error('isi') is-invalid @enderror"
                                       value="{{ old('isi', $produk->isi) }}" placeholder="ex: Size S, Merah">
                                @error('isi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Opsional: varian warna, ukuran, dll</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pendataan <span class="text-danger">*</span></label>
                                <input type="text" name="pendataan" class="form-control @error('pendataan') is-invalid @enderror"
                                       value="{{ old('pendataan', $produk->pendataan) }}" required>
                                @error('pendataan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="d-flex justify-content-between mt-5 pt-4 border-top">
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary btn-lg px-5">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>

                            <button type="submit" class="btn btn-warning btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Update Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection