@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-dark d-flex justify-content-between align-items-center">
            <h4>Data Lemburan</h4>
            <a href="{{ route('lembur.create') }}" class="btn btn-light">
                <i class="fas fa-plus"></i> Tambah Lembur
            </a>
        </div>
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th>Tgl Lembur</th>
                        <th>Jam Awal</th>
                        <th>Jam Selesai</th>
                        <th>Keterangan Lembur</th>
                        <th>Total Jam</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lembur as $item)
                    <tr>
                        <td>{{ $item->profile->nik ?? '-' }}</td>
                        <td>{{ $item->profile->nama ?? '-' }}</td>
                        <td>{{ $item->profile->jabatan ?? '-' }}</td>
                        <td>{{ $item->tgl_lembur?->format('d/m/Y') }}</td>
                        
                        <!-- Hanya Jam Saja -->
                        <td><strong>{{ $item->jam_awal?->format('H:i') }}</strong></td>
                        <td><strong>{{ $item->jam_selesai?->format('H:i') }}</strong></td>

                        <!-- Kolom Keterangan Lembur -->
                        <td class="text-truncate" 
                            style="max-width: 180px; cursor: pointer;" 
                            onclick="showKeterangan(`{{ addslashes($item->keterangan ?? '-') }}`)"
                            title="Klik untuk melihat keterangan lengkap">
                            
                            {{ Str::limit($item->keterangan ?? '-', 35) }}
                            
                            @if(strlen($item->keterangan ?? '') > 35)
                                <small class="text-primary ms-1">...</small>
                            @endif
                        </td>
                        
                        <td><strong>{{ number_format($item->total_jam ?? 0, 2) }} Jam</strong></td>
                        
                        <td>
                            <span class="badge bg-{{ $item->status == 'Disetujui' ? 'success' : ($item->status == 'Ditolak' ? 'danger' : 'warning') }}">
                                {{ $item->status ?? 'Diajukan' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('lembur.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('lembur.destroy', $item) }}" method="POST" style="display:inline" 
                                  onsubmit="return confirm('Yakin hapus data lembur ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $lembur->links() }}
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
function showKeterangan(text) {
    if (!text || text.trim() === '' || text === '-') {
        alert("Tidak ada keterangan.");
        return;
    }
    
    // Buat modal sederhana
    let modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
        background: white; padding: 25px; border-radius: 10px; 
        box-shadow: 0 0 20px rgba(0,0,0,0.3); z-index: 9999; 
        max-width: 500px; width: 90%; text-align: left;
    `;
    
    modal.innerHTML = `
        <h5 class="mb-3">Keterangan Lembur</h5>
        <p style="white-space: pre-wrap; line-height: 1.6;">${text}</p>
        <button onclick="this.parentElement.remove()" 
                class="btn btn-secondary btn-sm mt-3">Tutup</button>
    `;
    
    document.body.appendChild(modal);
    
    // Klik di luar modal untuk close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.remove();
    });
}
</script>
@endpush
@endsection