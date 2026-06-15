@extends('layouts.app')

@section('content')
<div class="app-container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Edit User</h3>

                <!-- Tombol Kembali Kondisional -->
                <a href="{{ auth()->user()->isAdminUser() ? route('users.index') : route('home') }}"
                   class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> 
                    {{ auth()->user()->isAdminUser() ? 'Kembali ke Daftar User' : 'Kembali ke Dashboard' }}
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    @php
                        $currentUser = auth()->user();

                        // Email khusus yang boleh edit unit meskipun role user
                        $emailKhusus = [
                            'oktaviandaaria@gmail.com',
                            'robiensyah22@gmail.com',
                            // tambah lagi di sini kalau perlu
                        ];

                        $bolehEditUnit = $currentUser->isAdminUser() || in_array($currentUser->email, $emailKhusus);
                    @endphp

                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Foto Profil + Inisial Aman -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if ($user->photo)
                                        <img src="{{ asset('/public/storage/' . $user->photo) }}"
                                             id="photo-preview"
                                             class="rounded-circle border border-4 border-white shadow object-fit-cover"
                                             width="140" height="140">
                                    @else
                                    <div id="photo-preview"
                                         class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white shadow"
                                         style="width:140px;height:140px;font-size:3rem;">
                                        {{ Str::upper(Str::substr($user->name ?? 'NA', 0, 2)) }}
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3">
                                <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                                <small class="text-muted">Kosongkan jika tidak ingin ganti foto</small>
                            </div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>

                            @if(auth()->user()->isAdminUser())
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach(['admin','user'] as $r)
                                        <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $r)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @else
                                <input type="text" class="form-control bg-light" value="{{ ucfirst(str_replace('_', ' ', $user->role)) }}" readonly>
                                <small class="text-muted">Role hanya dapat diubah oleh Administrator.</small>
                            @endif
                        </div>

                        <!-- Bimba Unit -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bimba Unit</label>

                            @if($bolehEditUnit)
                                <select name="bimba_unit" id="bimba_unit" class="form-select @error('bimba_unit') is-invalid @enderror">
                                    <option value="">-- Pilih Unit biMBA --</option>
                                    @forelse($units ?? [] as $unit)
                                        @if(trim($unit->biMBA_unit ?? ''))
                                            <option value="{{ $unit->biMBA_unit }}"
                                                    data-no-cabang="{{ $unit->no_cabang }}"
                                                    {{ old('bimba_unit', $user->bimba_unit) === $unit->biMBA_unit ? 'selected' : '' }}>
                                                {{ $unit->biMBA_unit }} ({{ $unit->no_cabang }})
                                            </option>
                                        @endif
                                    @empty
                                        <option disabled>Tidak ada unit tersedia</option>
                                    @endforelse
                                </select>
                                <small class="text-muted">Pilih unit → No. Cabang otomatis terisi</small>
                            @else
                                <input type="text" class="form-control" value="{{ $user->bimba_unit ?? '-' }}" readonly>
                            @endif

                            @error('bimba_unit')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- No. Cabang -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">No. Cabang</label>
                            <input type="text"
                                   name="no_cabang"
                                   id="no_cabang"
                                   class="form-control bg-light"
                                   value="{{ old('no_cabang', $user->no_cabang) }}"
                                   {{ $bolehEditUnit ? '' : 'readonly' }}
                                   placeholder="Otomatis terisi dari unit">
                        </div>

                        <!-- Password -->
                        <!-- <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Password Baru <small class="text-muted">(kosongkan jika tidak diganti)</small></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                        </div> -->
                        <div class="text-center mt-3">
<a href="{{ route('password.reset.form') }}">Lupa Password?</a>
</div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg btn-primary">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Auto Fill No. Cabang --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('bimba_unit');
        const input  = document.getElementById('no_cabang');

        if (!select || !input) return;

        function updateCabang() {
            const option = select.options[select.selectedIndex];
            input.value = option && option.value ? (option.dataset.noCabang || '') : '';
        }

        updateCabang();           // jalankan saat load
        select.addEventListener('change', updateCabang); // jalankan saat ganti
    });
</script>

@endsection