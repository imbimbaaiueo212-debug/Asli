<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AbsensiVolunteer;
use App\Models\AbsensiRelawan;
use App\Models\Profile;
use App\Imports\AbsensiVolunteerImport;
use Carbon\Carbon;
use App\Exports\AbsensiVolunteerExport;

class RelawanAbsenController extends Controller
{
    public function index(Request $request)
{
    $user = Auth::user();

    $profile = $user->profile ?? (object)[
        'nama'       => $user->name ?? 'Relawan',
        'bimba_unit' => 'Unit Tidak Diketahui',
        'no_cabang'  => '000',
    ];

    $perPage     = (int) $request->input('per_page', 50);
    $q           = trim((string) $request->input('q', ''));
    $status      = $request->input('status');
    $dateFrom    = $request->input('date_from');
    $dateTo      = $request->input('date_to');
    $filterNik   = $request->input('nik');
    $filterUnit  = $request->input('bimba_unit');

    // =========================================================
    // DROPDOWN UNIT
    // =========================================================
    $unitQuery = Profile::query();

    if (!$user->is_admin && $user->bimba_unit) {
        $unitQuery->where('profiles.bimba_unit', $user->bimba_unit);
    }

    $unitOptions = $unitQuery
        ->whereNotNull('profiles.bimba_unit')
        ->where('profiles.bimba_unit', '!=', '')
        ->distinct()
        ->orderBy('profiles.bimba_unit')
        ->pluck('profiles.bimba_unit')
        ->values();

    // =========================================================
    // DROPDOWN RELAWAN
    // =========================================================
    $relawanQuery = Profile::select(
            'profiles.nik',
            'profiles.nama',
            'profiles.bimba_unit',
            'profiles.no_cabang'
        )
        ->orderBy('profiles.nik');

    if (!$user->is_admin && $user->bimba_unit) {
        $relawanQuery->where('profiles.bimba_unit', $user->bimba_unit);
    }

    if ($filterUnit) {
        $relawanQuery->where('profiles.bimba_unit', $filterUnit);
    }

    $relawanOptions = $relawanQuery->get();

    // =========================================================
    // QUERY UTAMA (JOIN + ALIAS)
    // =========================================================
    $query = AbsensiVolunteer::query()
    ->from('absensi_volunteer')
    ->leftJoin('profiles', 'absensi_volunteer.nik', '=', 'profiles.nik')
    ->select(
        'absensi_volunteer.*',
        'profiles.jabatan as jabatan_profile',
        'profiles.departemen as departemen_profile' // ✅ TAMBAHKAN INI
    );

    // =========================================================
    // FILTER
    // =========================================================

    // Filter NIK / Search
    if ($filterNik) {
        $query->where('absensi_volunteer.nik', $filterNik);
    } elseif ($q !== '') {
        $query->where(function ($w) use ($q) {
            $w->where('absensi_volunteer.nama_relawan', 'like', "%{$q}%")
              ->orWhere('absensi_volunteer.nik', 'like', "%{$q}%");
        });
    }

    // Filter UNIT (WAJIB pakai prefix)
    if ($filterUnit) {
        $query->where('absensi_volunteer.bimba_unit', $filterUnit);
    }

    // Filter berdasarkan user (non admin)
    if (!$user->is_admin && $user->bimba_unit) {
        $query->where('absensi_volunteer.bimba_unit', $user->bimba_unit);
    }

    // Filter STATUS
    if ($status) {
        $query->where('absensi_volunteer.status', $status);
    }

    // Filter TANGGAL
    if ($dateFrom && $dateTo) {
        $query->whereBetween('absensi_volunteer.tanggal', [$dateFrom, $dateTo]);
    } elseif ($dateFrom) {
        $query->where('absensi_volunteer.tanggal', '>=', $dateFrom);
    } elseif ($dateTo) {
        $query->where('absensi_volunteer.tanggal', '<=', $dateTo);
    }

    // =========================================================
    // PAGINATION
    // =========================================================
    $absensi = $query->orderByDesc('absensi_volunteer.tanggal')
        ->paginate($perPage)
        ->appends($request->query());

    // =========================================================
    // FILTER STATE
    // =========================================================
    $filters = [
        'q'          => $q,
        'status'     => $status,
        'date_from'  => $dateFrom,
        'date_to'    => $dateTo,
        'per_page'   => $perPage,
        'nik'        => $filterNik,
        'bimba_unit' => $filterUnit,
    ];

    return view('relawan.index', compact(
        'absensi',
        'relawanOptions',
        'unitOptions',
        'profile',
        'filters'
    ));
}


