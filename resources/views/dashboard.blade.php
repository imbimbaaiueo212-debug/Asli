@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h3>Selamat datang, {{ Auth::user()->name }}</h3>
        <p>Anda berhasil login ke aplikasi.</p>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-danger">Logout</button>
        </form>
    </div>
</div>
@endsection
