@extends('layouts.app')

@section('title', 'Riwayat Perubahan - ' . ($bukuInduk->nama ?? 'Murid'))

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                Riwayat Perubahan Data Murid
                <br>
                <small>{{ $bukuInduk->nama ?? 'Nama tidak tersedia' }} ({{ $bukuInduk->nim ?? '-' }})</small>
            </h4>
            <a href="{{ route('buku_induk.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="card-body">
            @if($histories->isEmpty())
                <div class="alert alert-info text-center py-4 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Belum ada riwayat perubahan untuk murid ini.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 18%">Waktu</th>
                                <th style="width: 12%">Aksi</th>
                                <th style="width: 15%">User</th>
                                <th>Perubahan Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($histories as $history)
                                <tr>
                                    <td>
                                        <strong>{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $history->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</small>
                                    </td>

                                    <td>
                                        @switch(strtolower($history->action))
                                            @case('create')
                                            @case('import_create')
                                                <span class="badge bg-success px-3 py-2">Baru</span>
                                                @break
                                            @case('update')
                                            @case('update_partial')
                                            @case('update_import')
                                                <span class="badge bg-warning px-3 py-2">Update</span>
                                                @break
                                            @case('delete')
                                                <span class="badge bg-danger px-3 py-2">Hapus</span>
                                                @break
                                            @case('import')
                                                <span class="badge bg-info px-3 py-2">Import</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary px-3 py-2">{{ ucfirst($history->action) }}</span>
                                        @endswitch
                                    </td>

                                    <td>
                                        {{ $history->user ?? 'system' }}
                                    </td>

                                    <td>
    {{-- 
        Ambil data lama (old_data) dan baru (new_data) dari history
        Default ke array kosong jika null agar aman dari error
    --}}
    @php
        $old = $history->old_data ?? [];
        $new = $history->new_data ?? [];
        
        // Jika data lama/baru tersimpan sebagai STRING (bukan array) → biasanya karena data history lama
        // Kita paksa decode jadi array supaya bisa di-loop dengan aman
        if (is_string($old)) {
            $old = json_decode($old, true) ?? [];   // decode JSON → array, jika gagal jadi array kosong
        }
        if (is_string($new)) {
            $new = json_decode($new, true) ?? [];
        }
        
        // Kumpulkan HANYA field yang benar-benar berubah (old != new)
        // Ini mencegah menampilkan semua field, hanya yang relevan saja
        $changedFields = [];
        foreach ($old as $key => $oldValue) {
            $newValue = $new[$key] ?? null;
            if ($oldValue != $newValue) {           // Hanya simpan jika ada perubahan
                $changedFields[$key] = [
                    'old'  => $oldValue,
                    'new'  => $newValue,
                ];
            }
        }
    @endphp

    {{-- 
        Jika ADA perubahan (changedFields tidak kosong) DAN bukan aksi delete
        Tampilkan daftar perubahan dalam bentuk list yang rapi
    --}}
    @if(!empty($changedFields) && $history->action !== 'delete')
        <ul class="list-group list-group-flush small mb-0">
            @foreach($changedFields as $key => $change)
                @php
                    // Ubah nama field jadi lebih readable (contoh: tgl_masuk → Tgl Masuk)
                    $formattedKey = ucfirst(str_replace('_', ' ', $key));
                    
                    // Format khusus untuk field 'info' → tampilkan "dari ... → ..."
                    if ($key === 'info') {
                        $oldDisplay = $change['old'] ?: '<em>kosong</em>';
                        $newDisplay = $change['new'] ?: '<em>kosong</em>';
                        $display = "dari <strong>$oldDisplay</strong> → <strong>$newDisplay</strong>";
                    } else {
                        // Format default untuk field lain
                        $oldDisplay = $change['old'] ?: '<em>kosong</em>';
                        $newDisplay = $change['new'] ?: '<em>kosong</em>';
                        $display = "$oldDisplay → $newDisplay";
                    }
                @endphp
                
                <li class="list-group-item px-0 py-1 border-0">
                    <strong>{{ $formattedKey }} :</strong>
                    {{-- 
                        {!! !!} digunakan karena $display mengandung tag HTML (<strong>, <em>)
                        Jika pakai {{ }} maka tag HTML akan muncul sebagai teks biasa (bocor)
                    --}}
                    <span class="text-muted ms-1">{!! $display !!}</span>
                </li>
            @endforeach
        </ul>

    {{-- Kasus khusus: aksi create atau import_create --}}
    @elseif($history->action === 'create' || $history->action === 'import_create')
        <div class="alert alert-success small mb-0 py-2">
            <i class="fas fa-plus-circle me-2"></i>
            Data murid baru berhasil dibuat / diimport
        </div>

    {{-- Kasus khusus: aksi delete --}}
    @elseif($history->action === 'delete')
        <div class="alert alert-danger small mb-0 py-2">
            <i class="fas fa-trash-alt me-2"></i>
            Data murid dihapus
        </div>

    {{-- Jika tidak ada perubahan yang bisa ditampilkan --}}
    @else
        <small class="text-muted fst-italic">
            Tidak ada detail perubahan yang tercatat
        </small>
    @endif

    {{-- 
        Tampilkan info waktu update dalam format yang mudah dibaca
        Sudah sesuai permintaan: "Diperbarui: 22 Jan 2026 13:58 WIB"
        Menggunakan timezone Asia/Jakarta supaya sesuai WIB
    --}}
    @if($history->updated_at)
        <small class="text-muted d-block mt-2">
            Diperbarui: {{ $history->updated_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
        </small>
    @endif
</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $histories->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>

        <div class="card-footer text-muted small text-center">
            Total riwayat: {{ $histories->count() }} • Ditampilkan dari yang terbaru
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .list-group-item {
        background: transparent;
    }
    .badge {
        font-size: 0.9rem;
        min-width: 80px;
        text-align: center;
    }
</style>
@endpush