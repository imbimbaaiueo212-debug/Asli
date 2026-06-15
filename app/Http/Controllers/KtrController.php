<?php

namespace App\Http\Controllers;

use App\Models\Ktr;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KtrImport;

class KtrController extends Controller
{
public function index()
{
    $ktrs = Ktr::oldest()
                ->paginate(100)
                ->onEachSide(2); // menampilkan 2 link sebelum & 2 link setelah halaman aktif
    return view('ktr.index', compact('ktrs'));
}

    public function create()
    {
        return view('ktr.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'waktu' => 'required|date',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric',
        ]);

        Ktr::create($request->all());

        return redirect()->route('ktr.index')->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(Ktr $ktr)
    {
        return view('ktr.edit', compact('ktr'));
    }

    public function update(Request $request, Ktr $ktr)
    {
        $request->validate([
            'waktu' => 'required|date',
            'kategori' => 'required|string|max:255',
            'jumlah' => 'required|numeric',
        ]);

        $ktr->update($request->all());

        return redirect()->route('ktr.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(Ktr $ktr)
    {
        $ktr->delete();
        return redirect()->route('ktr.index')->with('success', 'Data berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new KtrImport, $request->file('file'));

        return redirect()->route('ktr.index')->with('success', 'Data berhasil diimport');
    }
}
