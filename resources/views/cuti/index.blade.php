@extends('layouts.app')

@section('content')
<div class="container">

    <div class="card shadow-sm">
        
        <div class="card-header">
            <h5 class="mb-0">Data Cuti Murid</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Unit</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Jenis Cuti</th>
                            <th>Status</th>
                            <th>Surat</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($cuti as $item)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $item->bukuInduk->nama ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->bukuInduk->bimba_unit ?? '-' }}
                                </td>

                                <td>
                                    {{ optional($item->tanggal_mulai)->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ optional($item->tanggal_selesai)->format('d-m-Y') }}
                                </td>

                                <td>
                                    {{ $item->jenis_cuti ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-warning">
                                        {{ $item->status }}
                                    </span>
                                </td>

                                <td>
                                    @if($item->surat_dokter)
                                        <a href="{{ asset('storage/' . $item->surat_dokter) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-primary">
                                            Lihat
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center">
                                    Tidak ada data cuti
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="mt-3">
                {{ $cuti->links() }}
            </div>

        </div>
    </div>
</div>
@endsection