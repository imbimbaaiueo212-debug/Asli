<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\BukuInduk;
use App\Models\Unit;
use App\Models\MuridTrial;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PerkembanganUnitController extends Controller
{
   public function index(Request $request)
{
    Carbon::setLocale('id');

    $user = Auth::user();
    $isAdmin = in_array($user->role ?? '', ['admin', 'superadmin', 'pusat']);

    $tahunMulai = (int) $request->input('tahun_mulai', date('Y'));
    $bulan      = $request->filled('bulan') ? (int) $request->input('bulan') : null;
    if ($bulan !== null && ($bulan < 1 || $bulan > 12)) {
        $bulan = null;
    }

    // ====================== HAK AKSES ======================
    if (!$isAdmin) {
        $bimba_unit_input = $user->bimba_unit ?? '';
        $no_cabang        = $user->no_cabang ?? '';

        if (empty($bimba_unit_input) || empty($no_cabang)) {
            return redirect()->route('dashboard')
                ->with('error', 'Unit Anda belum terdaftar di sistem.');
        }
    } else {
        $bimba_unit_input = $request->input('bimba_unit');
        $no_cabang        = $request->input('no_cabang');
    }

    $bimba_unit_norm = mb_strtoupper(trim((string) $bimba_unit_input));

    $unitTerpilih = null;
    if ($bimba_unit_norm !== '') {
        $unitTerpilih = Unit::whereRaw('TRIM(UPPER(bimba_unit)) = ?', [$bimba_unit_norm])->first();
        if ($unitTerpilih && empty($no_cabang)) {
            $no_cabang = $unitTerpilih->no_cabang;
        }
    }

    if (empty($bimba_unit_norm) || empty($no_cabang) || !$unitTerpilih) {
        return view('perkembangan_units.index', [
            'unitTerpilih' => null,
            'bimba_unit'   => $bimba_unit_input,
            'no_cabang'    => $no_cabang,
            'tahunMulai'   => $tahunMulai,
            'bulan'        => $bulan,
            'mb'  => array_fill(0, 12, 0),
            'mk'  => array_fill(0, 12, 0),
            'ma'  => array_fill(0, 12, 0),
            'ma1' => array_fill(0, 12, 0),
            'mtb' => array_fill(0, 12, 0),
            'mta' => array_fill(0, 12, 0),
            'bnf' => array_fill(0, 12, 0),
            'd'   => array_fill(0, 12, 0),
        ]);
    }

    $base = BukuInduk::query();

    if (!$isAdmin) {
        $base->where('bimba_unit', $user->bimba_unit)
             ->where('no_cabang', $user->no_cabang);
    } else {
        $base->whereRaw('TRIM(UPPER(bimba_unit)) = ?', [$bimba_unit_norm])
             ->where('no_cabang', $no_cabang);
    }

    // MA1, MB, MK, MA (tetap sama)
    $ma1 = array_fill(0, 12, 0);
    $runningTotal = $base->clone()
        ->where('tgl_masuk', '<', Carbon::create($tahunMulai, 1, 1))
        ->where(function ($q) use ($tahunMulai) {
            $q->whereNull('tgl_keluar')
              ->orWhere('tgl_keluar', '>=', Carbon::create($tahunMulai, 1, 1));
        })
        ->count();

    $ma1[0] = $runningTotal;

    for ($m = 2; $m <= 12; $m++) {
        $start = Carbon::create($tahunMulai, $m-1, 1)->startOfMonth();
        $end   = Carbon::create($tahunMulai, $m-1, 1)->endOfMonth()->endOfDay();

        $masuk = $base->clone()
            ->where('tgl_masuk', '>=', $start)
            ->where('tgl_masuk', '<=', $end)
            ->count();

        $keluar = $base->clone()
            ->where('tgl_keluar', '>=', $start)
            ->where('tgl_keluar', '<=', $end)
            ->count();

        $runningTotal += $masuk - $keluar;
        $ma1[$m - 1] = $runningTotal;
    }

    $mb = array_fill(0, 12, 0);
    $queryBaru = $base->clone()->whereYear('tgl_masuk', $tahunMulai);
    if ($bulan !== null) $queryBaru->whereMonth('tgl_masuk', $bulan);
    $baru = $queryBaru->selectRaw('MONTH(tgl_masuk) as bulan, COUNT(*) as jumlah')
                      ->groupBy('bulan')->pluck('jumlah', 'bulan');
    foreach ($baru as $bln => $jumlah) $mb[$bln - 1] = (int) $jumlah;

    $mk = array_fill(0, 12, 0);
    $keluarQuery = $base->clone()->whereNotNull('tgl_keluar')->whereYear('tgl_keluar', $tahunMulai);
    if ($bulan !== null) $keluarQuery->whereMonth('tgl_keluar', $bulan);
    $keluarData = $keluarQuery->selectRaw('MONTH(tgl_keluar) as bulan, COUNT(*) as jumlah')
                              ->groupBy('bulan')->pluck('jumlah', 'bulan');
    foreach ($keluarData as $bln => $jumlah) $mk[$bln - 1] = (int) $jumlah;

    $ma = array_fill(0, 12, 0);
    $bulanLoop = $bulan !== null ? [$bulan] : range(1, 12);
    foreach ($bulanLoop as $m) {
        $cutoff = Carbon::create($tahunMulai, $m, 1)->endOfMonth()->endOfDay();
        $ma[$m - 1] = $base->clone()
            ->where('tgl_masuk', '<=', $cutoff)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('tgl_keluar')
                  ->orWhere('tgl_keluar', '>', $cutoff);
            })
            ->count();
    }

    // =============================================
