<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Unit;
use App\Imports\ProdukImport;
use App\Exports\ProdukExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;   // ← BENAR (huruf i besar)

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $user = auth()->user();

    $perPage   = $request->get('per_page', 20);
    $search    = $request->get('search');
    $label     = $request->get('label');
    $jenis     = $request->get('jenis');
    $kategori  = $request->get('kategori');
    $pendataan = $request->get('pendataan');
    $bimbaUnit = $request->get('bimba_unit'); // 🔴 FILTER UNIT ADMIN

    // =============================
    // QUERY DASAR PRODUK
    // =============================
    $query = Produk::query();

    // =============================
    // FILTER UNIT (WAJIB)
    // =============================
    if ($user->isAdminUser()) {
        // Admin → boleh pilih unit
        if (!empty($bimbaUnit)) {
            $query->where('bimba_unit', $bimbaUnit);
        }
    } else {
        // User biasa → kunci ke unit sendiri
        $query->where('bimba_unit', $user->bimba_unit);
    }

    // =============================
    // SEARCH GLOBAL MULTI KOLOM
    // =============================
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('nama_produk', 'like', "%{$search}%")
              ->orWhere('kode', 'like', "%{$search}%")
              ->orWhere('label', 'like', "%{$search}%")
              ->orWhere('jenis', 'like', "%{$search}%")
              ->orWhere('kategori', 'like', "%{$search}%")
              ->orWhere('pendataan', 'like', "%{$search}%");
        });
    }

    // =============================
    // FILTER DROPDOWN
    // =============================
    if (!empty($label)) {
        $query->where('label', $label);
    }

    if (!empty($jenis)) {
        $query->where('jenis', $jenis);
    }

    if (!empty($kategori)) {
        $query->where('kategori', $kategori);
    }

    if (!empty($pendataan)) {
        $query->where('pendataan', $pendataan);
    }

    // =============================
    // URUTAN DEFAULT
    // =============================
    $query->orderBy('kode', 'asc');

    // =============================
    // PAGINATION
    // =============================
    $produks = $query->paginate($perPage)->withQueryString();

    // =============================
    // DATA DROPDOWN FILTER
    // (IKUT FILTER UNIT)
    // =============================
    $baseFilter = Produk::query();

    if ($user->isAdminUser()) {
        if (!empty($bimbaUnit)) {
            $baseFilter->where('bimba_unit', $bimbaUnit);
        }
    } else {
        $baseFilter->where('bimba_unit', $user->bimba_unit);
    }

    $labels = (clone $baseFilter)->whereNotNull('label')
        ->distinct()->orderBy('label')->pluck('label');

    $jenises = (clone $baseFilter)->whereNotNull('jenis')
        ->distinct()->orderBy('jenis')->pluck('jenis');

    $kategoris = (clone $baseFilter)->whereNotNull('kategori')
        ->distinct()->orderBy('kategori')->pluck('kategori');

    $pendataans = (clone $baseFilter)->whereNotNull('pendataan')
        ->distinct()->orderBy('pendataan')->pluck('pendataan');

    // =============================
    // DATA UNIT (UNTUK ADMIN)
    // =============================
    $units = $user->isAdminUser()
        ? Unit::orderBy('biMBA_unit')->get()
        : collect();

    // =============================
    // INFO UNIT USER (HEADER)
    // =============================
    $currentUnit = $user->bimba_unit
        ? Unit::where('biMBA_unit', $user->bimba_unit)->first()
        : null;

    // =============================
    // RETURN VIEW
    // =============================
    return view('produk.index', compact(
        'produks',
        'perPage',
        'search',
        'label',
        'jenis',
        'kategori',
        'pendataan',
        'labels',
        'jenises',
        'kategoris',
        'pendataans',
        'currentUnit',
        'units',      // 🔴 INI YANG MEMPERBAIKI ERROR
        'bimbaUnit'   // opsional (kalau mau dipakai lagi)
    ));
}



    /**
     * Show the form for creating a new resource.
     */
    public function create()
{
    // Admin bisa pilih unit, user biasa tidak perlu pilih (otomatis pakai unit sendiri)
    $units = Auth::user()->isAdminUser() 
        ? Unit::orderBy('biMBA_unit')->get() 
        : collect();

    return view('produk.create', compact('units'));
}

