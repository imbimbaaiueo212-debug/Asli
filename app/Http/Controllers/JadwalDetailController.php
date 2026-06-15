<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\JadwalDetail;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;  // ← ADD THIS LINE
use Throwable;

class JadwalDetailController extends Controller
{
    /**
     * Menampilkan daftar jadwal, difilter berdasarkan guru/pengampu.
     */
 public function index(Request $request)
{
    $guruNama         = trim($request->input('guru') ?? '');
    $selectedUnit     = trim($request->input('unit') ?? '');     // ← tetap pakai nama ini

    // Normalisasi untuk keperluan query
    $selectedUnitNorm = $this->cleanUnitName($selectedUnit);

    // Auto sync jika belum ada data
    if (JadwalDetail::count() === 0 || $request->query('sync') === 'auto') {
        $this->generateSilent();
    }

    // Cleanup murid yang sudah keluar
if ($request->query('cleanup') === 'true' || JadwalDetail::count() > 500) {
    $this->cleanupKeluar();
}

    $isAdmin = Auth::check() && Auth::user()->is_admin;

    $jabatanTarget = ['Guru', 'Kepala Unit', 'Pengajar', 'Pengajar Tetap', 'Tutor', 'Ka Unit', 'KU'];

    // ==================== QUERY GURU ====================
    $gurusQuery = Profile::whereIn('jabatan', $jabatanTarget)
        ->whereIn('status_karyawan', ['Aktif', 'Magang'])
        ->whereNotNull('nik')
        ->whereRaw("TRIM(nik) <> ''")
        ->select('nama', 'nik', 'bimba_unit', 'no_cabang', 'jabatan')
        ->orderBy('nik');

    if ($isAdmin && $selectedUnit !== '' && $selectedUnit !== 'SEMUA') {
        $gurusQuery->whereRaw("UPPER({$this->cleanUnitSql('bimba_unit')}) = ?", [$selectedUnitNorm]);
    }

    $gurus = $gurusQuery->get();

    // ==================== SAFETY NET GURU ====================
    $namaGuruDiJadwal = JadwalDetail::whereNotNull('guru')
        ->when($isAdmin && $selectedUnit !== '' && $selectedUnit !== 'SEMUA', function ($q) use ($selectedUnitNorm) {
            $q->whereHas('murid', fn($sq) => 
                $sq->whereRaw("UPPER({$this->cleanUnitSql('bimba_unit')}) = ?", [$selectedUnitNorm])
            );
        })
        ->whereRaw("TRIM(guru) <> '' AND guru != 'TANPA GURU'")
        ->distinct()
        ->pluck('guru');

    $namaSudahAda = $gurus->pluck('nama')->map(fn($n) => trim(strtoupper($n)));

    $namaKurang = $namaGuruDiJadwal
        ->map(fn($n) => trim(strtoupper($n)))
        ->diff($namaSudahAda);

    if ($namaKurang->isNotEmpty()) {
        $extra = Profile::whereIn('nama', $namaKurang)
            ->whereIn('status_karyawan', ['Aktif', 'Magang'])
            ->get()
            ->map(fn($p) => [
                'nama'       => $p->nama,
                'nik'        => $p->nik ?? '—',
                'jabatan'    => $p->jabatan ?? 'Pengampu (dari jadwal)',
                'bimba_unit' => $p->bimba_unit,
                'no_cabang'  => $p->no_cabang,
            ]);

        if ($extra->isNotEmpty()) {
            $gurus = $gurus->concat($extra)
                           ->unique('nama')
                           ->sortBy('nama')
                           ->values();
        }
    }

    if ($gurus->isEmpty()) {
        $gurus = collect([['nama' => 'TANPA GURU', 'nik' => '—', 'jabatan' => 'Sistem']]);
    }

        // ==================== DAFTAR UNIT (DEDUPLIKASI KUAT) ====================
    $unitsRaw = BukuInduk::whereIn('status', ['Aktif', 'Baru'])
        ->whereNotNull('bimba_unit')
        ->whereRaw("TRIM(bimba_unit) <> ''")
        ->select('bimba_unit')
        ->distinct()
        ->orderByRaw("UPPER(bimba_unit)")
        ->get();

    $units = [];
    $seen = []; // untuk deduplikasi berdasarkan nama bersih

    foreach ($unitsRaw as $item) {
        $original = trim($item->bimba_unit);
        $clean    = $this->cleanUnitName($original);
        
        // Hindari duplikat berdasarkan nama bersih
        if (!isset($seen[$clean])) {
            $seen[$clean] = true;
            $units[$original] = $clean;   // key tetap original (untuk filter), value = tampilan bersih
        }
    }

    // ==================== QUERY JADWAL ====================
$query = JadwalDetail::with(['murid' => function ($q) {
        $q->whereIn('status', ['Aktif', 'Baru']);   // ← TAMBAHKAN FILTER INI
    }])
    ->whereHas('murid', function ($q) {            // Pastikan murid masih aktif
        $q->whereIn('status', ['Aktif', 'Baru']);
    })
    ->orderBy('jam_ke', 'asc');

if ($isAdmin && $selectedUnit !== '' && $selectedUnit !== 'SEMUA') {
    $query->whereHas('murid', function ($q) use ($selectedUnitNorm) {
        $q->whereRaw("UPPER({$this->cleanUnitSql('bimba_unit')}) = ?", [$selectedUnitNorm])
          ->whereIn('status', ['Aktif', 'Baru']);   // ← TAMBAHKAN JUGA DISINI
    });
}

if ($guruNama !== '' && $guruNama !== 'SEMUA') {
    $query->whereRaw("TRIM(UPPER(guru)) = ?", [strtoupper(trim($guruNama))]);
}

$jadwal = $query->get()->groupBy('jam_ke');

    return view('jadwal.index', compact(
        'jadwal', 'gurus', 'guruNama', 'units', 'selectedUnit'
    ));
}


/**
 * Hapus JadwalDetail untuk murid yang sudah Keluar / Cuti
 */
public function cleanupKeluar()
{
    $deleted = JadwalDetail::whereHas('murid', function ($q) {
        $q->whereNotIn('status', ['Aktif', 'Baru']);
    })->delete();

    Log::info("✅ Cleanup JadwalDetail selesai", ['deleted' => $deleted]);

    return redirect()->route('jadwal.index')
        ->with('success', "Berhasil membersihkan {$deleted} jadwal murid yang sudah keluar/cuti.");
}
/**
 * Bersihkan nama unit - versi sangat robust
 */
private function cleanUnitName($name)
{
    if (empty($name)) return '';
    
    $clean = trim((string) $name);
    // Hapus prefix seperti "05141 | ", "05141|", dll
    $clean = preg_replace('/^\d+\s*[\|\-]\s*/', '', $clean);
    $clean = preg_replace('/\s+/', ' ', $clean);
    
    return trim(strtoupper($clean));
}

private function cleanUnitSql($column)
{
    return "TRIM(REGEXP_REPLACE(TRIM({$column}), '^[0-9]+\\s*[\\|\\-]\\s*', ''))";
}