    public function store(Request $request)
{
    $user = Auth::user();

    // Absen semua (hanya admin)
    if ($request->has('absen_all') && $user->is_admin) {
        $tanggal = $request->tanggal ?? today()->toDateString();

        foreach (Profile::all() as $r) {
            if (AbsensiVolunteer::where('nik', $r->nik)->where('tanggal', $tanggal)->exists()) {
                continue;
            }

            AbsensiVolunteer::create([
                'nik'           => $r->nik,
                'nama_relawan'  => $r->nama,
                'posisi'        => $r->posisi ?? 'Relawan',
                'bimba_unit'    => $r->bimba_unit ?? '-',
                'no_cabang'     => $r->no_cabang ?? '000',
                'tanggal'       => $tanggal,
                'jam_masuk'     => now()->format('H:i'),
                'status'        => 'Hadir',
                'jam_lembur'    => $request->jam_lembur ?? 0,
                'onduty'        => '08:00',
                'offduty'       => '16:00',
                'alasan'        => $request->alasan,               // <-- tambahkan ini
            ]);
        }

        return back()->with('success', 'Absen semua relawan berhasil!');
    }

    // Absen satu orang
    $request->validate([
        'nik'        => 'required|exists:profiles,nik',
        'tanggal'    => 'required|date',
        'kehadiran'  => 'required|string',
        'jam_lembur' => 'nullable|integer|min:0',
    ]);

    $profile = Profile::where('nik', $request->nik)->firstOrFail();

    // Keamanan: relawan biasa hanya boleh absen unitnya sendiri
    if (!$user->is_admin && $profile->bimba_unit !== $user->bimba_unit) {
        return back()->with('error', 'Kamu tidak bisa absen untuk unit lain!');
    }

    if (AbsensiVolunteer::where('nik', $request->nik)->where('tanggal', $request->tanggal)->exists()) {
        return back()->with('error', 'Sudah absen di tanggal ini.');
    }

    // Mapping status dari form ke status pendek
    $kehadiranInput = $request->kehadiran;
    $statusPendek   = $this->mapToShortStatus($kehadiranInput);

    // Tentukan jam_keluar otomatis jika status BUKAN yang butuh absen pulang
    $statusButuhPulang = ['Hadir', 'DT', 'PC'];
    $jamKeluar = null;

    if (!in_array($statusPendek, $statusButuhPulang)) {
        $jamKeluar = now()->format('H:i');  // otomatis absen pulang
    }

    // Simpan ke AbsensiVolunteer
    $absen = AbsensiVolunteer::create([
        'nik'           => $profile->nik,
        'nama_relawan'  => $profile->nama,
        'posisi'        => $profile->posisi ?? 'Relawan',
        'bimba_unit'    => $profile->bimba_unit ?? '-',
        'no_cabang'     => $profile->no_cabang ?? '000',
        'tanggal'       => $request->tanggal,
        'jam_masuk'     => now()->format('H:i'),
        'jam_keluar'    => $jamKeluar,                  // ← otomatis jika sakit/izin/alpa/dll
        'status'        => $statusPendek,
        'jam_lembur'    => $request->jam_lembur ?? 0,
        'onduty'        => '08:00',
        'offduty'       => '16:00',
        'keterangan'    => $kehadiranInput,             // simpan teks lengkap
        'alasan'        => $request->alasan,
    ]);

    // Sync ke AbsensiRelawan (khusus jika bukan Hadir)
    if ($statusPendek !== 'Hadir') {
        AbsensiRelawan::updateOrCreate(
            [
                'nik'     => $absen->nik,
                'tanggal' => $absen->tanggal,
            ],
            [
                'nama_relawaan'   => $absen->nama_relawan,
                'posisi'          => $absen->posisi ?? 'Relawan',
                'departemen' => $profile->departemen,
                'bimba_unit'      => $absen->bimba_unit,
                'no_cabang'       => $absen->no_cabang,
                'tanggal'         => $absen->tanggal,
                'absensi'         => $kehadiranInput,           // teks panjang (Sakit Dengan Keterangan Dokter, dll)
                'status_relawaan' => $profile?->status_karyawan ?? 'Aktif',
                'status'          => $statusPendek,             // pendek: Sakit, Izin, Alpa, dll
                'keterangan'      => $absen->keterangan,
                'alasan'          => $request->alasan,
            ]
        );
    }

    return back()->with('success', 'Absen berhasil disimpan!');
}

