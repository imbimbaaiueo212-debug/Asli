@extends('layouts.app')

@section('title', 'Daftar Pemakaian Produk')

@section('content')
<!-- Alpine.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="container-fluid py-4" x-data="pemakaianFilter()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Daftar Pemakaian Produk</h1>
        <a href="{{ route('pemakaian_produk.create') }}" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus me-2"></i> Tambah Data
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Card Filter dengan Dropdown dari Data Tabel -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-gradient-info text-white py-3">
            <h6 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2"></i> Filter Data Pemakaian Produk (Otomatis)
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <!-- Tanggal Dari -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tanggal Dari</label>
                    <input type="date" x-model="tanggalDari" class="form-control" @change="delayedFilter()">
                </div>

                <!-- Tanggal Sampai -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Tanggal Sampai</label>
                    <input type="date" x-model="tanggalSampai" class="form-control" @change="delayedFilter()">
                </div>

                <!-- Nama Murid (Dropdown dari data) -->
                <div class="col-md-3">
                    <label class="form-label small text-muted">Nama Murid</label>
                    <select x-model="namaMurid" class="form-select" @change="filterData()">
                        <option value="">-- Semua Murid --</option>
                        <template x-for="murid in uniqueMurid" :key="murid">
                            <option :value="murid" x-text="murid"></option>
                        </template>
                    </select>
                </div>

                <!-- Label Produk (Dropdown dari data) -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Label Produk</label>
                    <select x-model="label" class="form-select" @change="filterData()">
                        <option value="">-- Semua Label --</option>
                        <template x-for="lbl in uniqueLabel" :key="lbl">
                            <option :value="lbl" x-text="lbl"></option>
                        </template>
                    </select>
                </div>

                <!-- Guru (Dropdown dari data) -->
                <div class="col-md-2">
                    <label class="form-label small text-muted">Nama Guru</label>
                    <select x-model="guru" class="form-select" @change="filterData()">
                        <option value="">-- Semua Guru --</option>
                        <template x-for="g in uniqueGuru" :key="g">
                            <option :value="g" x-text="g || 'Tidak Ada Guru'"></option>
                        </template>
                    </select>
                </div>

                <!-- Unit biMBA -->
                <div class="col-md-3">
                    <label class="form-label small text-muted">Unit biMBA</label>
                    <select x-model="unitId" class="form-select" @change="delayedFilter()">
                        <option value="">-- Semua Unit --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol -->
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="button" @click="filterData()" class="btn btn-info text-white flex-fill">
                        <i class="fas fa-sync me-1"></i> Refresh
                    </button>
                    <button type="button" @click="resetFilter()" class="btn btn-secondary flex-fill">
                        <i class="fas fa-times me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover mb-0 align-middle text-center" style="min-width: 1700px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">NO</th>
                            <th>TANGGAL</th>
                            <th>MINGGU</th>
                            <th>LABEL</th>
                            <th>JUMLAH</th>
                            <th>NIM</th>
                            <th>KATEGORI</th>
                            <th>JENIS</th>
                            <th>NAMA PRODUK</th>
                            <th>SATUAN</th>
                            <th>HARGA (Rp)</th>
                            <th>TOTAL (Rp)</th>
                            <th>NAMA MURID</th>
                            <th>GOL</th>
                            <th>GURU</th>
                            <th style="width: 130px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in filteredItems" :key="item.id">
                            <tr>
                                <td x-text="index + 1"></td>
                                <td x-text="formatDate(item.tanggal)"></td>
                                <td x-text="item.minggu"></td>
                                <td><span class="badge bg-primary" x-text="item.label"></span></td>
                                <td class="text-end fw-bold" x-text="numberFormat(item.jumlah)"></td>
                                <td x-text="item.nim"></td>
                                <td x-text="item.kategori"></td>
                                <td x-text="item.jenis"></td>
                                <td class="text-start" x-text="item.nama_produk"></td>
                                <td x-text="item.satuan"></td>
                                <td class="text-end" x-text="numberFormat(item.harga)"></td>
                                <td class="text-end fw-bold text-success" x-text="numberFormat(item.total)"></td>
                                <td class="text-start fw-bold" x-text="item.nama_murid"></td>
                                <td x-text="item.gol"></td>
                                <td x-text="item.guru || '-'"></td>
                                <td>
    <a :href="`/pemakaian_produk/${item.id}/edit`"
       class="btn btn-sm btn-warning">
        <i class="fas fa-edit"></i>
    </a>

    @if (auth()->user()?->role === 'admin')
        <form :action="`/pemakaian_produk/${item.id}`"
              method="POST"
              class="d-inline"
              @submit.prevent="if(confirm('Yakin hapus data ini?')) $el.submit()">

            @csrf
            @method('DELETE')

            <button type="submit" class="btn btn-sm btn-danger">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endif
</td>
                            </tr>
                        </template>

                        <tr x-show="filteredItems.length === 0">
                            <td colspan="16" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                Tidak ada data yang sesuai dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-light py-3" x-show="allItems.length > 0">
                <div class="text-muted text-center">
                    Menampilkan <strong x-text="filteredItems.length"></strong> dari <strong x-text="allItems.length"></strong> data
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function pemakaianFilter() {
    return {
        allItems: @json($items->items()),

        tanggalDari: '{{ request('tanggal_dari') ?? '' }}',
        tanggalSampai: '{{ request('tanggal_sampai') ?? '' }}',
        namaMurid: '{{ request('nama_murid') ?? '' }}',
        label: '{{ request('label') ?? '' }}',
        guru: '{{ request('guru') ?? '' }}',
        unitId: '{{ request('unit_id') ?? '' }}',

        filteredItems: [],
        uniqueMurid: [],
        uniqueLabel: [],
        uniqueGuru: [],

        init() {
            // Ambil data unik dari allItems
            this.uniqueMurid = [...new Set(this.allItems.map(item => item.nama_murid))].sort();
            this.uniqueLabel = [...new Set(this.allItems.map(item => item.label))].sort();
            this.uniqueGuru = [...new Set(this.allItems.map(item => item.guru).filter(g => g))].sort();

            this.filterData();
        },

        delayedFilter() {
            setTimeout(() => this.filterData(), 300);
        },

        filterData() {
            let data = this.allItems;

            if (this.tanggalDari) {
                data = data.filter(item => item.tanggal >= this.tanggalDari);
            }
            if (this.tanggalSampai) {
                data = data.filter(item => item.tanggal <= this.tanggalSampai);
            }
            if (this.namaMurid) {
                data = data.filter(item => item.nama_murid === this.namaMurid);
            }
            if (this.label) {
                data = data.filter(item => item.label === this.label);
            }
            if (this.guru) {
                data = data.filter(item => item.guru === this.guru);
            }
            if (this.unitId) {
                data = data.filter(item => item.unit_id == this.unitId);
            }

            this.filteredItems = data;
        },

        resetFilter() {
            this.tanggalDari = '';
            this.tanggalSampai = '';
            this.namaMurid = '';
            this.label = '';
            this.guru = '';
            this.unitId = '';
            this.filterData();
        },

        formatDate(date) {
            if (!date) return '-';
            const d = new Date(date);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
        },

        numberFormat(num) {
            if (!num) return '0';
            return parseInt(num).toLocaleString('id-ID');
        }
    }
}
</script>
@endsection