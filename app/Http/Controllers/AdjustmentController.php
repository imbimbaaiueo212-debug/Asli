<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Adjustment::query();

        // Filter berdasarkan BIMBA Unit
        if ($request->filled('bimba_unit')) {
            $query->where('bimba_unit', 'like', '%' . $request->bimba_unit . '%');
        }

        // Filter berdasarkan Nama
        if ($request->filled('nama')) {
            $query->where('nama', 'like', '%' . $request->nama . '%');
        }

        // Filter berdasarkan Tipe
        if ($request->filled('type') && in_array($request->type, ['potongan', 'tambahan'])) {
            $query->where('type', $request->type);
        }

        // Filter berdasarkan Bulan
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter berdasarkan Tahun
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $adjustments = $query->latest('year')->latest('month')->paginate(20)->withQueryString();

        // Data untuk dropdown filter
        $bimbaUnits = Adjustment::distinct()->orderBy('bimba_unit')->pluck('bimba_unit');
        $namas      = Adjustment::distinct()->orderBy('nama')->pluck('nama');
        $years      = Adjustment::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('adjustments.index', compact(
            'adjustments',
            'bimbaUnits',
            'namas',
            'years',
            'months'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $years = range(now()->year - 2, now()->year + 2);

        // Ambil semua profile untuk dropdown NIK + Nama
        $profiles = Profile::select('nik', 'nama', 'jabatan', 'biMBA_unit', 'no_cabang', 'tgl_masuk', 'masa_kerja')
            ->orderBy('nama')
            ->get();

        return view('adjustments.create', compact('months', 'years', 'profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik'        => 'required|string|exists:profiles,nik',
            'nominal'    => 'required|numeric|min:0',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2000|max:2100',
            'type'       => 'required|in:potongan,tambahan',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        // Ambil data profile berdasarkan NIK
        $profile = Profile::where('nik', $validated['nik'])->firstOrFail();

        Adjustment::create([
            'nik'         => $profile->nik,
            'nama'        => $profile->nama,
            'jabatan'     => $profile->jabatan ?? null,
            'tanggal_masuk' => $profile->tgl_masuk ?? null,
            'bimba_unit'  => $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit,
            'no_cabang'   => $profile->no_cabang,
            'nominal'     => $validated['nominal'],
            'month'       => $validated['month'],
            'year'        => $validated['year'],
            'type'        => $validated['type'],
            'keterangan'  => $validated['keterangan'],
        ]);

        return redirect()
            ->route('adjustments.index')
            ->with('success', 'Adjustment berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Adjustment $adjustment): View
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $years = range(now()->year - 2, now()->year + 2);

        $profiles = Profile::select('nik', 'nama', 'jabatan', 'biMBA_unit', 'no_cabang')
            ->orderBy('nama')
            ->get();

        return view('adjustments.edit', compact('adjustment', 'months', 'years', 'profiles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Adjustment $adjustment): RedirectResponse
    {
        $validated = $request->validate([
            'nik'        => 'required|string|exists:profiles,nik',
            'nominal'    => 'required|numeric|min:0',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2000|max:2100',
            'type'       => 'required|in:potongan,tambahan',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $profile = Profile::where('nik', $validated['nik'])->firstOrFail();

        $adjustment->update([
            'nik'         => $profile->nik,
            'nama'        => $profile->nama,
            'jabatan'     => $profile->jabatan ?? null,
            'tanggal_masuk' => $profile->tgl_masuk ?? null,
            'bimba_unit'  => $profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit,
            'no_cabang'   => $profile->no_cabang,
            'nominal'     => $validated['nominal'],
            'month'       => $validated['month'],
            'year'        => $validated['year'],
            'type'        => $validated['type'],
            'keterangan'  => $validated['keterangan'],
        ]);

        return redirect()
            ->route('adjustments.index')
            ->with('success', 'Adjustment berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Adjustment $adjustment): RedirectResponse
    {
        $adjustment->delete();

        return redirect()
            ->route('adjustments.index')
            ->with('success', 'Adjustment berhasil dihapus!');
    }
}