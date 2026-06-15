{{-- resources/views/users/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="app-container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            {{-- Alert success kalau ada --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Profil Pengguna
                    </h4>
                </div>

                <div class="card-body text-center pt-4">

                    {{-- Foto Profil Besar --}}
                    <div class="mb-4 position-relative d-inline-block">
                        @if ($user->photo)
                            <img src="{{ asset('public/storage/' . $user->photo) }}"
                                 alt="Foto {{ $user->name }}"
                                 class="rounded-circle border border-4 border-white shadow-sm object-fit-cover"
                                 width="140" height="140">
                        @else
                            <div class="bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center text-white shadow-sm"
                                 style="width: 140px; height: 140px; font-size: 3rem;">
                                {{ Str::substr($user->name, 0, 2) }}
                            </div>
                        @endif
                    </div>

                    <h3 class="fw-bold mb-1">{{ $user->name }}</h3>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    {{-- Badge Role --}}
                    @php
                        $badgeColors = [
                            'admin'       => 'danger',
                            'pusat'       => 'dark',
                            'developer'   => 'info',
                            'superadmin'  => 'warning text-dark',
                            'owner'       => 'warning text-dark',
                            'direktur'    => 'primary',
                            'cabang'      => 'success',
                            'guru'        => 'secondary',
                        ];
                        $color = $badgeColors[strtolower($user->role)] ?? 'secondary';
                    @endphp

                    <span class="badge bg-{{ $color }} fs-6 px-3 py-2">
                        <i class="fas fa-shield-alt me-1"></i>
                        {{ Str::upper($user->role ?? 'User') }}
                    </span>
                </div>

                <div class="card-body border-top">
                    <div class="row g-4">
                        <div class="col-12">
                            <table class="table table-borderless table-sm">
                                <tbody>
                                    <tr>
                                        <th width="140">Nama Lengkap</th>
                                        <td>: {{ $user->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>: {{ $user->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Role</th>
                                        <td>: <strong>{{ ucfirst($user->role ?? '-') }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Bimba Unit</th>
                                        <td>: {{ $user->bimba_unit ?? '_'}}</td>
                                    </tr>
                                    <tr>
                                        <th>No. Cabang</th>
                                        <td>: <span class="fw-bold">{{ $user->no_cabang ?? '-' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Bergabung sejak</th>
                                        <td>: {{ $user->created_at->format('d F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir update</th>
                                        <td>: {{ $user->updated_at->diffForHumans() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light text-center py-3">
                    <div class="btn-group" role="group">
                        <a href="{{ route('users.edit', $user) }}"
                           class="btn btn-warning text-white">
                            <i class="fas fa-edit me-1"></i> Edit Profil
                        </a>
<a href="{{ auth()->user()->role === 'admin' ? route('users.index') : route('home') }}"
   class="btn btn-secondary">
    <i class="fas fa-arrow-left me-1"></i> 
    {{ auth()->user()->role === 'admin' ? 'Kembali ke Daftar User' : 'Kembali ke Home' }}
</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection