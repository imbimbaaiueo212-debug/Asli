<?php

namespace App\Http\Controllers;

use App\Models\DurasiKegiatan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DurasiKegiatanImport;

class DurasiKegiatanController extends Controller
{
    public function index()
    {
        $durasiList = DurasiKegiatan::all();
        return view('durasi.index', compact('durasiList'));
    }

    public function create()
    {
        return view('durasi.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'waktu_mgg' => 'required|string',
            'waktu_bln' => 'required|string',
        ]);

        DurasiKegiatan::create($request->all());

        return redirect()->route('durasi.index')->with('success', 'Durasi berhasil ditambahkan.');
    }

    public function edit(DurasiKegiatan $durasi)
    {
        return view('durasi.edit', compact('durasi'));
    }

    public function update(Request $request, DurasiKegiatan $durasi)
    {
        $request->validate([
            'waktu_mgg' => 'required|string',
            'waktu_bln' => 'required|string',
        ]);

        $durasi->update($request->all());

        return redirect()->route('durasi.index')->with('success', 'Durasi berhasil diperbarui.');
    }

    public function destroy(DurasiKegiatan $durasi)
    {
        $durasi->delete();
        return redirect()->route('durasi.index')->with('success', 'Durasi berhasil dihapus.');
    }

     public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new DurasiKegiatanImport, $request->file('file'));

        return redirect()->route('durasi.index')->with('success', 'Data durasi berhasil diimpor dari Excel.');
    }
}
