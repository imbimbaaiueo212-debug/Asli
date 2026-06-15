<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekapProgresif;
use App\Models\Profile;
use App\Models\BukuInduk;
use App\Models\Unit;
use App\Models\Penerimaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class SlipPembayaranProgresifController extends Controller
{
    private const DEFAULT_MAX_ROWS = 10;
    private const DEFAULT_REKAP_VALUES = [
        'murid_aktif_am1' => 0, 'murid_aktif_am2' => 0,
        'murid_garansi' => 0, 'murid_dhuafa' => 0, 'murid_bnf1' => 0, 'murid_bnf2' => 0,
        'murid_baru_bimba' => 0, 'murid_trial_bimba' => 0,
        'murid_baru_english' => 0, 'murid_trial_english' => 0,
        'total_fm' => 0, 'progresif' => 0, 'komisi' => 0,
        'komisi_mb_bimba' => 0, 'komisi_mt_bimba' => 0,
        'komisi_mb_english' => 0, 'komisi_mt_english' => 0,
        'komisi_asisten' => 0, 'spp_bimba' => 0, 'spp_english' => 0,
        'kurang' => 0, 'lebih' => 0, 'dibayarkan' => 0,
    ];

    public function index(Request $request)
{
    Carbon::setLocale('id');

    // Ambil filter dari request
    $selectedUnit  = $request->get('unit');
    $selectedNama  = $request->get('nama');
    $selectedBulan = $request->get('bulan');
    $selectedTahun = $request->get('tahun', date('Y'));
    $statusFilter  = $request->get('status');

    // ====================== DAFTAR UNIT ======================
    $unitList = RekapProgresif::whereNotNull('bimba_unit')
        ->distinct()
        ->orderBy('bimba_unit')
        ->pluck('bimba_unit')
        ->toArray();

    // ====================== DAFTAR NAMA DARI PROFILE SAJA ======================
    $namaListQuery = Profile::query()
        ->where(function ($q) {
            $q->whereIn('status_karyawan', ['Aktif', 'Magang'])
              ->orWhereNull('status_karyawan');
        });

    // Filter Unit (lebih robust)
    if (!empty($selectedUnit)) {
        $namaListQuery->where(function($q) use ($selectedUnit) {
            $q->where('bimba_unit', '=', $selectedUnit)
              ->orWhere('biMBA_unit', '=', $selectedUnit)
              ->orWhereRaw('TRIM(bimba_unit) = ?', [$selectedUnit])
              ->orWhereRaw('TRIM(biMBA_unit) = ?', [$selectedUnit]);
        });
    }

    $namaList = $namaListQuery
        ->orderBy('bimba_unit')
        ->orderBy('nama')
        ->pluck('nama')
        ->unique()
        ->values()
        ->toArray();

    // ====================== AMBIL DATA REKAP & PROFILE ======================
    $rekap = null;
    $profile = null;
    $unit = null;
    $muridList = [];
    $maxRows = self::DEFAULT_MAX_ROWS;

    if (!empty($selectedNama)) {
        [$rekap, $profile] = $this->getRekapData(
            $selectedNama,
            $selectedBulan,
            $selectedTahun
        );

        $unit = $this->getUnitByRekapOrProfile($rekap, $profile);

        $muridList = $this->getMuridList(
            $selectedNama,
            $profile,
            $statusFilter,
            $selectedBulan,
            $selectedTahun
        );

        $maxRows = max(count($muridList), self::DEFAULT_MAX_ROWS);
    }

    return view('slip-progresif.index', compact(
        'unitList',
        'selectedUnit',
        'namaList',
        'selectedNama',
        'selectedBulan',
        'selectedTahun',
        'rekap',
        'profile',
        'unit',
        'muridList',
        'maxRows'
    ));
}

/**
 * Ambil daftar nama unik untuk filter
 */
private function getUniqueNamaList(?string $unit = null): array
{
    return RekapProgresif::query()
        ->whereIn('jabatan', ['Guru', 'Kepala Unit'])
        ->when($unit, fn($q) => $q->where('bimba_unit', $unit))
        ->groupBy('nama')
        ->orderBy('nama')
        ->pluck('nama')
        ->toArray();
}

    public function previewPdf(Request $request)
    {
        $selectedNama   = $request->get('nama');
        $selectedBulan  = $request->get('bulan');
        $selectedTahun  = $request->get('tahun', date('Y'));

        [$rekap, $profile] = $this->getRekapData($selectedNama, $selectedBulan, $selectedTahun);
        $unit              = $this->getUnitByRekapOrProfile($rekap, $profile);
        $muridList         = $this->getMuridList($selectedNama, $profile, null, $selectedBulan, $selectedTahun);

        $pdf = Pdf::loadView('slip-progresif.pdf', compact('profile', 'rekap', 'unit', 'muridList'))
            ->setPaper('A5', 'landscape');

        $bulanLabel = $selectedBulan ?? 'SemuaBulan';
        return $pdf->stream("slip-progresif-{$selectedNama}-{$bulanLabel}-{$selectedTahun}.pdf");
    }

    /**
     * Ambil data rekap (atau default object jika belum ada).
     */
    private function getRekapData($nama, $bulan = null, $tahun = null): array
{
    $profile = Profile::where('nama', $nama)->firstOrFail();

    $rekap = RekapProgresif::where('nama', $nama)
        ->when($bulan, fn($q) => $q->where('bulan', $bulan))
        ->when($tahun, fn($q) => $q->where('tahun', $tahun))
        ->first();

    if (!$rekap) {
        $rekap = (object) array_merge([
            'nama'    => $nama,
            'bulan'   => $bulan ?? '-',
            'tahun'   => $tahun ?? date('Y'),
            'jabatan' => $profile->jabatan ?? '-',
            'unit'    => $profile->biMBA_unit ?? $profile->unit ?? $profile->departemen ?? null,
        ], self::DEFAULT_REKAP_VALUES);
    }

    $jabatan = strtolower($profile->jabatan ?? '');
    $isGuru = str_contains($jabatan, 'guru') || str_contains($jabatan, 'pengajar') || str_contains($jabatan, 'tutor');
    $isKepalaUnit = str_contains($jabatan, 'kepala unit') || str_contains($jabatan, 'ku');

    // Normalisasi unit key (pakai biMBA_unit sebagai prioritas utama)
    $unitKey = trim($profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit ?? $profile->departemen ?? '');
    $unitKey = preg_replace('/\s+/', ' ', $unitKey);

    Log::info("[SLIP getRekapData] Processing {$nama} | UnitKey: '{$unitKey}' | Jabatan: {$jabatan}");

    if ($isGuru) {
        // Guru: murid langsung dari nama guru (exact match)
        $muridAktif = BukuInduk::where('guru', $nama)
            ->whereRaw("LOWER(status) LIKE '%aktif%'")
            ->get();

        $am1 = $muridAktif->count();
        $am2 = $this->hitungBayarDenganPadding($muridAktif, $bulan, $tahun);
        $scope = 'guru_exact_nama';

    } elseif ($isKepalaUnit) {
        // Kepala Unit: murid hanya dari bimba_unit yang sama persis
        if (empty($unitKey)) {
            Log::warning("[SLIP] Kepala Unit {$nama} tanpa unit key");
            $am1 = 0;
            $am2 = 0;
        } else {
            // Hitung AM1: murid aktif di bimba_unit exact
            $am1 = BukuInduk::where('bimba_unit', $unitKey)
                ->whereRaw("LOWER(status) LIKE '%aktif%'")
                ->count();

            // AM2: hitung yang bayar dari murid di unit tersebut
            $muridDiUnit = BukuInduk::where('bimba_unit', $unitKey)
                ->whereRaw("LOWER(status) LIKE '%aktif%'")
                ->get();

            $am2 = $this->hitungBayarDenganPadding($muridDiUnit, $bulan, $tahun);
        }
        $scope = 'kepala_unit_exact_bimba_unit';

    } else {
        // Admin/Owner: total semua (tapi sebaiknya batasi atau beri warning)
        $am1 = BukuInduk::whereRaw("LOWER(status) LIKE '%aktif%'")->count();
        $am2 = $this->hitungSemuaYangBayar($bulan, $tahun);
        $scope = 'admin_all';
    }

    $rekap->murid_aktif_am1 = (int) $am1;
    $rekap->murid_aktif_am2 = (int) $am2;
    $rekap->murid_aktif_am2_source = $scope;

    Log::info("[SLIP] {$nama} | Unit: {$unitKey} | AM1: {$am1} | AM2: {$am2} | Scope: {$scope}");

    return [$rekap, $profile];
}
    // Untuk guru yang punya data BukuInduk -> hitung AM2 via BukuInduk
    private function hitungYangBayarMilikiGuru($guru, $bulan, $tahun)
    {
        
        return $this->hitungBayarDenganPadding(
            BukuInduk::where('guru', 'like', "%{$guru}%")->whereRaw("LOWER(status) LIKE '%aktif%'")->get(),
            $bulan, $tahun
        );
    }

    /**
     * Hitung AM2 untuk semua guru di sebuah unit (safe).
     */
    private function hitungYangBayarDiUnit($unitName, $bulan, $tahun)
    {
        if (!$unitName) return 0;

        $unitKey = trim(strtolower($unitName));

        // Bangun query Profile berdasarkan kolom yang tersedia
        $profileQuery = Profile::query();
        if (Schema::hasColumn('profiles', 'unit') && Schema::hasColumn('profiles', 'departemen')) {
            $profileQuery->where(function($q) use ($unitKey) {
                $q->whereRaw("LOWER(unit) LIKE ?", ["%{$unitKey}%"])
                    ->orWhereRaw("LOWER(departemen) LIKE ?", ["%{$unitKey}%"]);
            });
        } elseif (Schema::hasColumn('profiles', 'unit')) {
            $profileQuery->whereRaw("LOWER(unit) LIKE ?", ["%{$unitKey}%"]);
        } elseif (Schema::hasColumn('profiles', 'departemen')) {
            $profileQuery->whereRaw("LOWER(departemen) LIKE ?", ["%{$unitKey}%"]);
        } else {
            Log::warning("[SLIP] Tabel profiles tidak punya kolom 'unit' atau 'departemen'. UnitKey={$unitKey}");
            return 0;
        }

        $profileQuery->whereRaw("LOWER(jabatan) NOT LIKE '%kepala unit%'");

        $guruDiUnit = $profileQuery->pluck('nama')->toArray();

        Log::info("[SLIP] hitungYangBayarDiUnit (safe): unit={$unitKey}, guru_count=" . count($guruDiUnit));

        if (empty($guruDiUnit)) return 0;

        // Ambil murid BukuInduk dengan tolerant matching pada kolom guru
        $muridCollection = BukuInduk::where(function($q) use ($guruDiUnit) {
                            foreach ($guruDiUnit as $g) {
                                $q->orWhereRaw("LOWER(guru) LIKE ?", ['%'.strtolower($g).'%']);
                            }
                        })
                        ->whereRaw("LOWER(status) LIKE '%aktif%'")
                        ->get();

        return $this->hitungBayarDenganPadding($muridCollection, $bulan, $tahun);
    }

    private function hitungSemuaYangBayar($bulan, $tahun)
    {
        return $this->hitungBayarDenganPadding(
            BukuInduk::whereRaw("LOWER(status) LIKE '%aktif%'")->get(),
            $bulan, $tahun
        );
    }

    /**
     * Hitung berapa NIM unik yang membayar di periode tertentu.
     * Sudah diperbaiki untuk mengatasi inkonsistensi NIM (padded vs unpadded) di tabel Penerimaan.
     */
    private function hitungBayarDenganPadding($muridCollection, $bulan, $tahun)
    {
        if ($muridCollection->isEmpty()) return 0;

        // 1. Buat daftar NIM PADDED (seperti logic awal)
        $nimsPadded = $muridCollection->pluck('nim')
            ->map(fn($n) => str_pad((string)($n ?? ''), 5, '0', STR_PAD_LEFT))
            ->filter(fn($n) => strlen($n) > 0)
            ->unique();
        
        // 2. Buat daftar NIM RAW (tanpa padding) - Untuk mencari data yang mungkin tidak di-pad
        $nimsRaw = $muridCollection->pluck('nim')
            ->filter(fn($n) => !empty($n))
            ->unique()
            ->map(fn($n) => (string)$n); // Pastikan string

        // 3. Gabungkan kedua daftar untuk mencakup semua kemungkinan format di tabel Penerimaan
        $nimsCombined = $nimsPadded->merge($nimsRaw)->unique()->toArray();

        if (empty($nimsCombined)) return 0;

        Log::info("[SLIP] Querying Penerimaan for: " . count($nimsCombined) . " combined NIMs.");
        // Log::info("[SLIP] Combined NIMs (sample): " . implode(', ', array_slice($nimsCombined, 0, 10)));

        $query = Penerimaan::query();
        if ($bulan && $tahun) {
            // Menggunakan whereRaw untuk mencari substring bulan, seperti sebelumnya
            $query->whereRaw('LOWER(bulan) LIKE ?', ["%".strtolower($bulan)."%"])->where('tahun', $tahun);
        } elseif ($tahun) {
            $query->where('tahun', $tahun);
        }
        
        // FIX: Hapus DB::raw LPAD di sini. Cukup cocokkan kolom 'nim' di Penerimaan 
        // dengan daftar NIM gabungan ($nimsCombined) yang sudah disiapkan.
        $bayar = $query->whereIn('nim', $nimsCombined)->get()
            ->map(fn($i) => (object)[
                'nim_padded' => str_pad((string)($i->nim ?? ''), 5, '0', STR_PAD_LEFT),
                'nilai' => $this->getPaidAmount($i)
            ])
            ->filter(fn($i) => $i->nilai > 0); // Hanya hitung yang nilai bayarnya > 0

        return $bayar->pluck('nim_padded')->unique()->count();
    }

    private function getPaidAmount($item): float
    {
        return (float)($item->spp ?? $item->jumlah ?? $item->nominal ?? $item->total ?? $item->bayar ?? 0);
    }

    private function isAdminOrOwner(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        return in_array($user->email ?? '', ['admin@bimba.com', 'owner@bimba.com', 'kamu@gmail.com'])
            || in_array($user->role ?? '', ['admin', 'owner', 'superadmin'])
            || in_array($user->nama ?? '', ['Admin', 'Owner', 'Developer']);
    }

    /**
     * Ambil daftar murid untuk tampilan / PDF.
     */
    private function getMuridList($nama, $profile, $statusFilter = null, $bulan = null, $tahun = null)
{
    $jabatan = strtolower($profile->jabatan ?? '');
    $muridQuery = BukuInduk::query()
        ->select('nim', 'nama', 'kelas', 'gol', 'kd', 'spp', 'status', 'note', 'guru', 'bimba_unit');

    // Normalisasi unit dari profile
    $unitKey = trim($profile->biMBA_unit ?? $profile->bimba_unit ?? $profile->unit ?? $profile->departemen ?? '');
    $unitKey = preg_replace('/\s+/', ' ', $unitKey); // hilangkan spasi ganda

    if (empty($unitKey)) {
        Log::warning("[SLIP getMuridList] Unit kosong untuk {$nama}");
        return [];
    }

    Log::debug("[SLIP getMuridList] Filter unit exact: {$unitKey} untuk {$nama} ({$jabatan})");

    if (str_contains($jabatan, 'kepala unit')) {
        // Kepala Unit: murid di unit yang sama persis
        $muridQuery->where('bimba_unit', $unitKey);  // ← EXACT MATCH
    } else {
        // Guru: murid yang gurunya persis nama ini
        $muridQuery->where('guru', $nama);
        // Optional: tambah filter unit jika guru punya unit spesifik
        if (!empty($unitKey)) {
            $muridQuery->where('bimba_unit', $unitKey);
        }
    }

    // Filter status
    if ($statusFilter) {
        $muridQuery->whereRaw('LOWER(status) = ?', [strtolower($statusFilter)]);
    } else {
        $muridQuery->where(function ($q) {
            $q->whereRaw("LOWER(status) LIKE '%aktif%'")
              ->orWhereRaw("LOWER(status) LIKE '%baru%'");
        });
    }

    $muridData = $muridQuery->orderBy('nama')->get();

    // Normalisasi SPP
    return $muridData->map(function ($murid) {
        $angkaBersih = preg_replace('/[^0-9]/', '', $murid->spp ?? '');
        if ($angkaBersih === '' || !is_numeric($angkaBersih)) {
            $murid->spp = '300.000';
        } else {
            $angka = (int)$angkaBersih;
            if ($angka < 10000) $angka *= 1000;
            $murid->spp = number_format($angka, 0, ',', '.');
        }
        return $murid;
    })->toArray();
}


    /**
    * Ambil unit object (Unit model) berdasarkan rekap/profile.
    * Selalu sertakan field 'no_induk' yang berasal dari profile (bukan dari unit).
    */
    /**
 * Ambil unit object (Unit model) berdasarkan rekap/profile.
 * Selalu sertakan field 'no_induk' yang berasal dari profile (bukan dari unit).
 */
private function getUnitByRekapOrProfile($rekap = null, $profile = null)
{
    // --- 1. No induk untuk ditempel di object unit ---
    $noInduk = $profile->nomor_induk
        ?? $profile->no_induk
        ?? $profile->nik
        ?? '-';

    $default = (object) [
        'no_cabang'  => '-',
        'biMBA_unit' => '-',
        'no_induk'   => $noInduk,
        'nik'        => $noInduk,
    ];

    if (!$rekap && !$profile) {
        return $default;
    }

    // --- 2. Kumpulkan kandidat nama unit dengan urutan prioritas ---
    $candidates = [];

    if (!empty($profile?->unit)) {
        $candidates[] = $profile->unit;
    }
    if (!empty($rekap?->unit)) {
        $candidates[] = $rekap->unit;
    }
    if (!empty($rekap?->bimba_unit)) {
        $candidates[] = $rekap->bimba_unit;
    }
    if (!empty($profile?->departemen)) {
        // fallback terakhir
        $candidates[] = $profile->departemen;
    }

    // Bersihkan & unik (pakai lowercase biar konsisten)
    $candidates = array_values(array_unique(
        array_filter(array_map(fn($v) => trim($v), $candidates))
    ));

    if (empty($candidates)) {
        return $default;
    }

    $unit = null;

    // --- 3. Coba EXACT MATCH dulu (LOWER(biMBA_unit) = ?) ---
    foreach ($candidates as $cand) {
        $unit = Unit::whereRaw('LOWER(biMBA_unit) = ?', [strtolower($cand)])->first();
        if ($unit) {
            break;
        }
    }

    // --- 4. Kalau belum ketemu, baru pakai LIKE '%...%' sebagai fallback ---
    if (!$unit) {
        foreach ($candidates as $cand) {
            $unit = Unit::whereRaw('LOWER(biMBA_unit) LIKE ?', ['%' . strtolower($cand) . '%'])->first();
            if ($unit) {
                break;
            }
        }
    }

    if (!$unit) {
        return $default;
    }

    $unitObj = (object) $unit->toArray();
    $unitObj->no_induk = $noInduk;
    $unitObj->nik      = $noInduk;

    return $unitObj;
}




    public function getMuridByGuru(Request $request)
    {
        $guru = $request->input('guru');
        $murid = BukuInduk::where('guru', $guru)
            ->select('nim', 'nama', 'kelas', 'gol', 'kd', 'spp', 'status', 'note')
            ->get();
        return response()->json($murid);
    }

    private function hitungMuridAktifDiUnit($unitName)
    {
        if (!$unitName) return 0;

        $unitKey = trim(strtolower($unitName));

        $profileQuery = Profile::query();
        if (Schema::hasColumn('profiles', 'unit') && Schema::hasColumn('profiles', 'departemen')) {
            $profileQuery->where(function($q) use ($unitKey) {
                $q->whereRaw("LOWER(unit) LIKE ?", ["%{$unitKey}%"])
                    ->orWhereRaw("LOWER(departemen) LIKE ?", ["%{$unitKey}%"]);
            });
        } elseif (Schema::hasColumn('profiles', 'unit')) {
            $profileQuery->whereRaw("LOWER(unit) LIKE ?", ["%{$unitKey}%"]);
        } elseif (Schema::hasColumn('profiles', 'departemen')) {
            $profileQuery->whereRaw("LOWER(departemen) LIKE ?", ["%{$unitKey}%"]);
        } else {
            Log::warning("[SLIP] Tabel profiles tidak punya kolom 'unit' atau 'departemen'. UnitKey={$unitKey}");
            return 0;
        }

        $profileQuery->whereRaw("LOWER(jabatan) NOT LIKE '%kepala unit%'");

        $guruDiUnit = $profileQuery->pluck('nama')->toArray();

        if (empty($guruDiUnit)) {
            Log::warning("[SLIP] Tidak ada guru di unit (safe): {$unitKey}");
            return 0;
        }

        Log::info("[SLIP] Guru di unit {$unitKey}: " . implode(', ', $guruDiUnit));

        // tolerant match BukuInduk.guru
        $count = BukuInduk::where(function($q) use ($guruDiUnit) {
                            foreach ($guruDiUnit as $g) {
                                $q->orWhereRaw("LOWER(guru) LIKE ?", ['%'.strtolower($g).'%']);
                            }
                        })
                        ->whereRaw("LOWER(status) LIKE '%aktif%'")
                        ->count();

        return $count;
    }

    /**
     * Helper: ambil "unit key" dari profile dengan fallback ke departemen.
     */
    private function getProfileUnitKey($profile): ?string
    {
        $unitVal = null;
        if (Schema::hasColumn('profiles', 'unit') && !empty($profile->unit)) {
            $unitVal = $profile->unit;
        } elseif (Schema::hasColumn('profiles', 'departemen') && !empty($profile->departemen)) {
            $unitVal = $profile->departemen;
        } else {
            $unitVal = $profile->departemen ?? null;
        }

        if ($unitVal === null) return null;
        return trim(strtolower($unitVal));
    }
}