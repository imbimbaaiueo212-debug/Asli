<?php

namespace App\Http\Controllers;

use App\Models\HargaSaptataruna;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HargaSaptatarunaImport;

class HargaSaptatarunaController extends Controller
{
    public function index()
    {
        $items = HargaSaptataruna::all();
        return view('harga.index', compact('items'));
    }

    public function create()
    {
        return view('harga.create');
    }

    // ================= STORE (CREATE) =================
    public function store(Request $request)
{
    $data = $request->validate([
        'kategori'      => 'nullable|string|max:255',
        'sub_kategori'  => 'nullable|string|max:255',
        'kode'          => 'nullable|string|max:50',
        'nama'          => 'nullable|string|max:255',
        'duafa'         => 'nullable|numeric',
        'promo_2019'    => 'nullable|numeric',
        'daftar_ulang'  => 'nullable|numeric',
        'spesial'       => 'nullable|numeric',
        'umum1'         => 'nullable|numeric',
        'umum2'         => 'nullable|numeric',
        'harga'         => 'nullable|numeric',
        'a'             => 'nullable|numeric',
        'b'             => 'nullable|numeric',
        'c'             => 'nullable|numeric',
        'd'             => 'nullable|numeric',
        'e'             => 'nullable|numeric',
        'f'             => 'nullable|numeric',
        'promo_umum'    => 'nullable|numeric',   // ← Tambahkan ini
    ]);

    // 🔥 FIX: Ubah empty string menjadi null untuk kolom numeric
    $numericFields = [
        'duafa', 'promo_2019', 'daftar_ulang', 'spesial', 
        'umum1', 'umum2', 'harga', 'a', 'b', 'c', 'd', 'e', 'f',
        'promo_umum'        // ← Tambahkan ini
    ];

    foreach ($numericFields as $field) {
        if (isset($data[$field]) && $data[$field] === '') {
            $data[$field] = null;
        }
    }

    HargaSaptataruna::create($data);

    return redirect()->route('harga.index')
                     ->with('success', 'Data berhasil ditambahkan.');
}

    public function show(HargaSaptataruna $hargaSaptataruna)
    {
        return view('harga.show', compact('hargaSaptataruna'));
    }

    public function edit(HargaSaptataruna $harga)
    {
        return view('harga.edit', compact('harga'));
    }

    // ================= UPDATE =================
   public function update(Request $request, HargaSaptataruna $harga)
{
    $numericFields = [
        'duafa', 'promo_2019', 'daftar_ulang', 'spesial',
        'umum1', 'umum2', 'harga', 'a', 'b', 'c', 'd', 'e', 'f',
        'promo_umum'        // ← Tambahkan ini
    ];

    // Format angka Indonesia
    foreach ($numericFields as $field) {
        if ($request->$field === null || $request->$field === '') {
            $request[$field] = null;
        } else {
            $request[$field] = str_replace('.', '', $request[$field]);
            $request[$field] = str_replace(',', '.', $request[$field]);
        }
    }

    $data = $request->validate([
        'kategori'      => 'nullable|string|max:255',
        'sub_kategori'  => 'nullable|string|max:255',
        'kode'          => 'nullable|string|max:50',
        'nama'          => 'nullable|string|max:255',

        'duafa'         => 'nullable|numeric',
        'promo_2019'    => 'nullable|numeric',
        'daftar_ulang'  => 'nullable|numeric',
        'spesial'       => 'nullable|numeric',
        'umum1'         => 'nullable|numeric',
        'umum2'         => 'nullable|numeric',
        'harga'         => 'nullable|numeric',

        'a'             => 'nullable|numeric',
        'b'             => 'nullable|numeric',
        'c'             => 'nullable|numeric',
        'd'             => 'nullable|numeric',
        'e'             => 'nullable|numeric',
        'f'             => 'nullable|numeric',
        'promo_umum'    => 'nullable|numeric',   // ← Tambahkan ini
    ]);

    $harga->update($data);

    return redirect()->route('harga.index')
        ->with('success', 'Data berhasil diupdate.');
}
    public function destroy($id)
    {
        $item = HargaSaptataruna::findOrFail($id);
        $item->delete();

        return redirect()->route('harga.index')
                         ->with('success', 'Data berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new HargaSaptatarunaImport, $request->file('file'));

        return redirect()->route('harga.index')
                         ->with('success', 'Data berhasil diimport dari Excel.');
    }
}