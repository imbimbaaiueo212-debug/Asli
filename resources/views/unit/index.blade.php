@extends('layouts.app')

@section('title', 'Daftar Unit biMBA')

@section('content')

<style>
/* ===============================
   GLOBAL & RESET - Elegan & Modern
================================ */
body {
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  font-size: 14px;
  color: #1f2937;
  min-height: 100vh;
}

/* TYPOGRAPHY */
.page-header {
  margin-bottom: 2.5rem;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: #111827;
  background: linear-gradient(to right, #1e40af, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.page-subtitle {
  font-size: 1rem;
  color: #64748b;
  margin-top: 0.375rem;
}

/* CARD - Glassmorphism */
.card-table {
  border: none;
 
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  box-shadow: 
    0 10px 30px -8px rgba(0,0,0,0.08),
    0 25px 60px -15px rgba(0,0,0,0.07),
    inset 0 1px 0 rgba(255,255,255,0.7);

}


/* TABLE */
.table thead th {
  background: linear-gradient(to bottom, #b3ccff, #f1f5f9);
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 700;
  color: #475569;
  padding: 1.25rem 1.5rem;
  border-bottom: 2px solid #e2e8f0;

  /* shadow */
  box-shadow: inset 0 -1px 0 rgba(255,255,255,0.6),
              0 2px 6px rgba(15,23,42,0.12);
}


.table td {
  padding: 1.25rem 1.5rem;
  vertical-align: middle;
  border-bottom: 1px solid #b3ccff;
  color: #334155;
}

.table tbody tr {
  opacity: 0;
  transform: translateY(20px);
  animation: rowAppear 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
  animation-delay: calc(var(--row-index) * 90ms);
  transition: all 0.3s ease;
}

@keyframes rowAppear {
  0%   { opacity: 0; transform: translateY(20px) scale(0.98); }
  70%  { opacity: 0.7; transform: translateY(-4px) scale(1.01); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

.table tbody tr:hover {
  background: rgba(243, 244, 246, 0.6);
  transform: translateY(-3px) scale(1.005);
  box-shadow: 0 10px 25px rgba(0,0,0,0.05);
}

/* BUTTONS UMUM */
.btn {
  border-radius: 0.875rem;
  font-weight: 600;
  padding: 0.6rem 1.4rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border: none;
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.btn-primary:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 35px rgba(59, 130, 246, 0.4);
}

/* AKSI BUTTONS - Dibuat lebih "nimbul" & premium */
.action-btn {
  min-width: 90px;
  font-size: 0.9rem;
  padding: 0.55rem 1.1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: all 0.28s ease;
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.3), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.action-btn:hover::before {
  opacity: 1;
}

.action-btn:hover {
  transform: translateY(-3px) scale(1.04);
  box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

/* Warna spesifik per tombol aksi */
.btn-detail {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.btn-detail:hover {
  background: #dbeafe;
  color: #1e40af;
}

.btn-edit {
  background: #fffbeb;
  color: #92400e;
  border: 1px solid #fde68a;
}

.btn-edit:hover {
  background: #fef3c7;
  color: #92400e;
}

.btn-delete {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.btn-delete:hover {
  background: #fecaca;
  color: #991b1b;
}

/* MOBILE RESPONSIVE */
@media (max-width: 576px) {
  .container { padding: 1.25rem; }
  .page-title { font-size: 1.6rem; }

  .card-table { backdrop-filter: blur(10px); }

  .table thead { display: none; }

  .table tbody tr {
    display: block;
    background: rgba(145, 169, 218, 0.85);
    margin-bottom: 1.5rem;
    padding: 1.5rem 1.25rem;
    border-radius: 1.125rem;
    border: 1px solid rgba(229, 231, 235, 0.7);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    animation-delay: calc(var(--row-index) * 100ms);
  }

  .table tbody tr:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.12);
  }

  .table tbody td {
    display: flex;
    justify-content: space-between;
    padding: 0.9rem 0;
    border: none;
  }

  .table tbody td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #475569;
    flex: 1;
  }

  .btn-group {
    width: 100%;
    margin-top: 1.25rem;
    justify-content: space-between;
    gap: 0.5rem;
  }

  .action-btn {
    min-width: auto;
    flex: 1;
    text-align: center;
  }
}
</style>

<div class="card card-body border">

    <!-- HEADER -->
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-4">
        <div>
            <h2 class="page-title mb-2">Daftar Unit biMBA</h2>
            <p class="page-subtitle">Manajemen data seluruh unit secara terpusat dan real-time</p>
        </div>

        <a href="{{ route('unit.create') }}" class="btn btn-primary d-flex align-items-center shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Tambah Unit Baru
        </a>
    </div>

    <!-- ALERT -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- MAIN CARD -->
    <div class="card card-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0 table-primary">
                    <thead>
                        <tr>
                            <th>No Cabang</th>
                            <th>Nama Unit</th>
                            <th>Staff SOS</th>
                            <th>Telp</th>
                            <th>Email</th>
                            <th>Bank</th>
                            <th>Alamat</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($units as $unit)
                        <tr style="--row-index: {{ $loop->index }};">
                            <td data-label="No Cabang" class="fw-bold">{{ $unit->no_cabang }}</td>
                            <td data-label="Nama Unit" class="fw-bold text-primary">{{ $unit->biMBA_unit }}</td>
                            <td data-label="Staff SOS">{{ $unit->staff_sos ?? '-' }}</td>
                            <td data-label="Telp">{{ $unit->telp ?? '-' }}</td>
                            <td data-label="Email">
                                @if($unit->email)
                                    <a href="mailto:{{ $unit->email }}" class="text-primary text-decoration-none hover-underline">{{ $unit->email }}</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Bank">
                                @if($unit->bank_nama)
                                    <div class="fw-bold">{{ $unit->bank_nama }}</div>
                                    <small class="text-muted d-block">
                                        {{ $unit->bank_nomor ?? '-' }}<br>
                                        {{ $unit->bank_atas_nama ?? '' }}
                                    </small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Alamat">
                                <small class="text-muted">
                                    {{ $unit->alamat_jalan ?? '-' }},<br>
                                    {{ $unit->alamat_kota_kab ?? '-' }}
                                </small>
                            </td>
                            <td class="text-center" data-label="Aksi">
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('unit.show', $unit->id) }}" 
           class="action-btn btn-detail d-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-eye"></i> Detail
        </a>
        <a href="{{ route('unit.edit', $unit->id) }}" 
           class="action-btn btn-edit d-flex align-items-center justify-content-center gap-1">
            <i class="bi bi-pencil"></i> Edit
        </a>

        @if (auth()->user()?->role === 'admin')
            <form action="{{ route('unit.destroy', $unit->id) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus unit {{ addslashes($unit->biMBA_unit) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="action-btn btn-delete d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </form>
        @endif
    </div>
</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-4 opacity-50"></i>
                                <div class="fw-semibold fs-5 mb-2">Belum ada data unit biMBA</div>
                                <p class="mb-4">Mulai dengan menambahkan unit baru untuk memulai pengelolaan</p>
                                <a href="{{ route('unit.create') }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-plus-lg me-2"></i>Tambah Unit Pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PAGINATION -->
    @if($units->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $units->links('pagination::bootstrap-5') }}
        </div>
    @endif

</div>

@endsection