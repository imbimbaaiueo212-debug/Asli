<?php

namespace App\Http\Controllers;

use App\Models\Huma;
use App\Models\Student;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HumaExport;
use App\Imports\HumasImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class HumaController extends Controller
{
    // ================== HALAMAN INDEX ==================
    public function index(Request $request)
    {
        // Tanpa global scope supaya semua data bisa tampil
        $query = Huma::withoutGlobalScope(\App\Models\Scopes\UnitScope::class);

        if ($request->filled('nama')) {
            $query->where('nama', $request->nama);
        }

        if ($request->filled('unit')) {
            $query->where('bimba_unit', $request->unit);
        }

        $humas = $query->orderByDesc('tgl_reg')
                       ->paginate(20)
                       ->withQueryString();

        return view('humas.index', compact('humas'));
    }

    // ================== TAMBAH MANUAL ==================
    public function create()
    {
        return view('humas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tgl_reg'    => 'required|date',
            'nih'        => 'required|unique:humas,nih',
            'nama'       => 'required|string|max:100',
            'status'     => 'nullable|string|max:50',
            'no_telp'    => 'nullable|string|max:50',
            'pekerjaan'  => 'nullable|string|max:100',
            'alamat'     => 'nullable|string|max:255',
            'bimba_unit' => 'nullable|string|max:150',
            'no_cabang'  => 'nullable|string|max:50',
        ]);

        Huma::create($validated);

        return redirect()->route('humas.index')->with('success', 'Data HUMAS berhasil ditambahkan.');
    }

    public function edit(Huma $huma)
    {
        return view('humas.edit', compact('huma'));
    }

    public function update(Request $request, Huma $huma)
    {
        $validated = $request->validate([
            'tgl_reg'    => 'required|date',
            'nih'        => 'required|unique:humas,nih,' . $huma->id,
            'nama'       => 'required|string|max:100',
            'status'     => 'nullable|string|max:50',
            'no_telp'    => 'nullable|string|max:50',
            'pekerjaan'  => 'nullable|string|max:100',
            'alamat'     => 'nullable|string|max:255',
            'bimba_unit' => 'nullable|string|max:150',
            'no_cabang'  => 'nullable|string|max:50',
        ]);

        $huma->update($validated);

        return redirect()->route('humas.index')->with('success', 'Data HUMAS berhasil diperbarui.');
    }

    public function destroy(Huma $huma)
    {
        $huma->delete();
        return redirect()->route('humas.index')->with('success', 'Data HUMAS berhasil dihapus.');
    }

    // ================== IMPORT EXCEL ==================
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $import = new HumasImport();
            Excel::import($import, $request->file('file'));

            return redirect()->route('humas.index')
                ->with('success', 'Import berhasil! Data telah tersimpan.');
        } catch (\Throwable $e) {
            Log::error('IMPORT HUMAS GAGAL', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'file'    => $request->file('file')?->getClientOriginalName(),
            ]);

            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    // ================== EXPORT EXCEL ==================
    public function export(Request $request)
    {
        $filters = $request->only(['nama', 'unit']);

        return Excel::download(
            new HumaExport($filters),
            'Data_Humas_' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }
}
