<?php

namespace App\Http\Controllers;

use App\Models\Lembur;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LemburController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $unitFilter = $request->unit;

        $query = Lembur::with('profile')
            ->orderBy('tgl_lembur', 'desc')
            ->orderBy('jam_awal', 'desc');

        if ($search) {
            $query->whereHas('profile', function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nik', 'like', "%$search%");
            });
        }

        if ($unitFilter) {
            $query->whereHas('profile', function($q) use ($unitFilter) {
                $q->where('biMBA_unit', $unitFilter);
            });
        }

        $lembur = $query->paginate(20);

        $units = Profile::distinct()->pluck('biMBA_unit');

        return view('lembur.index', compact('lembur', 'units', 'search', 'unitFilter'));
    }

    public function create()
{
    $isAdmin = Auth::user()->is_admin ?? false;

    $units = Profile::whereNotNull('biMBA_unit')
                ->distinct()
                ->orderBy('biMBA_unit')
                ->pluck('biMBA_unit');

    // Jika bukan admin, langsung tampilkan semua karyawan aktif
    $profiles = $isAdmin ? collect() : Profile::whereIn('status_karyawan', ['Aktif', 'Magang'])
                    ->orderBy('nama')
                    ->get(['id', 'nik', 'nama', 'jabatan', 'biMBA_unit', 'no_cabang']);

    return view('lembur.create', compact('profiles', 'units', 'isAdmin'));
}

    public function store(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'tgl_lembur' => 'required|date',
            'jam_awal' => 'required',
            'jam_selesai' => 'required|after:jam_awal',
            'keterangan' => 'nullable|string',
        ]);

        Lembur::create($request->all());

        return redirect()->route('lembur.index')
            ->with('success', 'Data lembur berhasil ditambahkan');
    }

    public function edit(Lembur $lembur)
{
    $isAdmin = Auth::user()->is_admin ?? false;

    // Tampilkan SEMUA karyawan aktif (bukan hanya dari unit tertentu)
    $profiles = Profile::whereIn('status_karyawan', ['Aktif', 'Magang'])
                    ->orderBy('nama')
                    ->get(['id', 'nik', 'nama', 'jabatan', 'biMBA_unit']);

    return view('lembur.edit', compact('lembur', 'profiles', 'isAdmin'));
}
    public function update(Request $request, Lembur $lembur)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'tgl_lembur' => 'required|date',
            'jam_awal' => 'required',
            'jam_selesai' => 'required|after:jam_awal',
            'keterangan' => 'nullable|string',
            'status' => 'in:Diajukan,Disetujui,Ditolak'
        ]);

        $lembur->update($request->all());

        return redirect()->route('lembur.index')
            ->with('success', 'Data lembur berhasil diperbarui');
    }

    public function destroy(Lembur $lembur)
    {
        $lembur->delete();
        return back()->with('success', 'Data lembur dihapus');
    }

    public function getProfilesByUnit(Request $request)
{
    $unit = trim($request->bimba_unit);

    if (empty($unit)) {
        return response()->json(['error' => 'Unit kosong'], 400);
    }

    try {
        $profiles = Profile::where('biMBA_unit', $unit)
                    ->whereIn('status_karyawan', ['Aktif', 'Magang'])
                    ->orderBy('nama')
                    ->get(['id', 'nik', 'nama', 'jabatan']);

        Log::info("Get Profiles by Unit: {$unit} | Found: " . $profiles->count());

        return response()->json($profiles);

    } catch (\Exception $e) {
        Log::error("Error getProfilesByUnit: " . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}