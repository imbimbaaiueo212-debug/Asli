@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="login-header text-center mb-5">
        <h2 class="fw-bold" style="color: #222;">Reset Password</h2>
        <p style="color: #555;">Masukkan email dan password baru Anda</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center mb-4">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.reset.manual') }}" novalidate>
        @csrf

        <!-- Hidden token jika Laravel memerlukan (untuk reset dengan token) -->
        <!-- Jika route ini pakai token dari email, tambahkan: -->
        <!-- <input type="hidden" name="token" value="{{ $token ?? '' }}"> -->

        <div class="form-group">
            <div class="neu-input">
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="{{ old('email') }}" 
                    placeholder=" " 
                    required 
                    autocomplete="email"
                >
                <label for="email">Email</label>
            </div>
            <span class="error-message" id="emailError"></span>
        </div>

        <div class="form-group">
            <div class="neu-input password-group">
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    placeholder=" " 
                    required 
                    autocomplete="new-password"
                >
                <label for="password">Password Baru</label>
                <button type="button" class="password-toggle" id="passwordToggle">
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            <span class="error-message" id="passwordError"></span>
        </div>

        <div class="form-group">
            <div class="neu-input password-group">
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </div>
                <input 
                    type="password" 
                    name="password_confirmation" 
                    id="password_confirmation" 
                    placeholder=" " 
                    required 
                    autocomplete="new-password"
                >
                <label for="password_confirmation">Konfirmasi Password</label>
                <button type="button" class="password-toggle" id="passwordConfirmToggle">
                    <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
            <span class="error-message" id="passwordConfirmError"></span>
        </div>

        <button type="submit" class="neu-button">Reset Password</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none" style="color: #6a11cb; font-weight: 500;">← Kembali ke Login</a>
    </div>
@endsection