// MTB = Murid Trial Baru (berdasarkan tanggal_trial_baru)
// =============================================
$mtb = array_fill(0, 12, 0);

$trialBaruQuery = MuridTrial::query()
    ->whereNotNull('tanggal_trial_baru')
    ->whereYear('tanggal_trial_baru', $tahunMulai);

if (!$isAdmin) {
    $trialBaruQuery->where(function ($q) use ($user) {
        $q->where('bimba_unit', 'LIKE', "%{$user->bimba_unit}%")
          ->orWhere('no_cabang', $user->no_cabang);
    });
} else {
    $trialBaruQuery->where(function ($q) use ($bimba_unit_norm, $no_cabang) {
        $q->whereRaw('TRIM(UPPER(bimba_unit)) = ?', [$bimba_unit_norm])
          ->orWhere('bimba_unit', 'LIKE', "%{$bimba_unit_norm}%")
          ->orWhere('no_cabang', $no_cabang);
    });
}

if ($bulan !== null) {
    $trialBaruQuery->whereMonth('tanggal_trial_baru', $bulan);
}

$trialBaruData = $trialBaruQuery
    ->selectRaw('MONTH(tanggal_trial_baru) as bulan, COUNT(*) as jumlah')
    ->groupBy('bulan')
    ->pluck('jumlah', 'bulan');

foreach ($trialBaruData as $bln => $jumlah) {
    $mtb[$bln - 1] = (int) $jumlah;
}

// =============================================
// MTA = Murid Trial Aktif (berdasarkan tanggal_aktif)
// =============================================
$mta = array_fill(0, 12, 0);

$trialAktifQuery = MuridTrial::query()
    ->whereNotNull('tanggal_aktif')
    ->whereYear('tanggal_aktif', $tahunMulai);

if (!$isAdmin) {
    $trialAktifQuery->where(function ($q) use ($user) {
        $q->where('bimba_unit', 'LIKE', "%{$user->bimba_unit}%")
          ->orWhere('no_cabang', $user->no_cabang);
    });
} else {
    $trialAktifQuery->where(function ($q) use ($bimba_unit_norm, $no_cabang) {
        $q->whereRaw('TRIM(UPPER(bimba_unit)) = ?', [$bimba_unit_norm])
          ->orWhere('bimba_unit', 'LIKE', "%{$bimba_unit_norm}%")
          ->orWhere('no_cabang', $no_cabang);
    });
}

if ($bulan !== null) {
    $trialAktifQuery->whereMonth('tanggal_aktif', $bulan);
}

$trialAktifData = $trialAktifQuery
    ->selectRaw('MONTH(tanggal_aktif) as bulan, COUNT(*) as jumlah')
    ->groupBy('bulan')
    ->pluck('jumlah', 'bulan');

