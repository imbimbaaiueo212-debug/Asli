@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Semua History Buku Induk</h3>
    <a href="{{ route('buku_induk.index') }}" class="btn btn-secondary mb-3">← Kembali</a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Aksi</th>
                    <th>User</th>
                    <th>Buku Induk</th>
                    <th>Perubahan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                    <tr>
                        <td>
                            {{ \Carbon\Carbon::parse($history->created_at)
                                ->timezone('Asia/Jakarta')
                                ->format('d-m-Y H:i') }}
                        </td>
                        <td>
                            @switch(strtolower($history->action))
                                @case('import')
                                @case('import_create')
                                    <span class="badge bg-success">Import</span>
                                    @break
                                @case('update')
                                @case('update_import')
                                @case('update_partial')
                                    <span class="badge bg-warning">Update</span>
                                    @break
                                @case('create')
                                    <span class="badge bg-primary">Create</span>
                                    @break
                                @case('delete')
                                    <span class="badge bg-danger">Delete</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $history->action }}</span>
                            @endswitch
                        </td>
                        <td>{{ $history->user ?? 'system' }}</td>
                        <td>
                            @if($history->bukuInduk)
                                {{ $history->bukuInduk->nama ?? '-' }}
                                <small class="text-muted">({{ $history->bukuInduk->nim ?? '-' }})</small>
                            @else
                                <span class="text-danger">Data murid sudah dihapus</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $old = $history->old_data ?? [];
                                $new = $history->new_data ?? [];
                            @endphp

                            @if (is_array($old) && is_array($new) && !empty($old))
                                <ul class="list-unstyled mb-0">
                                    @foreach($old as $key => $oldValue)
                                        @php
                                            $newValue = $new[$key] ?? null;
                                        @endphp
                                        @if ($oldValue != $newValue && $oldValue !== null)
                                            <li class="mb-1">
                                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong>:
                                                <span class="text-danger">{{ $oldValue }}</span>
                                                →
                                                <span class="text-success">{{ $newValue }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @elseif (is_string($old) || is_string($new))
                                <div class="alert alert-warning small mb-0 py-2">
                                    <strong>Format history lama:</strong><br>
                                    <pre class="mb-0" style="font-size:0.85rem; white-space: pre-wrap;">{{ substr($old ?: $new, 0, 300) }} {{ strlen($old ?: $new) > 300 ? '...' : '' }}</pre>
                                </div>
                            @elseif ($history->action === 'create' || $history->action === 'import_create')
                                <small class="text-primary">Data baru dibuat</small>
                                @if (is_array($history->data))
                                    <pre class="small mt-1">{{ json_encode($history->data, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            @elseif ($history->action === 'delete')
                                <small class="text-danger">Data dihapus</small>
                            @else
                                <small class="text-muted">Tidak ada detail perubahan yang tersedia</small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada history yang tercatat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $histories->links() }}
    </div>
</div>
@endsection