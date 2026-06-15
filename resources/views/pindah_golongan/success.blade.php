@extends('layouts.app') 

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-white">Rencana Pindah Golongan Berhasil Disimpan</div>

                <div class="card-body">
                    <p class="lead">Data rencana pindah golongan untuk **{{ $pindahGolongan->nama }}** (NIM: {{ $pindahGolongan->nim }}) telah berhasil dibuat.</p>
                    
                    <div class="alert alert-info">
                        <strong>Langkah Penting Selanjutnya:</strong> Kirim tautan di bawah ini kepada orang tua murid untuk konfirmasi dan mengisi keterangan pindah golongan.
                    </div>

                    @if($googleFormLink)
                    <div class="p-3 mb-4 bg-light border rounded">
                        <h5>Tautan Konfirmasi Google Form:</h5>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $googleFormLink }}" id="gformLink" readonly>
                            <button class="btn btn-outline-primary" type="button" 
                                onclick="navigator.clipboard.writeText(document.getElementById('gformLink').value); alert('Link berhasil disalin!');">
                                <i class="fas fa-copy"></i> Salin Link
                            </button>
                        </div>
                        <p class="mt-2 text-muted">
                            <a href="{{ $googleFormLink }}" target="_blank">Klik untuk Menguji Form</a>
                        </p>
                    </div>
                    @else
                        <div class="alert alert-danger">Link Google Form tidak ditemukan. Pastikan konstanta `GF_URL` dan `GF_ENTRY_...` sudah diisi dengan benar di Controller.</div>
                    @endif

                    <a href="{{ route('pindah-golongan.index') }}" class="btn btn-secondary mt-3">Kembali ke Daftar Pindah Golongan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection