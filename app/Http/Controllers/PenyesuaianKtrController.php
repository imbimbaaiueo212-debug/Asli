<?php

namespace App\Http\Controllers;

use App\Models\PenyesuaianKtr;
use APP\Models\Ktr;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenyesuaianKtrImport;

class PenyesuaianKtrController extends Controller
{
    public function index()
    {
        $data = PenyesuaianKtr::all();
        return view('penyesuaian.index', compact('data'));
    }

    public function create()
    {
        return view('penyesuaian.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jumlah_murid' => 'required|string',
            'penyesuaian_ktr' => 'required|string',
        ]);

        PenyesuaianKtr::create($request->all());

        return redirect()->route('penyesuaian.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(PenyesuaianKtr $penyesuaian)
    {
        return view('penyesuaian.edit', compact('penyesuaian'));
    }

    public function update(Request $request, PenyesuaianKtr $penyesuaian)
    {
        $request->validate([
            'jumlah_murid' => 'required|string',
            'penyesuaian_ktr' => 'required|string',
        ]);

        $penyesuaian->update($request->all());

        return redirect()->route('penyesuaian.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(PenyesuaianKtr $penyesuaian)
    {
        $penyesuaian->delete();
        return redirect()->route('penyesuaian.index')->with('success', 'Data berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
        ]);

        Excel::import(new PenyesuaianKtrImport, $request->file('file'));

        return redirect()->route('penyesuaian.index')->with('success', 'Data berhasil diimpor dari Excel.');
    }
}