    /**
     * Sinkronisasi / generate ulang JadwalDetail dari BukuInduk.
     * Idempotent (bisa dijalankan berulang tanpa duplikasi berlebih).
     */
    public function generate()
{
    DB::beginTransaction();

    try {

        $totalBaru   = 0;
        $totalSkip   = 0;
        $totalUpdate = 0;

        // Bersihkan seluruh jadwal lama
        JadwalDetail::withoutGlobalScopes()->truncate();

        BukuInduk::query()
            ->whereIn('status', ['Aktif', 'Baru'])
            ->whereNotNull('kode_jadwal')
            ->whereRaw("TRIM(kode_jadwal) <> ''")
            ->select([
                'id',
                'guru',
                'kelas',
                'kode_jadwal',
                'jenis_kbm'
            ])
            ->orderBy('id')
            ->chunkById(100, function ($muridList) use (&$totalBaru, &$totalSkip) {

                foreach ($muridList as $murid) {

                    $kodeStr = trim((string) $murid->kode_jadwal);

                    if ($kodeStr === '') {
                        $totalSkip++;
                        continue;
                    }

                    $kode = (int) preg_replace('/\D+/', '', $kodeStr);

                    if ($kode === 0) {
                        $totalSkip++;
                        continue;
                    }

                    $shift    = null;
                    $hariList = [];
                    $jam_ke   = null;

                    // SRJ
                    if ($kode >= 108 && $kode <= 116) {

                        $shift    = 'SRJ';
                        $hariList = ['Senin', 'Rabu', 'Jumat'];
                        $jam_ke   = $kode - 107;

                    }
                    // SKS
                    elseif ($kode >= 208 && $kode <= 211) {

                        $shift    = 'SKS';
                        $hariList = ['Selasa', 'Kamis', 'Sabtu'];
                        $jam_ke   = $kode - 207;

                    }
                    // S6
                    elseif ($kode >= 308 && $kode <= 311) {

                        $shift    = 'S6';
                        $hariList = [
                            'Senin',
                            'Selasa',
                            'Rabu',
                            'Kamis',
                            'Jumat',
                            'Sabtu'
                        ];

                        $jam_ke = $kode - 307;

                    } else {

                        $totalSkip++;
                        continue;
                    }

                    $guruNama = trim((string) $murid->guru);

                    if ($guruNama === '' || $guruNama === '-') {
                        $guruNama = 'TANPA GURU';
                    }

                    foreach ($hariList as $hari) {

                        JadwalDetail::create([
                            'murid_id'    => $murid->id,
                            'hari'        => $hari,
                            'shift'       => $shift,
                            'jam_ke'      => $jam_ke,
                            'guru'        => $guruNama,
                            'kelas'       => $murid->kelas ?? '-',
                            'kode_jadwal' => $murid->kode_jadwal,
                            'jenis_kbm'   => $murid->jenis_kbm ?? '-',
                        ]);

                        $totalBaru++;
                    }
                }
            });

        DB::commit();

        return redirect()
            ->route('jadwal.index')
            ->with(
                'success',
                "Sinkronisasi selesai! Baru: {$totalBaru} | Dilewati: {$totalSkip}"
            );

    } catch (Throwable $e) {

        DB::rollBack();

        Log::error('Gagal sinkron jadwal', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        report($e);

        return back()->with(
            'error',
            'Gagal sinkronisasi jadwal. Coba lagi atau hubungi admin.'
        );
    }
}
        private function generateSilent()
{
    try {

        DB::beginTransaction();

        JadwalDetail::withoutGlobalScopes()->truncate();

        BukuInduk::query()
            ->whereIn('status', ['Aktif', 'Baru'])
            ->whereNotNull('kode_jadwal')
            ->whereRaw("TRIM(kode_jadwal) <> ''")
            ->select([
                'id',
                'guru',
                'kelas',
                'kode_jadwal',
                'jenis_kbm'
            ])
            ->orderBy('id')
            ->chunkById(100, function ($muridList) {

                foreach ($muridList as $murid) {

                    $kode = (int) preg_replace(
                        '/\D+/',
                        '',
                        trim((string) $murid->kode_jadwal)
                    );

                    if (!$kode) {
                        continue;
                    }

                    if ($kode >= 108 && $kode <= 116) {

                        $shift    = 'SRJ';
                        $hariList = ['Senin', 'Rabu', 'Jumat'];
                        $jam_ke   = $kode - 107;

                    } elseif ($kode >= 208 && $kode <= 211) {

                        $shift    = 'SKS';
                        $hariList = ['Selasa', 'Kamis', 'Sabtu'];
                        $jam_ke   = $kode - 207;

                    } elseif ($kode >= 308 && $kode <= 311) {

                        $shift    = 'S6';
                        $hariList = [
                            'Senin',
                            'Selasa',
                            'Rabu',
                            'Kamis',
                            'Jumat',
                            'Sabtu'
                        ];

                        $jam_ke = $kode - 307;

                    } else {
                        continue;
                    }

                    $guruNama = trim((string) $murid->guru);

                    if ($guruNama === '' || $guruNama === '-') {
                        $guruNama = 'TANPA GURU';
                    }

                    foreach ($hariList as $hari) {

                        JadwalDetail::create([
                            'murid_id'    => $murid->id,
                            'hari'        => $hari,
                            'shift'       => $shift,
                            'jam_ke'      => $jam_ke,
                            'guru'        => $guruNama,
                            'kelas'       => $murid->kelas ?? '-',
                            'kode_jadwal' => $murid->kode_jadwal,
                            'jenis_kbm'   => $murid->jenis_kbm ?? '-',
                        ]);
                    }
                }
            });

        DB::commit();

    } catch (Throwable $e) {

        DB::rollBack();

        Log::error('Gagal sinkron otomatis', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
}