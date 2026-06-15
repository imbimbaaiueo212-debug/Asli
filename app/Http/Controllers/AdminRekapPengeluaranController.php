<?php

namespace App\Http\Controllers;

use App\Models\PendapatanTunjangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRekapPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $bulan     = $request->get('bulan', now()->format('n'));
        $tahun     = $request->get('tahun', now()->year);
        $bimbaUnit = $request->get('bimba_unit');
        $noCabang  = $request->get('no_cabang');

        $rekap = $this->getRekapPengeluaran($bulan, $tahun, $bimbaUnit, $noCabang);

        $units = DB::table('units')
            ->select('biMBA_unit', 'no_cabang')
            ->distinct()
            ->orderBy('biMBA_unit')
            ->get();

        return view('admin.rekap-pengeluaran.index', compact(
            'rekap',
            'bulan',
            'tahun',
            'bimbaUnit',
            'noCabang',
            'units'
        ));
    }

    private function getRekapPengeluaran($bulan, $tahun, $bimbaUnit = null, $noCabang = null)
    {
        $bulanStr = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $periode  = "{$tahun}-{$bulanStr}";

        Log::info('Rekap Pengeluaran Pendapatan Tunjangan', [
            'periode'    => $periode,
            'bimba_unit' => $bimbaUnit,
            'no_cabang'  => $noCabang,
        ]);

        $query = PendapatanTunjangan::withoutGlobalScopes()
            ->where('bulan', $periode)

            // filter status tidak aktif
            ->whereNotIn(DB::raw('LOWER(status)'), [
                'resign',
                'keluar',
                'pensiun',
                'non aktif'
            ])

            // filter unit
            ->when($bimbaUnit, function ($q) use ($bimbaUnit) {
                $q->where('bimba_unit', trim($bimbaUnit));
            })

            // filter cabang
            ->when($noCabang, function ($q) use ($noCabang) {
                $q->where('no_cabang', $noCabang);
            });

        $data = $query->selectRaw("
            COUNT(DISTINCT nik) as jumlah_relawan,
            COALESCE(SUM(thp),0) as total_thp,
            COALESCE(SUM(kerajinan),0) as total_kerajinan,
            COALESCE(SUM(english),0) as total_english,
            COALESCE(SUM(mentor),0) as total_mentor,
            COALESCE(SUM(kekurangan),0) as total_kekurangan,
            COALESCE(SUM(tj_keluarga),0) as total_tj_keluarga,
            COALESCE(SUM(lain_lain),0) as total_lain_lain,
            COALESCE(SUM(
                COALESCE(thp,0) +
                COALESCE(kerajinan,0) +
                COALESCE(english,0) +
                COALESCE(mentor,0) +
                COALESCE(kekurangan,0) +
                COALESCE(tj_keluarga,0) +
                COALESCE(lain_lain,0)
            ),0) as total_pendapatan
        ")->first();

        if (!$data) {
            $data = (object)[
                'jumlah_relawan' => 0,
                'total_thp' => 0,
                'total_kerajinan' => 0,
                'total_english' => 0,
                'total_mentor' => 0,
                'total_kekurangan' => 0,
                'total_tj_keluarga' => 0,
                'total_lain_lain' => 0,
                'total_pendapatan' => 0,
            ];
        }

        return [
            'pendapatan_tunj' => $data,
            'grand_total_pendapatan' => $data->total_pendapatan,
            'periode' => $periode,
        ];
    }
}