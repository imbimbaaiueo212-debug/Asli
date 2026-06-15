@extends('layouts.app')

@section('content')
<div class="section-body">
    <h2 class="section-title">Pembayaran Komisi</h2>

    {{-- FILTER BULAN & TAHUN --}}
    <form class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <select name="bulan" class="form-control" onchange="this.form.submit()">
                    @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == $bulan ? 'selected' : '' }}>
                            {{ $namaBulan[$i - 1] }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="tahun" value="{{ $tahun }}" class="form-control"
                    onchange="this.form.submit()">
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead class="bg-primary text-dark">
                        <tr>
                            <th rowspan="2">NO</th>
                            <th rowspan="2">NAMA</th>
                            <th rowspan="2">JABATAN</th>
                            <th rowspan="2">STATUS</th>
                            <th rowspan="2">DEPARTEMEN</th>
                            <th rowspan="2">UNIT</th>           {{-- ← baris baru --}}
                            <th rowspan="2">NO CABANG</th>      {{-- ← baris baru --}}
                            <th rowspan="2">MASA KERJA</th>
                            <th rowspan="2">NO REKENING</th>
                            <th rowspan="2">BANK</th>
                            <th rowspan="2">ATAS NAMA</th>
                            <th colspan="5" class="text-center">biMBA AIUEO</th>
                            <th colspan="4" class="text-center">ENGLISH biMBA</th>
                            <th colspan="1" class="text-center">TRANSFER</th>
                        </tr>
                        <tr>
                            <th>THP</th>
                            <th>INSENTIF</th>
                            <th>KURANG</th>
                            <th>LEBIH</th>
                            <th>BULAN</th>
                            <th>THP</th>
                            <th>KURANG</th>
                            <th>LEBIH</th>
                            <th>BULAN</th>
                            <th>biMBA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $k)
                            @php
                                // THP ASLI (hanya komisi MB + MT)
                                $thpBimba   = ($k->komisi_mb_bimba ?? 0) + ($k->komisi_mt_bimba ?? 0);
                                $thpEnglish = ($k->komisi_mb_english ?? 0) + ($k->komisi_mt_english ?? 0);

                                // TOTAL TRANSFER YANG BENAR (TIDAK DOBEL KU!)
                                $transfer = $thpBimba
                                          + ($k->komisi_mb_english ?? 0)
                                          + ($k->komisi_mt_english ?? 0)
                                          + ($k->mb_insentif_ku ?? 0)           // Insentif KU hanya sekali
                                          + ($k->insentif_bimba ?? 0)           // Insentif dari pembayaran
                                          + ($k->lebih_bimba ?? 0)
                                          - ($k->kurang_bimba ?? 0);

                                // Fallback unit / no_cabang: ambil dari komisi, jika null pakai relasi unit
                                $unit_name   = $k->bimba_unit ?? ($k->unit?->biMBA_unit ?? '-');
                                $no_cabang   = $k->no_cabang ?? ($k->unit?->no_cabang ?? '-');
                            @endphp
                            <tr data-id="{{ $k->id }}">
                                <td>{{ $i + 1 }}</td>
                                <td><strong>{{ $k->profile->nama ?? $k->nama }}</strong></td>
                                <td>{{ $k->jabatan }}</td>
                                <td>{{ $k->status }}</td>
                                <td>{{ $k->departemen }}</td>

                                {{-- UNIT & NO CABANG --}}
                                <td>{{ $unit_name }}</td>
                                <td>{{ $no_cabang }}</td>

                                <td>{{ $k->profile?->masa_kerja_format ?? $k->masa_kerja . ' bulan' }}</td>
                                <td>{{ $k->profile->no_rekening ?? '-' }}</td>
                                <td>{{ $k->profile->bank ?? '-' }}</td>
                                <td>{{ $k->profile->atas_nama_rekening ?? $k->profile->nama ?? '-' }}</td>

                                <!-- THP biMBA (tidak bisa diedit) -->
                                <td class="text-end bg-light">
                                    Rp{{ number_format($thpBimba, 0, ',', '.') }}
                                </td>

                                <!-- INI YANG BISA DIEDIT & DISIMPAN -->
                                <td>
                                    <input type="text" class="form-control form-control-sm text-end editable rupiah"
                                           data-field="insentif"
                                           value="{{ number_format($k->insentif_bimba ?? 0, 0, ',', '.') }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-end editable rupiah text-danger"
                                           data-field="kurang"
                                           value="{{ number_format($k->kurang_bimba ?? 0, 0, ',', '.') }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-end editable rupiah text-success"
                                           data-field="lebih"
                                           value="{{ number_format($k->lebih_bimba ?? 0, 0, ',', '.') }}">
                                </td>
                                <td>
                                    <select class="form-control form-control-sm editable" data-field="bulan">
                                        <option value="">-</option>
                                        @foreach($namaBulan as $bln)
                                            <option value="{{ $bln }}" {{ ($k->bulan_kurang_lebih ?? '') == $bln ? 'selected' : '' }}>
                                                {{ $bln }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- ENGLISH (jika ada) -->
                                <td class="text-end">{{ number_format($thpEnglish, 0, ',', '.') }}</td>
                                <td></td>
                                <td></td>
                                <td></td>

                                <!-- TOTAL TRANSFER (SEKARANG SELALU BENAR) -->
                                <td class="text-end font-weight-bold text-success transfer-amount">
                                    Rp{{ number_format($transfer, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="22" class="text-center py-4 text-muted">
                                    Belum ada data komisi untuk periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto format rupiah saat ketik
document.querySelectorAll('.rupiah').forEach(input => {
    input.addEventListener('input', function() {
        let val = this.value.replace(/\D/g, '');
        this.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    });
});

// Simpan otomatis saat ada perubahan
document.querySelectorAll('.editable').forEach(el => {
    el.addEventListener('change', function() {
        const row = this.closest('tr');
        const id  = row.dataset.id;

        const getRaw = (field) => {
            let el = row.querySelector(`[data-field="${field}"]`);
            if (!el) return 0;
            if (el.tagName === 'SELECT') return el.value;
            return parseInt(el.value.replace(/\D/g, '')) || 0;
        };

        const data = {
            _token: '{{ csrf_token() }}',
            komisi_id: id,
            thp: 0,
            insentif: getRaw('insentif'),
            kurang: getRaw('kurang'),
            lebih: getRaw('lebih'),
            bulan: getRaw('bulan')
        };

        fetch("{{ route('pembayaran-komisi.save') }}", {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                // Update tampilan transfer
                row.querySelector('.transfer-amount').innerHTML =
                    'Rp' + res.transfer.replace('Rp ', '');
            }
        })
        .catch(() => {
            alert('Gagal menyimpan! Cek koneksi atau console.');
        });
    });
});
</script>
@endpush
@endsection
