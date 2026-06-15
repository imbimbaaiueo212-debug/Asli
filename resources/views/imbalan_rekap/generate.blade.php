@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Generate Imbalan Rekap dari Profile</h3>

    <p>Akan membuat/menyinkronkan ImbalanRekap untuk <strong>{{ $profilesCount }}</strong> profile.</p>

    <form action="{{ route('imbalan_rekap.generate') }}" method="POST" onsubmit="return confirm('Yakin ingin sinkron semua profile ke imbalan rekap?');">
        @csrf
        <button class="btn btn-primary">Mulai Sinkronisasi</button>
        <a href="{{ route('imbalan_rekap.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
