<?php

namespace App\Http\Controllers;

use App\Models\DaftarHarga;
use Illuminate\Http\Request;

class DaftarHargaController extends Controller
{
    /**
     * Menampilkan daftar harga
     */
    public function index()
{
    $daftarHarga = DaftarHarga::orderByRaw("
        CASE 
            WHEN kategori = 'Biaya Pendaftaran' THEN 1
            WHEN kategori = 'Penjualan' THEN 2
            WHEN kategori = 'Biaya SPP Per Bulan' THEN 3
            ELSE 4
        END
    ")
    ->orderByRaw("
        CASE 
            WHEN unit = 'biMBA AIUEO' THEN 1
            WHEN unit = 'English biMBA' THEN 2
            ELSE 3
        END
    ")
    ->orderByRaw("
        CASE 
            WHEN sub_kategori = 'Duafa' THEN 1
            WHEN sub_kategori = 'Promo 2019' THEN 2
            WHEN sub_kategori = 'Daftar Ulang' THEN 3
            WHEN sub_kategori = 'Spesial' THEN 4
            ELSE 5
        END
    ")
    ->get();

    return view('daftar_harga.index', compact('daftarHarga'));
}
    /**
     * Menampilkan form tambah data
     */
    public function create()
    {
        return view('daftar_harga.create');
    }

    /**
     * Menyimpan data baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'nullable|string|max:255',
            'sub_kategori' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'harga_a' => 'nullable|numeric',
            'harga_b' => 'nullable|numeric',
            'harga_c' => 'nullable|numeric',
            'harga_d' => 'nullable|numeric',
            'harga_e' => 'nullable|numeric',
            'harga_f' => 'nullable|numeric',
        ]);

        DaftarHarga::create($request->all());

        return redirect()->route('daftar-harga.index')
                         ->with('success', 'Data berhasil ditambahkan!');
    }

    /**
     * Menampilkan form edit
     */
    public function edit($id)
    {
        $data = DaftarHarga::findOrFail($id);
        return view('daftar_harga.edit', compact('data'));
    }

    /**
     * Memperbarui data
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori' => 'nullable|string|max:255',
            'sub_kategori' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:255',
            'deskripsi' => 'nullable|string|max:255',
            'harga_a' => 'nullable|numeric',
            'harga_b' => 'nullable|numeric',
            'harga_c' => 'nullable|numeric',
            'harga_d' => 'nullable|numeric',
            'harga_e' => 'nullable|numeric',
            'harga_f' => 'nullable|numeric',
        ]);

        $data = DaftarHarga::findOrFail($id);
        $data->update($request->all());

        return redirect()->route('daftar-harga.index')
                         ->with('success', 'Data berhasil diupdate!');
    }

    /**
     * Menghapus data
     */
    public function destroy($id)
    {
        $data = DaftarHarga::findOrFail($id);
        $data->delete();

        return redirect()->route('daftar-harga.index')
                         ->with('success', 'Data berhasil dihapus!');
    }
}
