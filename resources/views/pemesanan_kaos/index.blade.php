@extends('layouts.app')

@section('title', 'Pemesanan Atribut')

@section('content')
<div class="card card-body">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Pemesanan Atribut</h1>
                <a href="{{ route('pemesanan_kaos.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Data
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Form Filter dengan Auto Submit -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Data (Otomatis)</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET" action="{{ route('pemesanan_kaos.index') }}">
                        <div class="row g-3 align-items-end">
                            {{-- Filter Unit – HANYA UNTUK ADMIN --}}
                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <div class="col-md-3">
                                    <label class="form-label">Unit biMBA</label>
                                    <select name="unit_id" class="form-select">
                                        <option value="">- Semua Unit -</option>
                                        @foreach($filterUnits as $unit)
                                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                                {{ $unit->label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="col-md-4">
                                <label class="form-label">Nama Murid</label>
                                <select name="nama_murid" class="form-select select2-filter" id="nama_murid_select">
                                    <option value="">- Semua Murid -</option>
                                    @foreach($distinctMurid as $murid)
                                        <option value="{{ $murid }}" {{ request('nama_murid') == $murid ? 'selected' : '' }}>
                                            {{ $murid }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tanggal Dari</label>
                                <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Tanggal Sampai</label>
                                <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                            </div>

                            <div class="col-md-1">
                                <a href="{{ route('pemesanan_kaos.index') }}" class="btn btn-secondary w-100" title="Reset Filter">
                                    <i class="fas fa-sync-alt"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Daftar Pesanan -->
            <h4 class="mt-4 mb-3">Daftar Pesanan</h4>
            <div class="table-responsive shadow-sm rounded">
                <table class="table table-bordered table-hover table-sm text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="4%">NO</th>

                            {{-- KOLOM UNIT – HANYA UNTUK ADMIN --}}
                            @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                <th>UNIT</th>
                            @endif

                            <th>NO BUKTI</th>
                            <th>TANGGAL</th>
                            <th>NIM</th>
                            <th>NAMA MURID</th>
                            <th>GOL</th>
                            <th>TGL MASUK</th>
                            <th>LAMA BELAJAR</th>
                            <th>GURU</th>
                            <th>PENDEK</th>
                            <th>PANJANG</th>
                            <th>UKURAN</th>
                            <th>KPK</th>
                            <th>TAS</th>
                            <th>RBAS</th>
                            <th>BCABS01</th>
                            <th>BCABS02</th>
                            <th>KETERANGAN</th>
                            <th width="8%">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                {{-- Hanya admin yang melihat kolom ini --}}
                                @if (auth()->check() && (auth()->user()->is_admin ?? false))
                                    <td class="text-start"><strong>{{ $order->unit?->label ?? '-' }}</strong></td>
                                @endif

                                <td>{{ $order->no_bukti ?? '-' }}</td>
                                <td>{{ $order->tanggal ? \Illuminate\Support\Carbon::parse($order->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $order->nim ?? '-' }}</td>
                                <td class="text-start">{{ $order->nama_murid }}</td>
                                <td><strong>{{ $order->gol ?? '-' }}</strong></td>
                                <td>{{ $order->tgl_masuk ? \Carbon\Carbon::parse($order->tgl_masuk)->format('d/m/Y') : '-' }}</td>
                                <td>{{ $order->lama_bljr ?? '-' }}</td>
                                <td class="text-start">{{ $order->guru ?? '-' }}</td>
                                <td>{{ $order->kaos ?? 0 }}</td>
                                <td>{{ $order->kaos_panjang ?? 0 }}</td>
                                <td><strong>{{ strtoupper($order->size ?? '-') }}</strong></td>
                                <td>{{ $order->kpk ?? 0 }}</td>
                                <td>
                                    @if($order->kode_tas)
                                        @php
                                            $tasProduk = \App\Models\Produk::where('kode', $order->kode_tas)->first();
                                        @endphp
                                        <strong>{{ $tasProduk?->label ?? $order->kode_tas }}</strong>
                                        ({{ $order->jumlah_tas ?? 1 }} pcs)
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $order->rbas ?? 0 }}</td>
                                <td>{{ $order->bcabs01 ?? 0 }}</td>
                                <td>{{ $order->bcabs02 ?? 0 }}</td>
                                <td class="text-start">{{ Str::limit($order->keterangan ?? '-', 30) }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('pemesanan_kaos.edit', $order->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if (auth()->user()?->role === 'admin')
                                            <form action="{{ route('pemesanan_kaos.destroy', $order->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" 
                                                        onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->check() && (auth()->user()->is_admin ?? false) ? '20' : '19' }}" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    Belum ada data pemesanan yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Rekap Keseluruhan -->
            <h4 class="mt-5 mb-3">Rekap Pemesanan Keseluruhan</h4>
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th width="15%">KODE</th>
                                    <th>NAMA BARANG</th>
                                    <th width="20%">JUMLAH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapKeseluruhan as $item)
                                    <tr>
                                        <td><strong>{{ $item['kode'] }}</strong></td>
                                        <td>{{ $item['nama_barang'] }}</td>
                                        <td><strong>{{ $item['jumlah'] }} pcs</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rekap Per Unit – HANYA UNTUK ADMIN -->
            @if (auth()->check() && (auth()->user()->is_admin ?? false) && !empty($rekapPerUnit))
                <h4 class="mt-5 mb-3">Rekap Pemesanan Per Unit biMBA</h4>
                <div class="accordion" id="rekapPerUnitAccordion">
                    @foreach($rekapPerUnit as $index => $rekap)
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }} fw-bold" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseUnit{{ $index }}">
                                    {{ $rekap['unit_label'] }}
                                </button>
                            </h2>
                            <div id="collapseUnit{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                 data-bs-parent="#rekapPerUnitAccordion">
                                <div class="accordion-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0 text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="25%">KODE</th>
                                                    <th>NAMA BARANG</th>
                                                    <th width="25%">JUMLAH</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Kaos Lengan Pendek -->
                                                <tr class="table-active">
                                                    <td colspan="3" class="fw-bold text-start ps-3">KAOS LENGAN PENDEK</td>
                                                </tr>
                                                <tr><td>KAS</td>     <td>Kaos Anak S (Pendek)</td>      <td><strong>{{ $rekap['kaos_pendek']['KAS'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAM</td>     <td>Kaos Anak M (Pendek)</td>      <td><strong>{{ $rekap['kaos_pendek']['KAM'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAL</td>     <td>Kaos Anak L (Pendek)</td>      <td><strong>{{ $rekap['kaos_pendek']['KAL'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXL</td>    <td>Kaos Anak XL (Pendek)</td>     <td><strong>{{ $rekap['kaos_pendek']['KAXL'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXL</td>   <td>Kaos Anak XXL (Pendek)</td>    <td><strong>{{ $rekap['kaos_pendek']['KAXXL'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXXL</td>  <td>Kaos Anak XXXL (Pendek)</td>   <td><strong>{{ $rekap['kaos_pendek']['KAXXXL'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXXLS</td> <td>Kaos Anak XXXLS (Pendek)</td>  <td><strong>{{ $rekap['kaos_pendek']['KAXXXLS'] ?? 0 }} pcs</strong></td></tr>

                                                <!-- Kaos Lengan Panjang -->
                                                <tr class="table-active">
                                                    <td colspan="3" class="fw-bold text-start ps-3">KAOS LENGAN PANJANG</td>
                                                </tr>
                                                <tr><td>KAS01</td>    <td>Kaos Anak S (Panjang)</td>     <td><strong>{{ $rekap['kaos_panjang']['KAS01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAM01</td>    <td>Kaos Anak M (Panjang)</td>     <td><strong>{{ $rekap['kaos_panjang']['KAM01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAL01</td>    <td>Kaos Anak L (Panjang)</td>     <td><strong>{{ $rekap['kaos_panjang']['KAL01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXL01</td>   <td>Kaos Anak XL (Panjang)</td>    <td><strong>{{ $rekap['kaos_panjang']['KAXL01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXL01</td>  <td>Kaos Anak XXL (Panjang)</td>   <td><strong>{{ $rekap['kaos_panjang']['KAXXL01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXXL01</td> <td>Kaos Anak XXXL (Panjang)</td>  <td><strong>{{ $rekap['kaos_panjang']['KAXXXL01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KAXXXLS01</td><td>Kaos Anak XXXLS (Panjang)</td> <td><strong>{{ $rekap['kaos_panjang']['KAXXXLS01'] ?? 0 }} pcs</strong></td></tr>

                                                <!-- Item Lainnya -->
                                                <tr class="table-active">
                                                    <td colspan="3" class="fw-bold text-start ps-3">ITEM TAMBAHAN</td>
                                                </tr>
                                                <tr><td>RBAS</td>     <td>RBAS</td>                     <td><strong>{{ $rekap['lainnya']['rbas'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>BCABS01</td>  <td>BCABS01</td>                  <td><strong>{{ $rekap['lainnya']['bcabs01'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>BCABS02</td>  <td>BCABS02</td>                  <td><strong>{{ $rekap['lainnya']['bcabs02'] ?? 0 }} pcs</strong></td></tr>
                                                <tr><td>KPK</td>      <td>KPK</td>                      <td><strong>{{ $rekap['lainnya']['kpk'] ?? 0 }} pcs</strong></td></tr>

                                                <!-- Tas (jika ada) -->
                                                @if($rekap['lainnya']['tas'] ?? 0 > 0)
                                                    <tr>
                                                        <td>{{ $tasLabel ?? 'TAS' }}</td>
                                                        <td>Tas biMBA</td>
                                                        <td><strong>{{ $rekap['lainnya']['tas'] }} pcs</strong></td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi Select2
    $('#nama_murid_select').select2({
        placeholder: '- Semua Murid -',
        allowClear: true,
        width: '100%',
    });

    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('select, input[type="date"]');

    // Auto submit saat ada perubahan
    inputs.forEach(input => {
        input.addEventListener('change', () => form.submit());
    });

    // Select2 change event
    $('#nama_murid_select').on('select2:select select2:clear', () => form.submit());
});
</script>
@endpush
@endsection