foreach ($trialAktifData as $bln => $jumlah) {
    $mta[$bln - 1] = (int) $jumlah;
}

    // BNF & D (tetap)
    $bnf = array_fill(0, 12, 0);
    $bnfQuery = $base->clone()->whereIn('gol', ['S3B1', 'S3B2', 'S3B3']);
    if ($bulan !== null) $bnfQuery->whereMonth('tgl_masuk', $bulan);
    $bnfData = $bnfQuery->selectRaw('MONTH(tgl_masuk) as bulan, COUNT(*) as jumlah')
                        ->groupBy('bulan')->pluck('jumlah', 'bulan');
    foreach ($bnfData as $bln => $jumlah) $bnf[$bln - 1] = (int) $jumlah;

    $d = array_fill(0, 12, 0);
    $dhuafaQuery = $base->clone()->where('gol', 'D');
    if ($bulan !== null) $dhuafaQuery->whereMonth('tgl_masuk', $bulan);
    $dhuafaData = $dhuafaQuery->selectRaw('MONTH(tgl_masuk) as bulan, COUNT(*) as jumlah')
                              ->groupBy('bulan')->pluck('jumlah', 'bulan');
    foreach ($dhuafaData as $bln => $jumlah) $d[$bln - 1] = (int) $jumlah;

    // TOTAL
    $total_mb  = array_sum($mb);
    $total_mk  = array_sum($mk);
    $total_ma  = !empty($ma)  ? end($ma)  : 0;
    $total_ma1 = !empty($ma1) ? end($ma1) : 0;
    $total_mtb = !empty($mtb) ? end($mtb) : 0;
    $total_mta = !empty($mta) ? end($mta) : 0;
    $total_bnf = !empty($bnf) ? end($bnf) : 0;
    $total_d   = !empty($d)   ? end($d)   : 0;

    $totalMuridKeseluruhan = $total_ma1 + $total_mk;

    $sppPerBulan = $this->getSppPerBulan($tahunMulai, $bulan, $bimba_unit_norm);

    return view('perkembangan_units.index', [
        'unitTerpilih'       => $unitTerpilih,
        'bimba_unit'         => $bimba_unit_input,
        'no_cabang'          => $no_cabang,
        'tahunMulai'         => $tahunMulai,
        'bulan'              => $bulan,
        'mb'   => $mb,
        'mk'   => $mk,
        'ma'   => $ma,
        'ma1'  => $ma1,
        'mtb'  => $mtb,
        'mta'  => $mta,
        'bnf'  => $bnf,
        'd'    => $d,
        'sppPerBulan'        => $sppPerBulan,
        'totalMuridKeseluruhan' => $totalMuridKeseluruhan,
        'total_mb'  => $total_mb,
        'total_mk'  => $total_mk,
        'total_ma'  => $total_ma,
        'total_ma1' => $total_ma1,
        'total_mtb' => $total_mtb,
        'total_mta' => $total_mta,
        'total_bnf' => $total_bnf,
        'total_d'   => $total_d,
    ]);
}

    /**
     * Ambil data SPP per bulan dari tabel penerimaan
     */
    private function getSppPerBulan($tahun, $bulanFilter = null, $bimba_unit_norm = null)
    {
        $query = \App\Models\Penerimaan::query()
            ->where('tahun', $tahun)
            ->when($bimba_unit_norm, function ($q) use ($bimba_unit_norm) {
                $q->whereRaw('TRIM(UPPER(bimba_unit)) = ?', [$bimba_unit_norm]);
            });

        if ($bulanFilter !== null) {
            $bulanNama = strtolower(Carbon::create()->month($bulanFilter)->translatedFormat('F'));
            $query->whereRaw('LOWER(TRIM(bulan)) = ?', [$bulanNama]);
        }

        $penerimaan = $query->get();

        $grouped = $penerimaan
            ->groupBy(function ($item) {
                return strtolower(trim($item->bulan ?? ''));
            })
            ->map(function ($group) {
                return [
                    'bulan'        => ucfirst(strtolower(trim($group->first()->bulan ?? ''))),
                    'jumlah_murid' => $group->unique('nim')->count(),
                    'total_spp'    => $group->sum(function ($item) {
                        return (float) ($item->spp ?? $item->jumlah ?? $item->nominal ?? $item->total ?? 0);
                    }),
                ];
            });

        $bulanList = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
        $result = [];

        foreach ($bulanList as $idx => $bln) {
            $data = $grouped->firstWhere('bulan', ucfirst($bln));
            $result[$idx] = $data ?? ['jumlah_murid' => 0, 'total_spp' => 0];
        }

        return $result;
    }
}