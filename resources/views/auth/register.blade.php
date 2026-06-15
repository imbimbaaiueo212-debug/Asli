@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<h3 class="text-center mb-4">Register</h3>

{{-- Tampilkan error validasi --}}
@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('register.process') }}">
  @csrf
  <div class="mb-3">
    <input type="text" name="name" class="form-control" placeholder="Nama Lengkap" value="{{ old('name') }}" required>
  </div>
  <div class="mb-3">
    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
  </div>
  <div class="mb-3">
    <input type="password" name="password" class="form-control" placeholder="Password" required>
  </div>
  <div class="mb-3">
    <input type="password" name="password_confirmation" class="form-control" placeholder="Konfirmasi Password" required>
  </div>
  <button type="submit" class="btn btn-success w-100">Register</button>
</form>

<div class="text-center mt-3">
  <a href="{{ route('login') }}">Sudah punya akun? Login</a>
</div>
@endsection
