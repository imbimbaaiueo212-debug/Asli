<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PendapatanTunjangan;
use App\Models\Profile;
use App\Models\Penerimaan;
use App\Models\Skim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PendapatanTunjanganController extends Controller
{
    private function bulanList(): array
    {
        return [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
    }

    /**
     * Helper: Ambil nilai SKIM berdasarkan jabatan & masa kerja (dalam bulan)
     */
    private function hitungSkim(Profile $profile): float
{
    if (!$profile->jabatan || !$profile->status_karyawan) {
        return 0.0;
    }

    $jabatanLower = strtolower(trim($profile->jabatan));
    $statusLower  = strtolower(trim($profile->status_karyawan));

    // Masa kerja
    $masaKerjaBulan = 0;
    if (is_numeric($profile->masa_kerja)) {
        $masaKerjaBulan = (int) $profile->masa_kerja;
    } else {
        $mk = strtolower(trim($profile->masa_kerja ?? ''));
        preg_match('/(\d+)\s*tahun/', $mk, $tahun);
        preg_match('/(\d+)\s*bulan/', $mk, $bulan);
        if (!empty($tahun[1])) $masaKerjaBulan += (int)$tahun[1] * 12;
        if (!empty($bulan[1])) $masaKerjaBulan += (int)$bulan[1];
    }

    $kategoriLike = $masaKerjaBulan < 24 ? '%< 24 Bulan%' : '%>= 24 Bulan%';

    // Query yang sangat toleran terhadap spasi dan huruf
    $skim = Skim::where(function ($q) use ($jabatanLower) {
            $q->whereRaw('LOWER(jabatan) LIKE ?', ["%$jabatanLower%"])
              ->orWhereRaw('LOWER(jabatan) = ?', [$jabatanLower]);
        })
        ->where(function ($q) use ($statusLower) {
            $q->whereRaw('LOWER(status) = ?', [$statusLower])
              ->orWhereRaw('LOWER(status) LIKE ?', ["%$statusLower%"]);
        })
        ->whereRaw('LOWER(masa_kerja) LIKE ?', [$kategoriLike])
        ->first();

    // Khusus guru: force cari jika ada kata 'guru'
    if (!$skim && str_contains($jabatanLower, 'guru')) {
        $skim = Skim::whereRaw('LOWER(jabatan) LIKE ?', ['%guru%'])
                     ->whereRaw('LOWER(status) LIKE ?', ['%magang%'])
                     ->whereRaw('LOWER(masa_kerja) LIKE ?', ['%< 24 bulan%'])
                     ->first();
    }

    if ($skim) {
        return (float) $skim->thp;
    }

    // Log untuk debug (hapus setelah OK)
    \Illuminate\Support\Facades\Log::warning(
        "SKIM 0 - {$profile->nama} | Jabatan: '{$profile->jabatan}' | Status: '{$profile->status_karyawan}' | Masa Kerja: $masaKerjaBulan bulan"
    );

    return 0.0;
}

    /**
     * Mendapatkan data pendapatan tunjangan untuk satu karyawan di bulan tertentu
     * (hitung dinamis jika belum ada penyesuaian manual)
     */
    private function getPendapatanForProfileAndMonth(Profile $profile, string $bulan): array
    {
        $record = PendapatanTunjangan::where('nik', $profile->nik)
            ->where('bulan', $bulan)
            ->first();

        $skim = $this->hitungSkim($profile);

        if ($record) {
            // Ada record → gunakan nilai yang sudah disimpan (sudah ada penyesuaian)
            $data = $record->toArray();
            $data['thp'] = $skim; // selalu update skim terbaru
            $data['total'] = $skim + $record->kerajinan + $record->english + $record->mentor +
                             $record->kekurangan + $record->tj_keluarga + $record->lain_lain;
            $data['is_calculated'] = false;
            return $data;
        }

        // Tidak ada penyesuaian manual → hitung default
        return [
            'id'               => null,
            'nik'              => $profile->nik,
            'nama'             => $profile->nama,
            'jabatan'          => $profile->jabatan,
            'status'           => $profile->status_karyawan,
            'departemen'       => $profile->departemen,
            'bimba_unit'       => $profile->bimba_unit ?? null,
            'no_cabang'        => $profile->no_cabang ?? null,
            'masa_kerja'       => $profile->masa_kerja,
            'bulan'            => $bulan,
            'thp'              => $skim,
            'kerajinan'        => 0.0,
            'english'          => 0.0,
            'mentor'           => 0.0,
            'kekurangan'       => 0.0,
            'bulan_kekurangan' => null,
            'tj_keluarga'      => 0.0,
            'lain_lain'        => 0.0,
            'total'            => $skim,
            'is_calculated'    => true,   // tanda bahwa ini dihitung otomatis
            'created_at'       => null,
            'updated_at'       => null,
        ];
    }

    public function index(Request $request)
{
    $bulan = $request->input('bulan') ?: now()->format('Y-m');
    $search = $request->input('search');

    $profilesQuery = Profile::query();

    // Exclude yang sudah keluar
    $profilesQuery->whereNotIn('status_karyawan', [
        'Resign', 'Keluar', 'Pensiun', 'Non Aktif'
    ]);

    // Hindari data dummy lama
    $profilesQuery->where(function ($q) {
        $q->whereNull('tgl_masuk')
          ->orWhere('tgl_masuk', '>=', '2010-01-01');
    });

    // ================== FILTER JABATAN YANG DIPERBOLEHKAN ==================
    $profilesQuery->where(function ($q) {

        // Semua Kepala Unit / Kepala Sekolah
        $q->whereIn('jabatan', [
            'Kepala Unit',
            'Kepala biMBA',
            'Kepala Sekolah'
        ])

        // Guru (semua jenis)
        ->orWhereIn('jabatan', [
            'Guru',
            'Guru Trial',
            'Guru biMBA',
            'Pengajar',
            'Tutor'
        ])

        // Staff pendukung (sesuaikan dengan kebutuhan)
        ->orWhereIn('jabatan', [
            'Staff',
            'Admin',
            'Koordinator',
            'Finance',
            'HRD',
            // tambahkan jabatan lain jika perlu
        ]);
    });

    // Search
    if ($search) {
        $profilesQuery->where(function ($q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nik', 'like', "%{$search}%");
        });
    }

    $profiles = $profilesQuery
        ->orderBy('nama')
        ->get();

    // ================== Proses Pendapatan ==================
    $pendapatans = [];

    foreach ($profiles as $profile) {
        $data = $this->getPendapatanForProfileAndMonth($profile, $bulan);

        // Auto create jika belum ada
        if (($data['is_calculated'] ?? false) && empty($data['id'])) {
            $saved = PendapatanTunjangan::create([
                'nik'              => $profile->nik,
                'nama'             => $profile->nama,
                'jabatan'          => $profile->jabatan,
                'status'           => $profile->status_karyawan,
                'departemen'       => $profile->departemen,
                'bimba_unit'       => $profile->bimba_unit,
                'no_cabang'        => $profile->no_cabang,
                'masa_kerja'       => $profile->masa_kerja,
                'bulan'            => $bulan,
                'thp'              => $data['thp'],
                'kerajinan'        => 0,
                'english'          => 0,
                'mentor'           => 0,
                'kekurangan'       => 0,
                'bulan_kekurangan' => null,
                'tj_keluarga'      => 0,
                'lain_lain'        => 0,
                'total'            => $data['thp'],
            ]);

            $data = $saved->toArray();
        }

        $pendapatans[] = $data;
    }

    // Dropdown bulan
    $allMonths = PendapatanTunjangan::select('bulan')
        ->distinct()
        ->orderBy('bulan', 'desc')
        ->pluck('bulan')
        ->map(fn($m) => substr($m, 0, 7))
        ->unique()
        ->values();

    if (!$allMonths->contains($bulan)) {
        $allMonths->push($bulan);
    }

    $allMonths = $allMonths->sortDesc();

    return view('pendapatan-tunjangan.index', compact(
        'pendapatans', 'allMonths', 'bulan', 'search'
    ));
}
public function create()
{
    $profiles = Profile::orderBy('nama')->get();

    return view('pendapatan-tunjangan.create', compact('profiles'));
}
public function store(Request $request)
{
    $request->validate([
        'nik' => 'required',
        'nama' => 'required',
        'bulan' => 'required',
    ]);

    $profile = Profile::where('nik', $request->nik)->first();

    if (!$profile) {
        return back()->with('error', 'Profile tidak ditemukan');
    }

    $skim = $this->hitungSkim($profile);

    $kerajinan = $request->kerajinan ?? 0;
    $english = $request->english ?? 0;
    $mentor = $request->mentor ?? 0;
    $kekurangan = $request->kekurangan ?? 0;
    $tj_keluarga = $request->tj_keluarga ?? 0;
    $lain_lain = $request->lain_lain ?? 0;

    $total =
        $skim +
        $kerajinan +
        $english +
        $mentor +
        $kekurangan +
        $tj_keluarga +
        $lain_lain;

    PendapatanTunjangan::create([
        'nik' => $profile->nik,
        'nama' => $profile->nama,
        'jabatan' => $profile->jabatan,
        'status' => $profile->status_karyawan,
        'departemen' => $profile->departemen,
        'bimba_unit' => $profile->bimba_unit,
        'no_cabang' => $profile->no_cabang,
        'masa_kerja' => $profile->masa_kerja,

        'bulan' => $request->bulan,

        'thp' => $skim,
        'kerajinan' => $kerajinan,
        'english' => $english,
        'mentor' => $mentor,
        'kekurangan' => $kekurangan,
        'bulan_kekurangan' => $request->bulan_kekurangan,
        'tj_keluarga' => $tj_keluarga,
        'lain_lain' => $lain_lain,

        'total' => $total,
    ]);

    return redirect()
        ->route('pendapatan-tunjangan.index')
        ->with('success', 'Data berhasil ditambahkan');
}
    public function created(Profile $profile)
{
    // Lewati jika status sudah tidak aktif
    $statusLower = strtolower(trim($profile->status_karyawan ?? ''));
    if (in_array($statusLower, ['resign', 'keluar', 'pensiun', 'non aktif'])) {
        return;
    }

    // Opsional: hanya buat untuk jabatan tertentu (sesuaikan dengan logikamu)
    $jabatanLower = strtolower(trim($profile->jabatan ?? ''));
    $isEligible = str_contains($jabatanLower, 'guru') ||
                  str_contains($jabatanLower, 'pengajar') ||
                  str_contains($jabatanLower, 'tutor') ||
                  str_contains($jabatanLower, 'kepala') ||
                  in_array($jabatanLower, ['staff', 'admin', 'koordinator']); // tambah sesuai kebutuhan

    if (!$isEligible) {
        return;
    }

    $bulanSekarang = now()->format('Y-m');

    // Cek apakah sudah ada → hindari duplikat
    $exists = PendapatanTunjangan::where('nik', $profile->nik)
        ->where('bulan', $bulanSekarang)
        ->orWhere(function ($q) use ($profile, $bulanSekarang) {
            $q->where('nama', $profile->nama)
              ->where('bulan', $bulanSekarang);
        })
        ->exists();

    if ($exists) {
        return;
    }

    try {
        // Ambil THP dari service yang sudah kamu pakai
        $thp = SkimTHPService::getTHP(
            $profile->jabatan ?? '',
            $profile->status_karyawan ?? '',
            $profile->masa_kerja ?? 0
        );

        PendapatanTunjangan::create([
            'nik'              => $profile->nik,
            'nama'             => $profile->nama,
            'jabatan'          => $profile->jabatan,
            'status'           => $profile->status_karyawan,
            'departemen'       => $profile->departemen,
            'bimba_unit'       => $profile->bimba_unit ?? null,
            'no_cabang'        => $profile->no_cabang ?? null,
            'masa_kerja'       => $profile->masa_kerja ?? 0,
            'bulan'            => $bulanSekarang,
            'thp'              => $thp,
            'total'            => $thp,           // awalnya hanya THP
            // field tunjangan lain default 0 atau null
            'kerajinan'        => 0,
            'english'          => 0,
            'mentor'           => 0,
            'kekurangan'       => 0,
            'tj_keluarga'      => 0,
            'lain_lain'        => 0,
            'bulan_kekurangan' => null,
        ]);

        \Log::info("Auto-created PendapatanTunjangan untuk profile baru", [
            'profile_id' => $profile->id,
            'nama'       => $profile->nama,
            'nik'        => $profile->nik,
            'bulan'      => $bulanSekarang,
            'thp'        => $thp,
        ]);

    } catch (\Exception $e) {
        \Log::error("Gagal auto-create PendapatanTunjangan", [
            'profile_id' => $profile->id,
            'nama'       => $profile->nama,
            'error'      => $e->getMessage(),
        ]);
    }

    // Panggil sync yang sudah ada
    $this->syncImbalanRekap($profile);
}

    // edit, update, destroy tetap mirip, tapi update juga pakai updateOrCreate logic jika perlu

    public function edit($id)
    {
        $pendapatan = PendapatanTunjangan::findOrFail($id);
        $profiles = Profile::orderBy('nama')->get();
        return view('pendapatan-tunjangan.edit', compact('pendapatan', 'profiles'));
    }

    public function update(Request $request, $id)
    {
        $pendapatan = PendapatanTunjangan::findOrFail($id);

        // Validasi hampir sama dengan store
        $validator = Validator::make($request->all(), [
            'kerajinan'         => 'nullable|numeric|min:0',
            'english'           => 'nullable|numeric|min:0',
            'mentor'            => 'nullable|numeric|min:0',
            'kekurangan'        => 'nullable|numeric|min:0',
            'tj_keluarga'       => 'nullable|numeric|min:0',
            'lain_lain'         => 'nullable|numeric|min:0',
            'bulan_kekurangan'  => 'nullable|string',
        ]);

        // after validation sama seperti store...

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $profile = $pendapatan->profile ?? Profile::where('nik', $pendapatan->nik)->first();
        $skim = $profile ? $this->hitungSkim($profile) : $pendapatan->thp;

        $data = $request->only([
            'kerajinan', 'english', 'mentor', 'kekurangan',
            'tj_keluarga', 'lain_lain', 'bulan_kekurangan'
        ]);

        $data['thp'] = $skim;
        $data['total'] = $skim +
            floatval($data['kerajinan'] ?? $pendapatan->kerajinan) +
            floatval($data['english'] ?? $pendapatan->english) +
            floatval($data['mentor'] ?? $pendapatan->mentor) +
            floatval($data['kekurangan'] ?? $pendapatan->kekurangan) +
            floatval($data['tj_keluarga'] ?? $pendapatan->tj_keluarga) +
            floatval($data['lain_lain'] ?? $pendapatan->lain_lain);

        $pendapatan->update($data);

        return redirect()->route('pendapatan-tunjangan.index')
            ->with('success', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        PendapatanTunjangan::findOrFail($id)->delete();
        return redirect()->route('pendapatan-tunjangan.index')
            ->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Opsional: tombol untuk memaksa generate record default untuk semua karyawan di bulan tertentu
     * (bisa dipakai sekali saat migrasi atau untuk backfill)
     */
    public function forceGenerateMonth(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));

        $profiles = Profile::whereNotIn('status_karyawan', ['Resign', 'Keluar'])->get();

        DB::transaction(function () use ($profiles, $bulan) {
            foreach ($profiles as $profile) {
                $exists = PendapatanTunjangan::where('nik', $profile->nik)
                    ->where('bulan', $bulan)
                    ->exists();

                if (!$exists) {
                    $skim = $this->hitungSkim($profile);

                    PendapatanTunjangan::create([
                        'nik'              => $profile->nik,
                        'nama'             => $profile->nama,
                        'jabatan'          => $profile->jabatan,
                        'status'           => $profile->status_karyawan,
                        'departemen'       => $profile->departemen,
                        'bimba_unit'       => $profile->bimba_unit,
                        'no_cabang'        => $profile->no_cabang,
                        'masa_kerja'       => $profile->masa_kerja,
                        'thp'              => $skim,
                        'total'            => $skim,
                        'bulan'            => $bulan,
                        // field lain default 0 atau null
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', "Data bulan {$bulan} berhasil digenerate untuk semua karyawan aktif!");
    }

    // Ajax untuk preview THP saat create/edit
    public function ajaxGetSkimFromProfile(Profile $profile)
    {
        return response()->json([
            'thp' => $this->hitungSkim($profile),
            'nama' => $profile->nama,
        ]);
    }
    private function formatMasaKerja($masaKerja): string
{
    $mk = (int) ($masaKerja ?? 0);
    
    $years  = floor($mk / 12);
    $months = $mk % 12;

    $yearText  = $years > 0 ? $years . ' tahun ' : '0 tahun ';
    $monthText = $months > 0 ? $months . ' bulan' : '0 bulan';

    return $yearText . $monthText;
}
public function generateBulanBaru(Request $request)
{
    // Panggil method yang sudah ada
    return $this->forceGenerateMonth($request);
}
/**
 * Backfill / generate otomatis data pendapatan tunjangan dari tanggal penerimaan guru/kepala unit
 * Jalankan sekali untuk isi data historis (misal dari 2024-04 sampai sekarang)
 */
public function backfillFromPenerimaan(Request $request)
{
    // Ambil semua guru/kepala unit unik dari penerimaan
    $gurus = Penerimaan::whereNotNull('guru')
        ->where('guru', '!=', '')
        ->select('guru', 'bimba_unit', 'no_cabang')
        ->distinct()
        ->get();

    $now = Carbon::now();
    $generatedCount = 0;

    foreach ($gurus as $guru) {
        // Cari tanggal penerimaan pertama untuk guru ini (bulan mulai)
        $firstPenerimaan = Penerimaan::where('guru', $guru->guru)
            ->orderBy('tanggal', 'asc')
            ->first();

        if (!$firstPenerimaan || !$firstPenerimaan->tanggal) {
            continue;
        }

        $startDate = Carbon::parse($firstPenerimaan->tanggal)->startOfMonth();
        $endDate   = $now->startOfMonth();

        // Loop dari bulan mulai sampai sekarang
        while ($startDate->lte($endDate)) {
            $bulanStr = $startDate->format('Y-m');

            // Cek apakah sudah ada record
            $exists = PendapatanTunjangan::where('nama', $guru->guru)
                ->where('bulan', $bulanStr)
                ->exists();

            if (!$exists) {
                // Cari profile guru (untuk skim & data lain)
                $profile = Profile::where('nama', $guru->guru)->first();

                if (!$profile) {
                    continue; // skip jika profile tidak ditemukan
                }

                $skim = $this->hitungSkim($profile);

                PendapatanTunjangan::create([
                    'nik'              => $profile->nik ?? null,
                    'nama'             => $profile->nama,
                    'jabatan'          => $profile->jabatan,
                    'status'           => $profile->status_karyawan,
                    'departemen'       => $profile->departemen,
                    'bimba_unit'       => $guru->bimba_unit,
                    'no_cabang'        => $guru->no_cabang,
                    'masa_kerja'       => $profile->masa_kerja ?? 0,
                    'thp'              => $skim,
                    'kerajinan'        => 0,
                    'english'          => 0,
                    'mentor'           => 0,
                    'kekurangan'       => 0,
                    'bulan_kekurangan' => null,
                    'tj_keluarga'      => 0,
                    'lain_lain'        => 0,
                    'total'            => $skim,
                    'bulan'            => $bulanStr,
                ]);

                $generatedCount++;
            }

            $startDate->addMonth();
        }
    }

    return redirect()->back()->with('success', "Berhasil generate $generatedCount data pendapatan tunjangan dari penerimaan guru/kepala unit!");
}

/**
 * Hapus semua data pendapatan tunjangan sebelum tahun 2024
 */
public function hapusDataSebelum2024(Request $request)
{
    // Konfirmasi via password atau token kalau mau lebih aman
    // Misal: if ($request->input('confirm') !== 'hapus2023') { return redirect()->back()->with('error', 'Konfirmasi salah'); }

    $deleted = PendapatanTunjangan::where('bulan', '<', '2024-04')->delete();

    return redirect()->route('pendapatan-tunjangan.index')
        ->with('success', "Berhasil menghapus $deleted data sebelum tahun 2024!");
}
}