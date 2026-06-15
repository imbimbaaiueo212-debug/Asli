<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\Unit;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class AdminPerkembanganUnitController extends Controller
{
    public function index(Request $request)
    {
        // =====================================================
        // HAK AKSES
        // =====================================================
        if (!auth()->user()?->isAdminUser()) {
            abort(403, 'Akses hanya untuk admin');
        }

        Carbon::setLocale('id');

        // =====================================================
        // FILTER INPUT (LINTAS TAHUN)
        // =====================================================
        $bulanMulai = (int) $request->get('bulan_mulai', 1);
        $tahunMulai = (int) $request->get('tahun_mulai', date('Y'));

        $bulanAkhir = (int) $request->get('bulan_akhir', 12);
        $tahunAkhir = (int) $request->get('tahun_akhir', date('Y'));

        // Validasi ringan
        $bulanMulai = max(1, min(12, $bulanMulai));
        $bulanAkhir = max(1, min(12, $bulanAkhir));

        // =====================================================
        // RANGE TANGGAL (KUNCI UTAMA)
        // =====================================================
        $startDate = Carbon::create($tahunMulai, $bulanMulai, 1)->startOfMonth();
        $endDate   = Carbon::create($tahunAkhir, $bulanAkhir, 1)->endOfMonth();

        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $monthsInRange = $startDate->diffInMonths($endDate) + 1;

        // =====================================================
        // AMBIL SEMUA UNIT (TANPA GLOBAL SCOPE)
        // =====================================================
        $units = Unit::withoutGlobalScopes()
            ->orderBy('biMBA_unit')
            ->orderBy('no_cabang')
            ->get();

        // =====================================================
        // INISIALISASI GRAFIK (DINAMIS & AMAN)
        // =====================================================
        $grafikAktif  = [];
        $grafikBaru   = [];
        $grafikKeluar = [];

        for ($i = 0; $i < $monthsInRange; $i++) {
            $grafikAktif[$i]  = 0;
            $grafikBaru[$i]   = 0;
            $grafikKeluar[$i] = 0;
        }

        $rekap = [];

        // =====================================================
        // LOOP PER UNIT
        // =====================================================
        foreach ($units as $unit) {

            $kodeUnit = trim($unit->biMBA_unit ?? '');
            if ($kodeUnit === '') continue;

            // Basis query (KUNCI KONSISTENSI)
            $base = BukuInduk::where('biMBA_unit', $kodeUnit);

            // -------------------------------------------------
            // AKTIF AWAL PERIODE
            // -------------------------------------------------
            $awalPeriode = $startDate->copy()->subSecond();

            $aktifAwal = (clone $base)
                ->whereDate('tgl_masuk', '<=', $awalPeriode)
                ->where(function ($q) use ($awalPeriode) {
                    $q->whereNull('tgl_keluar')
                      ->orWhere('tgl_keluar', '>', $awalPeriode);
                })
                ->count();

            $totalBaru   = 0;
            $totalKeluar = 0;

            // -------------------------------------------------
            // LOOP BULAN (LINTAS TAHUN)
            // -------------------------------------------------
            for ($i = 0; $i < $monthsInRange; $i++) {

                $bulan = $startDate->copy()->addMonths($i);
                $startBulan = $bulan->copy()->startOfMonth();
                $endBulan   = $bulan->copy()->endOfMonth();

                // Murid baru
                $baru = (clone $base)
                    ->whereBetween('tgl_masuk', [$startBulan, $endBulan])
                    ->count();

                // Murid keluar
                $keluar = (clone $base)
                    ->whereBetween('tgl_keluar', [$startBulan, $endBulan])
                    ->count();

                // Murid aktif akhir bulan (snapshot)
                $aktifAkhirBulan = (clone $base)
                    ->whereDate('tgl_masuk', '<=', $endBulan)
                    ->where(function ($q) use ($endBulan) {
                        $q->whereNull('tgl_keluar')
                          ->orWhere('tgl_keluar', '>', $endBulan);
                    })
                    ->count();

                // ⛑️ SAFEGUARD (ANTI ERROR ARRAY KEY)
                $grafikBaru[$i]   = ($grafikBaru[$i]   ?? 0) + $baru;
                $grafikKeluar[$i] = ($grafikKeluar[$i] ?? 0) + $keluar;
                $grafikAktif[$i]  = ($grafikAktif[$i]  ?? 0) + $aktifAkhirBulan;

                $totalBaru   += $baru;
                $totalKeluar += $keluar;
            }

            // -------------------------------------------------
            // AKTIF AKHIR PERIODE
            // -------------------------------------------------
            $aktifAkhir = (clone $base)
                ->whereDate('tgl_masuk', '<=', $endDate)
                ->where(function ($q) use ($endDate) {
                    $q->whereNull('tgl_keluar')
                      ->orWhere('tgl_keluar', '>', $endDate);
                })
                ->count();

            // -------------------------------------------------
            // DHUAFA / BEASISWA
            // -------------------------------------------------
            $dhuafa = 0;
            if (Schema::hasColumn('buku_induk', 'status_beasiswa')) {
                $dhuafa = (clone $base)
                    ->whereDate('tgl_masuk', '<=', $endDate)
                    ->where(function ($q) use ($endDate) {
                        $q->whereNull('tgl_keluar')
                          ->orWhere('tgl_keluar', '>', $endDate);
                    })
                    ->whereIn('status_beasiswa', ['BNF', 'D'])
                    ->count();
            }

            // -------------------------------------------------
            // SIMPAN REKAP UNIT
            // -------------------------------------------------
            $rekap[$kodeUnit] = [
                'nama_unit'      => $kodeUnit,
                'no_cabang'      => $unit->no_cabang ?? '-',
                'aktif_lalu'     => $aktifAwal,
                'baru_periode'   => $totalBaru,
                'keluar_periode' => $totalKeluar,
                'aktif_akhir'    => $aktifAkhir,
                'dhuafa'         => $dhuafa,
            ];
        }

        // =====================================================
        // LABEL BULAN (LINTAS TAHUN)
        // =====================================================
        $namaBulanArr = [];
        for ($i = 0; $i < $monthsInRange; $i++) {
            $namaBulanArr[] = $startDate
                ->copy()
                ->addMonths($i)
                ->translatedFormat('M y');
        }

        $periode = $startDate->translatedFormat('F Y')
                 . ' - '
                 . $endDate->translatedFormat('F Y');

        // =====================================================
        // RETURN VIEW
        // =====================================================
        return view('admin.perkembangan-units.index', compact(
            'rekap',
            'bulanMulai',
            'tahunMulai',
            'bulanAkhir',
            'tahunAkhir',
            'periode',
            'grafikAktif',
            'grafikBaru',
            'grafikKeluar',
            'namaBulanArr'
        ));
    }
}
