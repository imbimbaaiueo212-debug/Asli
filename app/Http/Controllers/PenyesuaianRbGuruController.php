<?php

namespace App\Http\Controllers;

use App\Models\PenyesuaianRbGuru;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenyesuaianRbGuruImport;


class PenyesuaianRbGuruController extends Controller
{
    public function index()
    {
        $data = PenyesuaianRbGuru::all();
        return view('rb.index', compact('data'));
    }

    public function create()
    {
        return view('rb.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jumlah_murid'   => 'required|string',
            'slot_rombim'    => 'required|string',
            'jam_kegiatan'   => 'required|string',
            'penyesuaian_rb' => 'required|string',
        ]);

        PenyesuaianRbGuru::create($request->all());

        return redirect()->route('rb.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(PenyesuaianRbGuru $rb)
    {
        return view('rb.edit', compact('rb'));
    }

    public function update(Request $request, PenyesuaianRbGuru $rb)
    {
        $request->validate([
            'jumlah_murid'   => 'required|string',
            'slot_rombim'    => 'required|string',
            'jam_kegiatan'   => 'required|string',
            'penyesuaian_rb' => 'required|string',
        ]);

        $rb->update($request->all());

        return redirect()->route('rb.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(PenyesuaianRbGuru $rb)
    {
        $rb->delete();
        return redirect()->route('rb.index')->with('success', 'Data berhasil dihapus.');
    }

      public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new PenyesuaianRbGuruImport, $request->file('file'));

        return redirect()->route('rb.index')->with('success', 'Data berhasil diimpor dari Excel.');
    }
}

