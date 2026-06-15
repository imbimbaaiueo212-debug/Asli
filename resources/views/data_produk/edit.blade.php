@extends('layouts.app')

@section('title', 'Edit Rekap Stok Produk')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-11">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-gradient-warning text-dark py-3">
                    <h4 class="mb-0 text-center">
                        <i class="fas fa-edit me-2"></i>
                        Edit Data Rekap Stok Produk
                    </h4>
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('data_produk.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Unit biMBA -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Unit biMBA <span class="text-danger">*</span></label>
                                <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Unit biMBA --</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ old('unit_id', $item->unit_id) == $unit->id ? 'selected' : '' }}>
                                            {{ $unit->no_cabang }} | {{ strtoupper($unit->biMBA_unit) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('unit_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Periode -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Periode Rekap <span class="text-danger">*</span></label>
                                <input type="month"
                                       name="periode"
                                       class="form-control @error('periode') is-invalid @enderror"
                                       value="{{ old('periode', $item->periode) }}"
                                       required>
                                @error('periode')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle text-center">
                                <thead class="table-warning">
                                    <tr>
                                        <th width="15%">PRODUK</th>
                                        <th width="8%">SATUAN</th>
                                        <th width="10%">HARGA (Rp)</th>
                                        <th width="8%">MIN STOK</th>
                                        <th width="9%">SLD AWAL</th>
                                        <th width="9%">TERIMA</th>
                                        <th width="9%">PAKAI</th>
                                        <th width="9%">SLD AKHIR</th>
                                        <th width="8%">STATUS</th>
                                        <th width="9%">OPNAME</th>
                                        <th width="9%">SELISIH</th>
                                        <th width="12%">NILAI STOK FISIK (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <!-- Produk (Dropdown) -->
                                        <td>
                                            <select name="kode"
                                                    class="form-select @error('kode') is-invalid @enderror"
                                                    required>
                                                <option value="">-- Pilih Produk --</option>
                                                @foreach($produks as $p)
                                                    <option value="{{ $p->kode }}"
                                                            data-jenis="{{ $p->jenis }}"
                                                            data-label="{{ $p->label }}"
                                                            data-satuan="{{ $p->satuan }}"
                                                            data-harga="{{ $p->harga }}"
                                                            {{ old('kode', $item->kode) == $p->kode ? 'selected' : '' }}>
                                                        {{ $p->kode }} - {{ $p->label }} ({{ $p->jenis }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('kode')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror

                                            <input type="hidden" name="jenis" id="jenis" value="{{ old('jenis', $item->jenis) }}">
                                            <input type="hidden" name="label" id="label" value="{{ old('label', $item->label) }}">
                                        </td>

                                        <!-- Satuan -->
                                        <td>
                                            <input type="text"
                                                   id="satuan"
                                                   class="form-control bg-light text-center"
                                                   value="{{ old('satuan', $item->satuan) }}"
                                                   readonly>
                                            <input type="hidden" name="satuan" id="satuan_hidden" value="{{ old('satuan', $item->satuan) }}">
                                        </td>

                                        <!-- Harga -->
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number"
                                                       id="harga"
                                                       name="harga"
                                                       class="form-control text-end bg-light"
                                                       value="{{ old('harga', $item->harga) }}"
                                                       readonly>
                                            </div>
                                        </td>

                                        <!-- Min Stok -->
                                        <td>
                                            <input type="number"
                                                   name="min_stok"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('min_stok', $item->min_stok) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Saldo Awal -->
                                        <td>
                                            <input type="number"
                                                   name="sld_awal"
                                                   class="form-control text-end calc-input"
                                                   value="{{ old('sld_awal', $item->sld_awal) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Terima -->
                                        <td>
                                            <input type="number"
                                                   name="terima"
                                                   class="form-control text-end calc-input text-success"
                                                   value="{{ old('terima', $item->terima) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Pakai -->
                                        <td>
                                            <input type="number"
                                                   name="pakai"
                                                   class="form-control text-end calc-input text-danger"
                                                   value="{{ old('pakai', $item->pakai) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Saldo Akhir -->
                                        <td>
                                            <input type="number"
                                                   id="sld_akhir"
                                                   class="form-control text-end fw-bold bg-light"
                                                   readonly>
                                        </td>

                                        <!-- Status Stok -->
                                        <td>
                                            <div id="status" class="fw-bold fs-4 text-center">
                                                -
                                            </div>
                                        </td>

                                        <!-- Opname Fisik -->
                                        <td>
                                            <input type="number"
                                                   name="opname"
                                                   class="form-control text-end calc-input fw-bold"
                                                   value="{{ old('opname', $item->opname) }}"
                                                   min="0"
                                                   required>
                                        </td>

                                        <!-- Selisih -->
                                        <td>
                                            <input type="number"
                                                   id="selisih"
                                                   name="selisih"
                                                   class="form-control text-end fw-bold bg-light"
                                                   readonly
                                                   >
                                        </td>

                                        <!-- Nilai Stok Fisik (Opname × Harga) -->
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text"
                                                       id="nilai"
                                                       class="form-control text-end fw-bold bg-light"
                                                       readonly
                                                       value="0">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5 text-end">
                            <a href="{{ route('data_produk.index') }}" class="btn btn-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-5 ms-3">
                                <i class="fas fa-save me-2"></i> Update Rekap Stok
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectProduk = document.querySelector('select[name="kode"]');
    const opnameInput = document.querySelector('input[name="opname"]');
    
    const inputs = {
        jenis: document.getElementById('jenis'),
        label: document.getElementById('label'),
        satuan: document.getElementById('satuan'),
        satuanHidden: document.getElementById('satuan_hidden'),
        harga: document.getElementById('harga'),
        sldAwal: document.querySelector('input[name="sld_awal"]'),
        terima: document.querySelector('input[name="terima"]'),
        pakai: document.querySelector('input[name="pakai"]'),
        opname: opnameInput,
        minStok: document.querySelector('input[name="min_stok"]'),
        sldAkhir: document.getElementById('sld_akhir'),
        status: document.getElementById('status'),
        selisih: document.getElementById('selisih'),
        nilai: document.getElementById('nilai')
    };

    let isOpnameTouched = false;  // Flag kunci: apakah user sudah menyentuh kolom opname

    function formatRupiah(angka) {
        if (angka === 0) return "0";
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function hitungSemua() {
        const sldAwal  = parseInt(inputs.sldAwal.value)  || 0;
        const terima   = parseInt(inputs.terima.value)   || 0;
        const pakai    = parseInt(inputs.pakai.value)    || 0;
        const opname   = parseInt(inputs.opname.value)   || 0;
        const harga    = parseFloat(inputs.harga.value)  || 0;
        const minStok  = parseInt(inputs.minStok.value)  || 0;

        const sldAkhir    = sldAwal + terima - pakai;
        const selisih     = opname - sldAkhir;
        const nilaiRupiah = opname * harga;

        // Selalu update ini (stok buku)
        inputs.sldAkhir.value = sldAkhir;

        // Status stok buku
        if (sldAkhir >= minStok) {
            inputs.status.innerHTML = '<span class="text-success">STOK AMAN</span>';
        } else if (sldAkhir > 0) {
            inputs.status.innerHTML = '<span class="text-warning">STOK MENIPIS</span>';
        } else {
            inputs.status.innerHTML = '<span class="text-danger">STOK HABIS</span>';
        }

        // Hanya hitung & tampilkan selisih + nilai JIKA user sudah menyentuh opname
        if (isOpnameTouched) {
            inputs.selisih.value = selisih;
            inputs.nilai.value   = formatRupiah(nilaiRupiah);

            // Warna selisih
            inputs.selisih.className = 'form-control text-end fw-bold bg-light ' + 
                (selisih < 0 ? 'text-danger' : (selisih > 0 ? 'text-success' : 'text-secondary'));
        } else {
            inputs.selisih.value = '';
            inputs.nilai.value   = '0';
            inputs.selisih.className = 'form-control text-end fw-bold bg-light text-secondary';
        }
    }

    // Saat ganti produk
    selectProduk.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            inputs.jenis.value       = option.dataset.jenis;
            inputs.label.value       = option.dataset.label;
            inputs.satuan.value      = option.dataset.satuan;
            inputs.satuanHidden.value = option.dataset.satuan;
            inputs.harga.value       = option.dataset.harga;
        } else {
            inputs.jenis.value       = '';
            inputs.label.value       = '';
            inputs.satuan.value      = '';
            inputs.satuanHidden.value = '';
            inputs.harga.value       = 0;
        }
        hitungSemua();
    });

    // Deteksi perubahan di semua input kalkulasi
    document.querySelectorAll('.calc-input').forEach(el => {
        el.addEventListener('input', function () {
            // Khusus opname: tandai bahwa sudah disentuh
            if (el === opnameInput) {
                isOpnameTouched = true;
            }
            hitungSemua();
        });
    });

    // Inisialisasi awal (selisih kosong dulu)
    hitungSemua();

    // Jika produk sudah dipilih (mode edit)
    if (selectProduk.value) {
        selectProduk.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection