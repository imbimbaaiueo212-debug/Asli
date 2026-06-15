@extends('layouts.app')

@section('content')
<div class="card p-4">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Order Modul</h3>

        <a href="{{ route('order_modul.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Data
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="row g-2 mb-4">

    <div class="col-md-4">
        <label class="form-label">Dari Tanggal</label>
        <input type="date" name="start_date" class="form-control"
               value="{{ request('start_date') }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">Sampai Tanggal</label>
        <input type="date" name="end_date" class="form-control"
               value="{{ request('end_date') }}">
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary w-100">
            Filter
        </button>
        <a href="{{ route('order_modul.index') }}" class="btn btn-secondary w-100">
    Reset
</a>
    </div>

</form>

    {{-- TABLE --}}
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Unit</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Stok</th>
                    <th>Approval</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $i => $order)

                    @php
                        $rekap = \App\Models\DataProduk::where('unit_id', $order->unit_id)
                            ->where('label', $order->kode1)
                            ->first();

                        $status = null;
                        if ($rekap) {
                            $sld = $rekap->sld_awal + $rekap->terima - $rekap->pakai;
                            $status = $sld >= $rekap->min_stok ? 1 : 0;
                        }
                    @endphp

                    <tr>
                        <td>{{ $i+1 }}</td>

                        <td>{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d-m-Y') }}</td>

                        <td>
                            {{ $order->unit->no_cabang ?? '-' }} <br>
                            <small>{{ strtoupper($order->unit->biMBA_unit ?? '') }}</small>
                        </td>

                        <td class="text-start">{{ $order->kode1 }}</td>

                        <td>{{ $order->jml1 }}</td>

                        

                        <td>
                            @if($status === 1)
                                <span class="badge bg-success">Aman</span>
                            @elseif($status === 0)
                                <span class="badge bg-danger">Kurang</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($order->status == 'accept')
                                <span class="badge bg-success">Accepted</span>
                            @elseif($order->status == 'reject')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('order_modul.edit', $order->id) }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ route('order_modul.destroy', $order->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Hapus data?')" class="btn btn-sm btn-danger">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="8">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection