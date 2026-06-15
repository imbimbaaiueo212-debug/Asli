<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashAdvanceInstallment;
use App\Models\CashAdvance;
use Carbon\Carbon;

class CashAdvanceInstallmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $query = CashAdvanceInstallment::with(['cashAdvance.profile']) // ← TAMBAHKAN .profile
        ->orderBy('jatuh_tempo', 'desc')
        ->orderBy('cicilan_ke');

    // Filter berdasarkan nama karyawan
    if ($request->filled('nama')) {
        $query->whereHas('cashAdvance', function ($q) use ($request) {
            $q->where('nama', 'like', '%' . $request->nama . '%');
        });
    }

    // Filter berdasarkan status cicilan
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter berdasarkan bulan/tahun jatuh tempo
    if ($request->filled('bulan') && $request->filled('tahun')) {
        $query->whereMonth('jatuh_tempo', $request->bulan)
              ->whereYear('jatuh_tempo', $request->tahun);
    }

    // TAMBAHKAN FILTER BIMBA UNIT (BARU!)
    if ($request->filled('bimba_unit')) {
        $query->whereHas('cashAdvance.profile.unit', function ($q) use ($request) {
            $q->where('biMBA_unit', 'like', '%' . $request->bimba_unit . '%');
        });
    }

    $installments = $query->paginate(20)->withQueryString();

    // Untuk dropdown tahun
    $years = CashAdvanceInstallment::selectRaw('YEAR(jatuh_tempo) as year')
        ->distinct()
        ->orderBy('year', 'desc')
        ->pluck('year');

    // Untuk dropdown biMBA Unit (opsional, jika ingin filter)
    $unitOptions = \App\Models\Unit::orderBy('biMBA_unit')->pluck('biMBA_unit');

    return view('cash-advance.installments.index', compact('installments', 'years', 'unitOptions'));
}

    // Method lain bisa dibiarkan kosong atau di-comment kalau tidak dipakai
    public function create() { abort(404); }
    public function store(Request $request) { abort(404); }
    public function show(string $id) { abort(404); }
    public function edit(string $id) { abort(404); }
    public function update(Request $request, string $id) { abort(404); }
    public function destroy(string $id) { abort(404); }
}