    public function updateStatus(Request $request, $id)
{
    $request->validate(['status' => 'required|string|max:50']);

    $absen = AbsensiVolunteer::findOrFail($id);
    $user = Auth::user();

    // Cek hak akses
    if (!$user->is_admin && $absen->bimba_unit !== $user->bimba_unit) {
        return back()->with('error', 'Kamu hanya bisa ubah data unitmu sendiri!');
    }

    $newStatus = $request->status;

    // Status yang MASIH MEMBUTUHKAN absen pulang manual
    $statusButuhPulang = ['Hadir', 'DT', 'PC'];

    // Status yang LANGSUNG SELESAI (otomatis isi jam_keluar)
    $statusSelesai = [
        'Sakit', 'Izin', 'Alpa', 'Cuti', 'Tidak Aktif',
        'Minggu', 'Libur Nasional', /* tambahkan status lain jika ada */
    ];

    $updateData = ['status' => $newStatus];

    // Jika status termasuk yang langsung selesai → isi jam_keluar otomatis
    if (in_array($newStatus, $statusSelesai)) {
        if (is_null($absen->jam_keluar)) {
            $updateData['jam_keluar'] = now()->format('H:i');
            // Opsional: tambah catatan otomatis
            // $updateData['keterangan'] = trim(($absen->keterangan ?? '') . ' | Otomatis selesai karena ' . $newStatus);
        }
    }
    // Jika kembali ke status yang butuh absen pulang → bisa reset jam_keluar (opsional)
    elseif (in_array($newStatus, $statusButuhPulang) && !is_null($absen->jam_keluar)) {
        // $updateData['jam_keluar'] = null;   // uncomment jika ingin reset
    }

    $absen->update($updateData);

    return back()->with('success', 'Status berhasil diperbarui.');
}

    public function absenPulang($id)
    {
        $absen = AbsensiVolunteer::findOrFail($id);
        $user = Auth::user();

        if (!$user->is_admin && $absen->bimba_unit !== $user->bimba_unit) {
            return response()->json(['error' => 'Tidak diizinkan!'], 403);
        }

        if (!$absen->jam_masuk) {
            return response()->json(['error' => 'Belum absen masuk!'], 400);
        }

        if ($absen->jam_keluar) {
            return response()->json(['error' => 'Sudah absen pulang!'], 400);
        }

        $absen->jam_keluar = now()->format('H:i');
        $absen->save();

        return response()->json(['success' => true, 'jam_keluar' => $absen->jam_keluar]);
    }

    public function edit($id)
    {
        $absen = AbsensiVolunteer::findOrFail($id);
        $user = Auth::user();

        if (!$user->is_admin && $absen->bimba_unit !== $user->bimba_unit) {
            abort(403, 'Akses ditolak');
        }

        $relawan = Profile::where('bimba_unit', $absen->bimba_unit)
            ->orderBy('nama')
            ->get(['nik', 'nama', 'bimba_unit', 'no_cabang']);

        return view('relawan.edit', compact('absen', 'relawan'));
    }

