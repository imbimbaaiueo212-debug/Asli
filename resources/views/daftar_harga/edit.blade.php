@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Edit Data Daftar Harga</h1>

    <a href="{{ route('daftar-harga.index') }}" class="btn btn-secondary mb-3">Kembali</a>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('daftar-harga.update', $data->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label>Kategori</label>
            <input type="text" name="kategori" class="form-control" value="{{ $data->kategori }}" readonly>
        </div>

        <div class="mb-3">
            <label>Sub Kategori</label>
            <input type="text" name="sub_kategori" class="form-control" value="{{ $data->sub_kategori }}">
        </div>

        <div class="mb-3">
            <label>Unit</label>
            <input type="text" name="unit" class="form-control" value="{{ $data->unit }}">
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <input type="text" name="deskripsi" class="form-control" value="{{ $data->deskripsi }}">
        </div>

        <h5>Harga</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>A</th>
                    <th>B</th>
                    <th>C</th>
                    <th>D</th>
                    <th>E</th>
                    <th>F</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="number" name="harga_a" class="form-control" value="{{ $data->harga_a }}" step="0.01"></td>
                    <td><input type="number" name="harga_b" class="form-control" value="{{ $data->harga_b }}" step="0.01"></td>
                    <td><input type="number" name="harga_c" class="form-control" value="{{ $data->harga_c }}" step="0.01"></td>
                    <td><input type="number" name="harga_d" class="form-control" value="{{ $data->harga_d }}" step="0.01"></td>
                    <td><input type="number" name="harga_e" class="form-control" value="{{ $data->harga_e }}" step="0.01"></td>
                    <td><input type="number" name="harga_f" class="form-control" value="{{ $data->harga_f }}" step="0.01"></td>
                </tr>
            </tbody>
        </table>

        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
@endsection
