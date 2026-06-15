{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="app-container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Tambah User Baru</h3>
                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
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
                        $emailKhusus = ['oktaviandaaria@gmail.com', 'robiensyah22@gmail.com'];
                        $bolehEditUnit = $currentUser->isAdminUser() || in_array($currentUser->email, $emailKhusus);

                        // Ambil semua unit untuk create (sama seperti edit)
                        $units = \App\Models\Unit::orderBy('biMBA_unit')->get();
                    @endphp

                    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Foto Profil (preview inisial) -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div id="photo-preview"
                                     class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white shadow"
                                     style="width:140px;height:140px;font-size:3rem;">
                                    {{ Str::upper(Str::substr(old('name', ''), 0, 2)) ?: 'NA' }}
                                </div>
                            </div>
                            <div class="mt-3">
                                <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
                                <small class="text-muted">Opsional</small>
                            </div>
                        </div>

                        <!-- Nama -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach(['admin','user'] as $r)
                                    <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $r)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <!-- Bimba Unit — SAMA PERSIS seperti di edit -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Bimba Unit</label>

                            @if($bolehEditUnit)
                                <select name="bimba_unit" id="bimba_unit" class="form-select @error('bimba_unit') is-invalid @enderror">
                                    <option value="">-- Pilih Unit biMBA --</option>
                                    @foreach($units as $unit)
                                        @if(trim($unit->biMBA_unit))
                                            <option value="{{ $unit->biMBA_unit }}"
                                                    data-no-cabang="{{ $unit->no_cabang }}"
                                                    {{ old('bimba_unit') === $unit->biMBA_unit ? 'selected' : '' }}>
                                                {{ $unit->biMBA_unit }} ({{ $unit->no_cabang }})
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="text-muted">Pilih unit → No. Cabang otomatis terisi</small>
                            @else
                                <input type="text" class="form-control" value="-" readonly>
                                <input type="hidden" name="bimba_unit" value="">
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
                                   value="{{ old('no_cabang') }}"
                                   {{ $bolehEditUnit ? '' : 'readonly' }}
                                   placeholder="Otomatis terisi dari unit">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-lg btn-primary">
                                Simpan User Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript Auto Fill No. Cabang di Create juga --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const select = document.getElementById('bimba_unit');
        const input  = document.getElementById('no_cabang');

        if (!select || !input) return;

        function updateCabang() {
            const option = select.options[select.selectedIndex];
            input.value = option && option.value ? (option.dataset.noCabang || '') : '';
        }

        updateCabang();
        select.addEventListener('change', updateCabang);

        // Update inisial saat ketik nama
        const nameInput = document.querySelector('input[name="name"]');
        const preview = document.getElementById('photo-preview');
        if (nameInput && preview) {
            nameInput.addEventListener('input', function(e) {
                const name = e.target.value.trim();
                preview.textContent = name ? name.substring(0,2).toUpperCase() : 'NA';
            });
        }
    });
</script>
@endsection