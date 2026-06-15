<?php

namespace App\Http\Controllers;

use App\Models\Vocer;
use Illuminate\Http\Request;

class VocerController extends Controller
{
    // Tampilkan semua data
    public function index()
    {
        $vocers = Vocer::orderBy('id', 'desc')->get();
        return view('vocers.index', compact('vocers'));
    }

    // Form create
    public function create()
    {
        return view('vocers.create');
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $data = $request->validate([
            'numerator' => 'nullable|integer',
            'kategori_v' => 'nullable|string|max:255',
            'nilai_v' => 'nullable|integer',
            'tgl_peny' => 'nullable|date',
            'st_v' => 'nullable|string|max:255',
            'va_murid_humas' => 'nullable|string|max:255',
            'va_murid_humas_1' => 'nullable|string|max:255',
            'va_murid_humas_2' => 'nullable|string|max:255',
            'nama_murid_humas' => 'nullable|string|max:255',
            'va_murid_baru' => 'nullable|string|max:255',
            'va_murid_baru_1' => 'nullable|string|max:255',
            'va_murid_baru_2' => 'nullable|string|max:255',
            'nama_murid_baru' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        Vocer::create($data);

        return redirect()->route('vocers.index')->with('success', 'Data berhasil ditambahkan.');
    }

    // Form edit
    public function edit(Vocer $vocer)
    {
        return view('vocers.edit', compact('vocer'));
    }

    // Update data
    public function update(Request $request, Vocer $vocer)
    {
        $data = $request->validate([
            'numerator' => 'nullable|integer',
            'kategori_v' => 'nullable|string|max:255',
            'nilai_v' => 'nullable|integer',
            'tgl_peny' => 'nullable|date',
            'st_v' => 'nullable|string|max:255',
            'va_murid_humas' => 'nullable|string|max:255',
            'va_murid_humas_1' => 'nullable|string|max:255',
            'va_murid_humas_2' => 'nullable|string|max:255',
            'nama_murid_humas' => 'nullable|string|max:255',
            'va_murid_baru' => 'nullable|string|max:255',
            'va_murid_baru_1' => 'nullable|string|max:255',
            'va_murid_baru_2' => 'nullable|string|max:255',
            'nama_murid_baru' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $vocer->update($data);

        return redirect()->route('vocers.index')->with('success', 'Data berhasil diupdate.');
    }

    // Hapus data
    public function destroy(Vocer $vocer)
    {
        $vocer->delete();
        return redirect()->route('vocers.index')->with('success', 'Data berhasil dihapus.');
    }

}