public function store(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'kode'        => 'required|string|max:50|unique:produk,kode',
        'kategori'    => 'required|string|max:100',
        'jenis'       => 'required|string|max:100',
        'label'       => 'required|string|max:100',
        'nama_produk' => 'required|string|max:255',
        'satuan'      => 'required|string|max:50',
        'berat'       => 'required|numeric|min:0',
        'harga'       => 'required|numeric|min:0',
        'status'      => 'required|string|in:Satuan,Paket',
        'isi'         => 'nullable|string|max:255',
        'pendataan'   => 'required|string|max:100',
    ]);

    // Tentukan bimba_unit
    if ($user->isAdminUser()) {
        $request->validate([
            'bimba_unit' => 'required|string|exists:units,biMBA_unit'
        ]);
        $bimbaUnit = $request->bimba_unit;
    } else {
        $bimbaUnit = $user->bimba_unit;
        if (!$bimbaUnit) {
            return redirect()->back()
                ->withErrors(['bimba_unit' => 'Unit biMBA Anda tidak terdeteksi.'])
                ->withInput();
        }
    }

    $unit = Unit::where('biMBA_unit', $bimbaUnit)->firstOrFail();

    // Isi semua kolom yang dibutuhkan database
    $validated['unit_id']    = $unit->id;
    $validated['bimba_unit'] = $bimbaUnit;           // ← Kembali diisi
    $validated['no_cabang']  = $unit->no_cabang;     // ← Kembali diisi

    Produk::create($validated);

    return redirect()->route('produk.index')
        ->with('success', 'Produk baru berhasil ditambahkan!');
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // findOrFail otomatis hanya ambil data unit user (kecuali admin)
        $produk = Produk::findOrFail($id);

        $units = Auth::user()->isAdminUser() 
            ? Unit::orderBy('biMBA_unit')->get() 
            : collect();

        return view('produk.edit', compact('produk', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $produk = Produk::findOrFail($id);
    $user = Auth::user();

    $rules = [
        'kode'        => 'required|string|max:50|unique:produk,kode,' . $id,
        'kategori'    => 'required|string|max:100',
        'jenis'       => 'required|string|max:100',
        'label'       => 'required|string|max:100',
        'nama_produk' => 'required|string|max:255',
        'satuan'      => 'required|string|max:50',
        'berat'       => 'required|numeric|min:0',
        'harga'       => 'required|numeric|min:0',
        'status'      => 'required|string|in:Satuan,Paket',
        'isi'         => 'nullable|string|max:255',
        'pendataan'   => 'required|string|max:100',
    ];

    if ($user->isAdminUser()) {
        $rules['bimba_unit'] = [
            'required',
            'string',
            Rule::exists('units', 'biMBA_unit'),
        ];
    }

    $validated = $request->validate($rules);

    $bimbaUnit = $user->isAdminUser() 
        ? $validated['bimba_unit'] 
        : $produk->bimba_unit; // non-admin tetap pakai unit lama

    $unit = Unit::where('biMBA_unit', $bimbaUnit)->firstOrFail();

$validated['unit_id']    = $unit->id;
$validated['bimba_unit'] = $bimbaUnit;           // ← Kembali diisi
$validated['no_cabang']  = $unit->no_cabang;     // ← Kembali diisi

$produk->update($validated);

    return redirect()->route('produk.index')
                     ->with('success', 'Produk berhasil diperbarui!');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id); // otomatis scoped
        $produk->delete();

        return redirect()->route('produk.index')
                         ->with('success', 'Produk berhasil dihapus!');
    }

    /**
     * Import produk dari Excel
     */
    /**
 * Import produk dari Excel
 */
public function import(Request $request)
{
    $user = auth()->user();

    // ================= VALIDASI =================
    $rules = [
        'file' => 'required|mimes:xlsx,xls,csv|max:10240',
    ];

    if ($user->isAdminUser()) {
        $rules['bimba_unit'] = 'required|exists:units,biMBA_unit';
    }

    $validated = $request->validate($rules);

    // ================= TENTUKAN UNIT =================
    if ($user->isAdminUser()) {
        $unit = Unit::where('biMBA_unit', $validated['bimba_unit'])->first();
    } else {
        $unit = Unit::where('biMBA_unit', $user->bimba_unit)->first();
    }

    if (!$unit) {
        return back()->with('error', 'Unit tujuan tidak ditemukan.');
    }

    // ================= PROSES IMPORT =================
    $import = new ProdukImport($user, $unit);
    Excel::import($import, $request->file('file'));

    $inserted = $import->getInserted();
    $updated  = $import->getUpdated();
    $total    = $inserted + $updated;

    return back()->with(
        $total > 0 ? 'success' : 'warning',
        $total > 0
            ? "Import selesai.
               Data baru: {$inserted}
               Data diperbarui: {$updated}
               Unit: {$unit->biMBA_unit}"
            : "Import selesai, tetapi tidak ada perubahan data."
    );
}


public function export()
{
    $user = auth()->user();

    $unit = Unit::where('biMBA_unit', $user->bimba_unit)->firstOrFail();

    $unitName = str_replace(' ', '_', strtoupper($unit->nama_unit ?? $unit->biMBA_unit ?? 'unknown'));
    $tanggal  = now('Asia/Jakarta')->format('Ymd_His');

    $fileName = "produk_{$unitName}_{$tanggal}.xlsx";

    // Opsional: log untuk debug
    \Log::info("Export produk dimulai untuk unit: {$unit->biMBA_unit} | File: {$fileName}");

    return Excel::download(
        new ProdukExport($unit),
        $fileName
    );
}

}