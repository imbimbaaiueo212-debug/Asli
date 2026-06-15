<?php

namespace App\Http\Controllers;

use App\Models\AbsensiRelawan;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use App\Models\Unit; // TAMBAHAN: untuk ambil data unit
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AbsensiRelawanImport;
use App\Exports\AbsensiRelawanExport;  // ← ini yang harus dipakai sekarang
use Illuminate\Support\Carbon;

class AbsensiRelawanController extends Controller
{
    public function index(Request $request)
{
    $perPage        = (int) $request->input('per_page', 10);
    $q              = trim((string) $request->input('q', ''));
    $filterNik      = $request->input('nik');
    $filterUnit     = $request->input('bimba_unit');
    $filterTanggal  = $request->input('tanggal');

    $user = Auth::user();

    // === DROPDOWN UNIT (ADMIN) ===
    $unitOptions = $user->is_admin
        ? Profile::whereNotNull('bimba_unit')
            ->where('bimba_unit', '!=', '')
            ->distinct()
            ->orderBy('bimba_unit')
            ->pluck('bimba_unit')
        : collect();

    // === DROPDOWN NIK / NAMA ===
    $muridQuery = Profile::select('nik as nim', 'nama as nama_murid')
        ->orderBy('nik');

    if (!$user->is_admin && $user->bimba_unit ?? false) {
        $muridQuery->where('bimba_unit', $user->bimba_unit);
    }

    if ($filterUnit) {
        $muridQuery->where('bimba_unit', $filterUnit);
    }

    $muridOptions = $muridQuery->get();

    // ============================
    // ✅ QUERY UTAMA (JOIN 🔥)
    // ============================
    $query = AbsensiRelawan::query()
        ->leftJoin('absensi_volunteer as av', function ($join) {
            $join->on('absensi_relawan.nik', '=', 'av.nik')
                 ->on('absensi_relawan.tanggal', '=', 'av.tanggal');
        })
        ->leftJoin('profiles as p', 'absensi_relawan.nik', '=', 'p.nik')
        ->select([
            'absensi_relawan.*',
            'av.alasan as volunteer_alasan',
            'av.keterangan as volunteer_keterangan',
            'p.jabatan',
            'p.departemen',
        ]);

    // ============================
    // FILTER
    // ============================

    if ($filterNik) {
        $query->where('absensi_relawan.nik', $filterNik);
    } elseif ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('absensi_relawan.nama_relawaan', 'like', "%{$q}%")
              ->orWhere('absensi_relawan.nik', 'like', "%{$q}%");
        });
    }

    if ($filterUnit) {
        $query->where('absensi_relawan.bimba_unit', $filterUnit);
    }

    if (!$user->is_admin && $user->bimba_unit ?? false) {
        $query->where('absensi_relawan.bimba_unit', $user->bimba_unit);
    }

    if ($filterTanggal) {
        $query->whereDate('absensi_relawan.tanggal', $filterTanggal);
    }

    if ($request->status) {
        $query->where('absensi_relawan.status', $request->status);
    }

    $absensi = $query->orderByDesc('absensi_relawan.tanggal')
        ->paginate($perPage)
        ->appends($request->query());

    $filters = [
        'q'           => $q,
        'per_page'    => $perPage,
        'nik'         => $filterNik,
        'bimba_unit'  => $filterUnit,
        'tanggal'     => $filterTanggal,
        'status'      => $request->status,
    ];

    return view('absensi-relawan.index', compact(
        'absensi',
        'muridOptions',
        'unitOptions',
        'filters'
    ));
}


    // =================================================================
    // CREATE
    // =================================================================
    public function create()
    {
        $profiles = Profile::orderBy('nama')->get();

        // DATA UNIT UNTUK DROPDOWN
        $unitCollection = Unit::orderBy('no_cabang')->get();
        $units = $unitCollection->mapWithKeys(function ($unit) {
            $label = $unit->no_cabang
                ? $unit->no_cabang . ' - ' . $unit->biMBA_unit
                : $unit->biMBA_unit;
            return [$unit->biMBA_unit => $label];
        })->sort();

        $unitNoCabang = $unitCollection->pluck('no_cabang', 'biMBA_unit')->toArray();

        return view('absensi-relawan.create', compact(
            'profiles', 'units', 'unitNoCabang'
        ));
    }

    // =================================================================
    // STORE
    // =================================================================
    public function store(Request $request)
    {
        $request->validate([
            'nama_relawaan'   => 'required|string|max:255',
            'posisi'          => 'required|string|max:100',
            'status_relawaan' => 'required|string|max:50',
            'departemen'      => 'required|string|max:100',
            'bimba_unit'      => 'required|string|max:100',   // BARU
            'no_cabang'       => 'nullable|string|max:20',    // BARU
            'tanggal'         => 'required|date',
            'absensi'         => 'required|string|max:100',
            'keterangan'      => 'nullable|string',
        ]);

            $data = $request->only([
    'nama_relawaan', 'status_relawaan', 'departemen',
    'bimba_unit', 'no_cabang', 'tanggal', 'absensi', 'keterangan'
]);

// Ambil profile berdasarkan nama
$profile = Profile::where('nama', $request->nama_relawaan)->first();

// Ambil NIK
$data['nik'] = $profile?->nik;

// 🔥 Ambil jabatan jadi posisi
$data['posisi'] = $profile?->jabatan;

        // Ambil NIK otomatis
        $profile = Profile::where('nama', $request->nama_relawaan)->first();
        $data['nik'] = $profile?->nik;

        $data['status'] = $this->mapAbsensiToStatus($request->absensi);

        AbsensiRelawan::create($data);

        return redirect()->route('absensi-relawan.index')
            ->with('success', 'Absensi relawan berhasil disimpan!');
    }

    // =================================================================
    // EDIT
    // =================================================================
    public function edit(AbsensiRelawan $absensiRelawan)
    {
        $profiles = Profile::orderBy('nama')->get();

        // DATA UNIT UNTUK DROPDOWN (sama seperti create)
        $unitCollection = Unit::orderBy('no_cabang')->get();
        $units = $unitCollection->mapWithKeys(function ($unit) {
            $label = $unit->no_cabang
                ? $unit->no_cabang . ' - ' . $unit->biMBA_unit
                : $unit->biMBA_unit;
            return [$unit->biMBA_unit => $label];
        })->sort();

        $unitNoCabang = $unitCollection->pluck('no_cabang', 'biMBA_unit')->toArray();

        return view('absensi-relawan.edit', compact(
            'absensiRelawan', 'profiles', 'units', 'unitNoCabang'
        ));
    }

    // =================================================================
    // UPDATE
    // =================================================================
    public function update(Request $request, AbsensiRelawan $absensiRelawan)
    {
        $request->validate([
            'nama_relawaan'   => 'required|string|max:255',
            'posisi'          => 'required|string|max:100',
            'status_relawaan' => 'required|string|max:50',
            'departemen'      => 'required|string|max:100',
            'bimba_unit'      => 'required|string|max:100',
            'no_cabang'       => 'nullable|string|max:20',
            'tanggal'         => 'required|date',
            'absensi'         => 'required|string|max:100',
            'keterangan'      => 'nullable|string',
        ]);

        $data = $request->only([
            'nama_relawaan', 'posisi', 'status_relawaan', 'departemen',
            'bimba_unit', 'no_cabang', 'tanggal', 'absensi', 'keterangan'
        ]);

        $profile = Profile::where('nama', $request->nama_relawaan)->first();

$data['nik'] = $profile?->nik;
$data['posisi'] = $profile?->jabatan;

        $data['status'] = $this->mapAbsensiToStatus($request->absensi);

        $absensiRelawan->update($data);

        return redirect()->route('absensi-relawan.index')
            ->with('success', 'Absensi berhasil diupdate!');
    }

    // =================================================================
    // DESTROY, IMPORT, DLL (tetap sama)
    // =================================================================
    public function destroy(AbsensiRelawan $absensiRelawan)
    {
        $absensiRelawan->delete();
        return redirect()->route('absensi-relawan.index')
            ->with('success', 'Absensi berhasil dihapus!');
    }

    public function importForm()
    {
        return view('absensi-relawan.import');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        $import = new AbsensiRelawanImport;
        Excel::import($import, $request->file('file'));

        return back()->with([
            'success'  => "Import selesai. Berhasil: {$import->getSuccessCount()}, Gagal: " . count($import->failures()),
            'failures' => $import->failures(),
            'errorsEx' => $import->errors(),
        ]);
    }

    private function mapAbsensiToStatus($absensi)
    {
        $absensi = trim(strtolower($absensi));
        $absensi = str_replace(['_', '-'], ' ', $absensi);
        $absensi = preg_replace('/\s+/', ' ', $absensi);

        return match (true) {
            str_contains($absensi, 'sakit') && str_contains($absensi, 'dokter') => 'Sakit',
            str_contains($absensi, 'sakit')                                      => 'Izin',
            str_contains($absensi, 'izin') && str_contains($absensi, 'acc')     => 'Izin',
            str_contains($absensi, 'izin')                                      => 'Alpa',
            str_contains($absensi, 'tidak masuk') || 
            str_contains($absensi, 'tanpa form') ||
            str_contains($absensi, 'tidak mengisi')                             => 'Alpa',
            str_contains($absensi, 'tidak aktif') ||
            str_contains($absensi, 'non aktif') ||
            str_contains($absensi, 'nonaktif')                                  => 'Tidak Aktif',
            str_contains($absensi, 'cuti')                                       => 'Cuti',
            str_contains($absensi, 'datang terlambat')                           => 'DT',
            str_contains($absensi, 'pulang cepat')                               => 'PC',
            default                                                              => 'Hadir',
        };
    }
    public function export(Request $request)
{
    $filters = $request->only([
        'date_from', 'date_to', 'nik', 'bimba_unit', 'status', 'q'
    ]);

    $filename = 'Kehadiran_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

    return Excel::download(
        new AbsensiRelawanExport($filters),  // ← ubah ke RelawanExport
        $filename
    );
}
}