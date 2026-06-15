@extends('layouts.app')

@section('title', 'Profil Unit biMBA - ' . $unit->biMBA_unit)

@section('content')

<style>
/* ===============================
   GLOBAL & BACKGROUND
================================ */
body {
  background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  color: #1f2937;
  min-height: 100vh;
}

/* ===============================
   TYPOGRAPHY
================================ */
h2, h4 {
  font-weight: 800;
  letter-spacing: -0.025em;
}

h2 {
  font-size: 2rem;
  background: linear-gradient(to right, #1e40af, #3b82f6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

h4 {
  font-size: 1.25rem;
  color: #334155;
  margin: 2.5rem 0 1rem;
  position: relative;
}

h4::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 60px;
  height: 3px;
  background: linear-gradient(to right, #3b82f6, #60a5fa);
  border-radius: 3px;
}

/* ===============================
   CARD & CONTAINER
================================ */
.container {
  max-width: 900px;
  padding: 2rem 1rem;
}

.profile-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-radius: 1.5rem;
  border: 1px solid rgba(229, 231, 235, 0.5);
  box-shadow: 
    0 10px 35px -10px rgba(0,0,0,0.1),
    0 20px 60px -20px rgba(0,0,0,0.08),
    inset 0 1px 0 rgba(255,255,255,0.7);
  overflow: hidden;
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  position: relative;
}

.profile-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 25px 70px -15px rgba(0,0,0,0.15);
}

/* ===============================
   TOMBOL KEMBALI - Elegan & Nimbul
================================ */
.btn-back {
  border-radius: 0.875rem;
  font-weight: 600;
  padding: 0.55rem 1.2rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  background: white;
  border: 1px solid #cbd5e1;
  color: #475569;
}

.btn-back:hover {
  background: #f1f5f9;
  color: #1e40af;
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.15);
  border-color: #bfdbfe;
}

/* ===============================
   TABLE STYLE - Elegant & Modern
================================ */
.table {
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0;
}

.table th, .table td {
  padding: 1.1rem 1.4rem;
  border: none;
  vertical-align: middle;
}

.table th {
  background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
  font-weight: 700;
  color: #475569;
  width: 35%;
  border-bottom: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
}

.table td {
  background: white;
  color: #334155;
  border-bottom: 1px solid #e2e8f0;
}

.table tr:last-child th,
.table tr:last-child td {
  border-bottom: none;
}

.table tr:hover td {
  background: rgba(243, 244, 246, 0.5);
  transition: background 0.25s ease;
}

/* ===============================
   ANIMASI FADE-IN HALUS
================================ */
.profile-card,
.profile-card .table {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.profile-card .table {
  animation-delay: 0.2s;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ===============================
   RESPONSIVE
================================ */
@media (max-width: 576px) {
  h2 { font-size: 1.6rem; }
  h4 { font-size: 1.15rem; }

  .container { padding: 1.5rem 1rem; }

  .table th, .table td {
    display: block;
    width: 100%;
    border: none;
    padding: 1rem 1.2rem;
  }

  .table th {
    background: #f1f5f9;
    border-bottom: none;
    font-weight: 600;
    color: #64748b;
  }

  .table td {
    border-bottom: 1px solid #e2e8f0;
  }

  .table tr:last-child td {
    border-bottom: none;
  }

  .btn-back {
    width: 100%;
    margin-bottom: 1.5rem;
  }
}
</style>

<div class="container">
    <div class="profile-card p-4 p-md-5">

        <!-- Tombol Kembali -->
        <div class="mb-4">
            <a href="{{ route('unit.index') }}" class="btn btn-back d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Unit
            </a>
        </div>

        <h2>biMBA-AIUEO {{ $unit->biMBA_unit }} | Profil Unit</h2>

        <table class="table">
            <tr><th>No Cabang</th><td class="fw-semibold">{{ $unit->no_cabang }}</td></tr>
            <tr><th>biMBA Unit</th><td class="fw-semibold text-primary">{{ $unit->biMBA_unit }}</td></tr>
            <tr><th>Staff SOS</th><td>{{ $unit->staff_sos ?? '-' }}</td></tr>
            <tr><th>Telp/HP</th><td>{{ $unit->telp ?? '-' }}</td></tr>
            <tr><th>Email</th><td>
                @if($unit->email)
                    <a href="mailto:{{ $unit->email }}" class="text-primary">{{ $unit->email }}</a>
                @else
                    -
                @endif
            </td></tr>
        </table>

        <h4>Bank / Rekening</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Bank</th>
                    <th>Nomor Rekening</th>
                    <th>Atas Nama</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $unit->bank_nama ?? '-' }}</td>
                    <td>{{ $unit->bank_nomor ?? '-' }}</td>
                    <td>{{ $unit->bank_atas_nama ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <h4>Alamat Lengkap</h4>
        <table class="table">
            <tr><th>Alamat</th><td>{{ $unit->alamat_jalan ?? '-' }}</td></tr>
            <tr><th>RT/RW</th><td>{{ $unit->alamat_rt_rw ?? '-' }}</td></tr>
            <tr><th>Kode Pos</th><td>{{ $unit->alamat_kode_pos ?? '-' }}</td></tr>
            <tr><th>Kel/Desa</th><td>{{ $unit->alamat_kel_des ?? '-' }}</td></tr>
            <tr><th>Kecamatan</th><td>{{ $unit->alamat_kecamatan ?? '-' }}</td></tr>
            <tr><th>Kota/Kab</th><td>{{ $unit->alamat_kota_kab ?? '-' }}</td></tr>
            <tr><th>Provinsi</th><td>{{ $unit->alamat_provinsi ?? '-' }}</td></tr>
        </table>
    </div>
</div>

@endsection