   public function update(Request $request, $id)
{
    $absen = AbsensiVolunteer::findOrFail($id);

    // Cek hak akses
    if (!Auth::user()->is_admin && $absen->bimba_unit !== Auth::user()->bimba_unit) {
        abort(403, 'Anda tidak berhak mengedit absensi unit lain.');
    }

    // Validasi input
    $request->validate([
        'tanggal'     => 'required|date',
        'status'      => 'required|string',
        'jam_masuk'   => 'nullable|date_format:H:i',
        'jam_keluar'  => 'nullable|date_format:H:i',
        'jam_lembur'  => 'nullable|integer|min:0',
        'keterangan'  => 'nullable|string|max:500',
        'alasan'      => 'nullable|string|max:1000', // boleh kosong
    ]);

    // Mapping status dari form ke status pendek (sesuai fungsi mapToShortStatus Anda)
    $newStatus     = $request->status;
    $statusSistem  = $this->mapToShortStatus($newStatus);

    // Tentukan apakah status ini membutuhkan alasan
    $statusButuhAlasan = $statusSistem !== 'Hadir';

    // Validasi kondisional: alasan wajib jika status bukan Hadir
    if ($statusButuhAlasan && !$request->filled('alasan')) {
        return back()
            ->withInput()
            ->withErrors(['alasan' => 'Alasan wajib diisi untuk status selain "Hadir".']);
    }

    // Data yang akan di-update ke AbsensiVolunteer
    $updateData = [
        'tanggal'     => $request->tanggal,
        'status'      => $statusSistem,
        'jam_masuk'   => $request->jam_masuk,
        'jam_keluar'  => $request->jam_keluar,
        'jam_lembur'  => $request->jam_lembur,
        'keterangan'  => $request->keterangan,
        'alasan'      => $request->alasan,          // ← TAMBAHKAN INI
    ];

    // --- LOGIKA OTOMATIS JAM KELUAR ---
    $statusSelesai = ['Sakit', 'Izin', 'Alpa', 'Cuti', 'Tidak Aktif', 'Minggu', 'Libur Nasional'];

    if (in_array($statusSistem, $statusSelesai)) {
        if (is_null($updateData['jam_keluar'])) {
            $updateData['jam_keluar'] = now()->format('H:i');
        }
    }

    // Update record AbsensiVolunteer
    $absen->update($updateData);

    // === SYNC KE AbsensiRelawan ===
    if ($absen->nik && $statusSistem !== 'Hadir') {
        $profile = Profile::where('nik', $absen->nik)->firstOrFail();

        AbsensiRelawan::updateOrCreate(
            [
                'nik'     => $absen->nik,
                'tanggal' => $absen->tanggal,
            ],
            [
                'nama_relawaan'   => $absen->nama_relawan,
                'posisi'          => $absen->posisi ?? 'Relawan',
                'departemen' => $profile->departemen,
                'bimba_unit'      => $absen->bimba_unit,
                'no_cabang'       => $absen->no_cabang,
                'tanggal'         => $absen->tanggal,
                'absensi'         => $newStatus,               // teks panjang dari form
                'status_relawaan' => $profile?->status_karyawan ?? 'Aktif',
                'status'          => $statusSistem,            // pendek
                'keterangan'      => $absen->keterangan,
                'alasan'          => $absen->alasan,           // ← TAMBAHKAN INI (dari AbsensiVolunteer)
            ]
        );
    } else {
        // Hapus sync jika status jadi Hadir
        AbsensiRelawan::where('nik', $absen->nik)
            ->where('tanggal', $absen->tanggal)
            ->delete();
    }

    return redirect()->route('relawan.index')
        ->with('success', 'Absensi berhasil diperbarui & tersinkron ke potongan tunjangan!');
}

    // MAPPING: status pendek → teks panjang (untuk kolom absensi di absensi_relawan)
    private function mapToShortStatus($status)
{
    return match (true) {
        str_contains($status, 'Sakit Dengan Keterangan Dokter')        => 'Sakit',
        str_contains($status, 'Izin Dengan Form di ACC')               => 'Izin',
        str_contains($status, 'Izin Tanpa Form di ACC')                => 'Izin',
        str_contains($status, 'Sakit Tanpa Keterangan Dokter')         => 'Izin',
        str_contains($status, 'Tidak Mengisi')                         => 'Alpa',
        str_contains($status, 'Tidak Masuk Tanpa Form')                => 'Alpa',
        str_contains($status, 'Tidak Aktif')                           => 'Tidak Aktif',
        str_contains($status, 'Cuti')                                  => 'Cuti',
        str_contains($status, 'Datang Terlambat')                      => 'DT',
        str_contains($status, 'Pulang Cepat')                          => 'PC',
        str_contains($status, 'Hari Minggu')                           => 'Minggu',
        str_contains($status, 'Libur Nasional')                        => 'Libur Nasional',
        default                                                                          => 'Hadir',
    };
}

    public function destroy($id)
    {
        $absen = AbsensiVolunteer::findOrFail($id);

        if (!Auth::user()->is_admin && $absen->bimba_unit !== Auth::user()->bimba_unit) {
            abort(403);
        }

        // Hapus dari AbsensiRelawan dulu
        if ($absen->nik) {
            AbsensiRelawan::where('nik', $absen->nik)
                ->where('tanggal', $absen->tanggal)
                ->delete();
        }

        $absen->delete();

        return back()->with('success', 'Data berhasil dihapus dari kedua tabel!');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new AbsensiVolunteerImport, $request->file('file'));

        return back()->with('success', 'Import berhasil! Data unitmu sudah muncul.');
    }
    public function export(Request $request)
{
    $filters = $request->only([
        'q', 'status', 'date_from', 'date_to', 'nik', 'bimba_unit'
    ]);

    $filename = 'Absen_Relawan_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

    return Excel::download(
        new AbsensiVolunteerExport($filters),
        $filename
    );
}
}
