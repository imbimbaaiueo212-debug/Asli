@extends('layouts.auth')

@section('title', 'Login')

@section('content')
    <h3 class="text-center mb-4 fw-bold" style="color: #222;">Login</h3>

    @if (session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger text-center">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.process') }}" novalidate>
        @csrf

        <div class="form-group">
            <div class="neu-input">
                <div class="input-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                </div>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder=" " required autocomplete="email">
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
                <input type="password" name="password" id="password" placeholder=" " required autocomplete="current-password">
                <label for="password">Password</label>
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

        <button type="submit" class="neu-button mt-3">Login</button>
                <div class="social-divider">
            <div class="line"></div>
            <div class="line"></div>
        </div>

        <div class="social-buttons">
            <!-- Instagram - arahkan ke halaman login IG atau profil
            <a href="https://www.instagram.com/accounts/login/" target="_blank" class="neu-social social-ig" title="Masuk dengan Instagram">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.332.014 7.052.072 2.578.227.225 2.58.072 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.153 4.472 2.506 6.825 6.978 6.978 1.28.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.472-.153 6.825-2.506 6.978-6.978.058-1.28.072-1.689.072-4.948 0-3.259-.014-3.668-.072-4.948C18.825.225 16.472-.128 12 0z"/>
                    <circle cx="12" cy="12" r="3.5"/>
                </svg>
            </a> -->

            <!-- Facebook - arahkan ke login FB 
            <a href="https://www.facebook.com/login/" target="_blank" class="neu-social social-fb" title="Masuk dengan Facebook">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </a> -->

            <!-- Google - arahkan ke login Google 
            <a href="https://accounts.google.com/signin" target="_blank" class="neu-social social-google" title="Masuk dengan Google">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
            </a> -->

            <!-- Twitter/X 
            <a href="https://twitter.com/i/flow/login" target="_blank" class="neu-social social-twitter" title="Masuk dengan X">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </a> -->

    <div class="text-center mt-4">
        <a href="{{ route('password.reset.form') }}">Lupa Password?</a>
    </div>
@endsection