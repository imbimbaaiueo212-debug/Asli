<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BukuInduk;
use App\Models\Penerimaan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function home()
    {
        Carbon::setLocale('id');
        $currentYear = now()->year;

        // 1. Kartu status murid — diubah hanya totalBaru
    $totalBaru   = BukuInduk::whereYear('tgl_masuk', $currentYear)->count();
    // atau jika ingin lebih ketat (hanya yang benar-benar baru tahun ini dan status aktif/baru):
    // $totalBaru = BukuInduk::whereYear('tgl_masuk', $currentYear)
    //                        ->whereIn('status', ['baru', 'aktif'])
    //                        ->count();

    $totalAktif  = BukuInduk::where('status', 'aktif')->count();
    $totalKeluar = BukuInduk::where('status', 'keluar')->count();

        // 2. Grafik pendaftaran murid per bulan (tahun ini)
        $monthlyData = BukuInduk::select(
                DB::raw('MONTH(tgl_masuk) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->whereYear('tgl_masuk', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $totalsPerMonth = array_fill(1, 12, 0);
        foreach ($monthlyData as $m => $t) {
            $totalsPerMonth[$m] = (int) $t;
        }

        // 3. Aggregat Penerimaan: SPP & TOTAL per bulan
        $agg = Penerimaan::selectRaw('MONTH(tanggal) as month, SUM(spp) as sum_spp, SUM(total) as sum_total')
            ->whereYear('tanggal', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $sppPerMonth   = array_fill(1, 12, 0);
        $totalPerMonth = array_fill(1, 12, 0);

        foreach ($agg as $month => $row) {
            $sppPerMonth[$month]   = (int) ($row->sum_spp ?? 0);
            $totalPerMonth[$month] = (int) ($row->sum_total ?? 0);
        }

        $totalSPPSetahun   = array_sum($sppPerMonth);
        $totalOmzetSetahun = array_sum($totalPerMonth);

        // 4. Label bulan Indonesia
        $monthNames = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthNames[] = Carbon::create(null, $i, 1)->translatedFormat('F');
        }

        // 5. Data untuk Chart.js
        $chartData = [
            'labels' => $monthNames,
            'data'   => array_values($totalsPerMonth),
        ];

        $chartDataPenerimaan = [
            'labels' => $monthNames,
            'spp'    => array_values($sppPerMonth),
            'total'  => array_values($totalPerMonth),
        ];

        // 6. Recent Registrations (6 pendaftaran terbaru)
        $recentMurid = BukuInduk::select(
        'nama',
        'tgl_masuk',
        'status',
        'no_cabang'
    )
    ->with(['unit' => function ($query) {
        $query->select('no_cabang', 'biMBA_unit');
    }])
    ->orderBy('tgl_masuk', 'desc')
    ->take(6)
    ->get();

        // 7. Top Units (top 5 unit dengan murid terbanyak)
        $topUnits = BukuInduk::select(
        'no_cabang',
        DB::raw('COUNT(*) as total_murid')
    )
    ->with(['unit' => function ($query) {
        $query->select('no_cabang', 'biMBA_unit')
              ->withoutGlobalScopes();
    }])
    ->whereNotNull('no_cabang')
    ->groupBy('no_cabang')
    ->orderByDesc('total_murid')
    ->take(5)
    ->get();


        return view('home', compact(
            'totalsPerMonth',
            'totalBaru',
            'totalAktif',
            'totalKeluar',
            'chartData',
            'chartDataPenerimaan',
            'totalSPPSetahun',
            'totalOmzetSetahun',
            'recentMurid',
            'topUnits'
        ));
    }
}