@extends('layouts.app')

@section('content')
<div class="app-container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Daftar Pengguna</h3>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Tambah User
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 60px;">#</th>
                            <th style="width: 80px;">Foto</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bimba Unit</th>
                            <th>No. Cabang</th>
                            <th>Dibuat</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $index => $user)
                            <tr>
                                <td class="text-center fw-bold">
                                    {{ $users->firstItem() + $index }}
                                </td>

                                {{-- Foto Profil (dengan fallback) --}}
                                <td class="text-center">
                                    @if ($user->photo)
                                        <img src="{{ asset('/public/storage/' . $user->photo) }}"
                                             alt="{{ $user->name }}"
                                             class="rounded-circle object-fit-cover"
                                             width="50" height="50">
                                    @else
                                        <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center text-white"
                                             style="width: 50px; height: 50px; font-size: 1.2rem;">
                                            {{ Str::substr($user->name, 0, 2) }}
                                        </div>
                                    @endif
                                </td>

                                <td class="fw-medium">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>

                                {{-- Role dengan badge warna berbeda --}}
                                <td class="text-center">
                                    @php
                                        $badges = [
                                            'admin'       => 'danger',
                                            'pusat'       => 'dark',
                                            'developer'   => 'info',
                                            'superadmin'  => 'warning text-dark',
                                            'owner'       => 'warning text-dark',
                                            'direktur'    => 'primary',
                                            'cabang'      => 'success',
                                            'guru'        => 'secondary',
                                        ];
                                        $color = $badges[strtolower($user->role)] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }} fs-6">
                                        {{ Str::upper($user->role ?? '-') }}
                                    </span>
                                </td>

                                <td>{{ $user->bimba_unit ?? '-' }}</td>
                                <td class="text-center fw-bold">{{ $user->no_cabang ?? '-' }}</td>

                                <td class="text-center text-muted small">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('users.edit', $user) }}"
                                       class="btn btn-sm btn-warning text-white mb-1">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('users.destroy', $user) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin hapus user {{ addslashes($user->name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <p class="mb-0">Belum ada data user</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->onEachSide(1)->links() }}
    </div>
</div>
@endsection