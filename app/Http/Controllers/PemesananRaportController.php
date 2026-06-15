<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemesananRaport;

class PemesananRaportController extends Controller
{
    // Menampilkan semua data
    public function index()
    {
        $data = PemesananRaport::all();
        return view('pemesanan_raport.index', compact('data'));
    }

    // Form untuk menambahkan data baru
    public function create()
    {
        return view('pemesanan_raport.create');
    }

    // Menyimpan data baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'nim' => 'required|unique:pemesanan_raport,nim',
            'nama_murid' => 'required',
        ]);

        PemesananRaport::create($request->all());

        return redirect()->route('pemesanan_raport.index')
                         ->with('success', 'Data berhasil ditambahkan!');
    }

    // Form untuk edit data
    public function edit(PemesananRaport $pemesananRaport)
    {
        return view('pemesanan_raport.edit', compact('pemesananRaport'));
    }

    // Update data di database
    public function update(Request $request, PemesananRaport $pemesananRaport)
    {
        $request->validate([
            'nim' => 'required|unique:pemesanan_raport,nim,' . $pemesananRaport->id,
            'nama_murid' => 'required',
        ]);

        $pemesananRaport->update($request->all());

        return redirect()->route('pemesanan_raport.index')
                         ->with('success', 'Data berhasil diperbarui!');
    }

    // Hapus data
    public function destroy(PemesananRaport $pemesananRaport)
    {
        $pemesananRaport->delete();

        return redirect()->route('pemesanan_raport.index')
                         ->with('success', 'Data berhasil dihapus!');
    }
}
