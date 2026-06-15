<?php

namespace App\Http\Controllers;

use App\Models\MbcMurid;
use Illuminate\Http\Request;

class MbcMuridController extends Controller
{
    // Menampilkan daftar MBC Murid
    public function index()
    {
        $murid = MbcMurid::orderBy('id', 'asc')->get();
        return view('mbc_murid.index', compact('murid'));
    }

    // Form tambah MBC Murid
    public function create()
    {
        return view('mbc_murid.create');
    }

    // Menyimpan data MBC Murid baru
    public function store(Request $request)
    {
        $request->validate([
            'no_cabang' => 'nullable|string',
            'nama_unit' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'no_pembayaran' => 'nullable|string',
            'nama_murid' => 'nullable|string',
            'kelas' => 'nullable|string',
            'golongan_kode' => 'nullable|string',
            'spp' => 'nullable|numeric',
            'wali_murid' => 'nullable|string',
            'bill_payment' => 'nullable|string',
            'virtual_account' => 'nullable|string',
        ]);

        MbcMurid::create($request->all());

        return redirect()->route('mbc-murid.index')->with('success', 'Data MBC Murid berhasil ditambahkan!');
    }

    // Form edit MBC Murid
    public function edit($id)
    {
        $murid = MbcMurid::findOrFail($id);
        return view('mbc_murid.edit', compact('murid'));
    }

    // Update data MBC Murid
    public function update(Request $request, $id)
    {
        $murid = MbcMurid::findOrFail($id);

        $request->validate([
            'no_cabang' => 'nullable|string',
            'nama_unit' => 'nullable|string',
            'no_telp' => 'nullable|string',
            'email' => 'nullable|email',
            'alamat' => 'nullable|string',
            'no_pembayaran' => 'nullable|string',
            'nama_murid' => 'nullable|string',
            'kelas' => 'nullable|string',
            'golongan_kode' => 'nullable|string',
            'spp' => 'nullable|numeric',
            'wali_murid' => 'nullable|string',
            'bill_payment' => 'nullable|string',
            'virtual_account' => 'nullable|string',
        ]);

        $murid->update($request->all());

        return redirect()->route('mbc-murid.index')->with('success', 'Data MBC Murid berhasil diupdate!');
    }

    // Hapus data MBC Murid
    public function destroy($id)
    {
        $murid = MbcMurid::findOrFail($id);
        $murid->delete();

        return redirect()->route('mbc-murid.index')->with('success', 'Data MBC Murid berhasil dihapus!');
    }
}
