<?php

namespace App\Http\Controllers;

use App\Models\BukuInduk;
use App\Models\MuridTrial;          // ← PASTIKAN INI ADA
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BukuIndukStatistikController extends Controller
{
    public function index(Request $request)
    {
        // ================== INPUT RANGE ==================
        $bulanAwal  = $request->input('bulan_awal', now()->month);
        $tahunAwal  = $request->input('tahun_awal', now()->year);
        $bulanAkhir = $request->input('bulan_akhir', now()->month);
        $tahunAkhir = $request->input('tahun_akhir', now()->year);

        // ================== FORMAT TANGGAL ==================
        $startDate = Carbon::create($tahunAwal, $bulanAwal, 1)->startOfMonth();
        $endDate   = Carbon::create($tahunAkhir, $bulanAkhir, 1)->endOfMonth();

        // ================== VALIDASI ==================
        if ($startDate > $endDate) {
            return back()->with('error', 'Periode awal tidak boleh lebih besar dari periode akhir!');
        }

        $user = Auth::user();
        $isAdmin = in_array($user->role ?? '', ['admin', 'kepala_pusat', 'superadmin']);
        $userUnit = $user->bimba_unit ?? null;
        $selectedUnit = $request->input('unit_id');

        // ================== QUERY BUKU INDUK ==================
        $queryBukuInduk = BukuInduk::query();
        if (!$isAdmin) {
            $queryBukuInduk->where('bimba_unit', 'LIKE', "%{$userUnit}%");
        } elseif ($selectedUnit && $selectedUnit !== '') {
            $queryBukuInduk->where('bimba_unit', $selectedUnit);
        }

       // ================== MURID TRIAL BARU ==================
        $queryTrial = MuridTrial::query();

        if (!$isAdmin) {
            if ($userUnit) {
                $queryTrial->where('bimba_unit', 'LIKE', "%{$userUnit}%");
            }
        } 
        // Perbaikan untuk Admin yang memilih unit
        elseif ($selectedUnit && $selectedUnit !== '') {
            $queryTrial->where('bimba_unit', 'LIKE', "%{$selectedUnit}%");
        }

        $trialBaru = (clone $queryTrial)
            ->where('status_trial', 'baru')
            ->whereBetween('tanggal_trial_baru', [$startDate, $endDate])
            ->count();

        // ================== DATA BUKU INDUK ==================
$muridAktif = (clone $queryBukuInduk)
    ->where('tgl_masuk', '<=', $endDate)
    ->where(function ($q) use ($endDate) {
        $q->whereNull('tgl_keluar')
          ->orWhere('tgl_keluar', '>', $endDate);
    })
    ->get();

$muridBaru = (clone $queryBukuInduk)
    ->whereBetween('tgl_masuk', [$startDate, $endDate])
    ->get();

$muridKeluar = (clone $queryBukuInduk)
    ->whereBetween('tgl_keluar', [$startDate, $endDate])
    ->get();

$totalAktif = $muridAktif->count();

$muridBaruCount   = $muridBaru->count();
$muridKeluarCount = $muridKeluar->count();
$muridAktifCount  = $totalAktif;


        // ================== REALISASI ==================
        $muridBaruCount   = $muridBaru->count();
        $muridKeluarCount = $muridKeluar->count();
        $muridAktifCount  = $totalAktif;

        $muridDhuafa = $muridAktif->filter(fn($m) =>
            $m->note && str_contains(strtolower($m->note), 'dhuafa')
        )->count();

        $muridBNF = $muridAktif->filter(fn($m) =>
            $m->note && str_contains(strtolower($m->note), 'bnf')
        )->count();

        // ================== USIA ==================
        $dataUsia = [
            ['label' => '< 3 Tahun', 'jumlah' => $muridAktif->where('usia', '<', 3)->count()],
            ['label' => '3 Tahun',   'jumlah' => $muridAktif->where('usia', 3)->count()],
            ['label' => '4 Tahun',   'jumlah' => $muridAktif->where('usia', 4)->count()],
            ['label' => '5 Tahun',   'jumlah' => $muridAktif->where('usia', 5)->count()],
            ['label' => '6 Tahun',   'jumlah' => $muridAktif->where('usia', 6)->count()],
            ['label' => '> 6 Tahun', 'jumlah' => $muridAktif->where('usia', '>', 6)->count()],
        ];

        foreach ($dataUsia as &$item) {
            $item['persen'] = $totalAktif > 0
                ? round(($item['jumlah'] / $totalAktif) * 100)
                : 0;
        }

        // ================== LAMA BELAJAR ==================
        $extractBulan = fn($lama) => $lama ? (int) preg_replace('/[^0-9]/', '', $lama) : 0;

        $dataLama = [
            ['label' => '0 - 3 Bulan',   'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) <= 3)->count()],
            ['label' => '4 - 6 Bulan',   'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) >= 4 && $b <= 6)->count()],
            ['label' => '7 - 12 Bulan',  'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) >= 7 && $b <= 12)->count()],
            ['label' => '13 - 18 Bulan', 'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) >= 13 && $b <= 18)->count()],
            ['label' => '19 - 24 Bulan', 'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) >= 19 && $b <= 24)->count()],
            ['label' => '> 24 Bulan',    'jumlah' => $muridAktif->filter(fn($m) => ($b = $extractBulan($m->lama_bljr)) > 24)->count()],
        ];

        foreach ($dataLama as &$item) {
            $item['persen'] = $totalAktif > 0
                ? round(($item['jumlah'] / $totalAktif) * 100)
                : 0;
        }

        // ================== LAINNYA ==================
        $tahapPersiapan = $muridAktif->where('tahap', 'Persiapan')->count();
        $tahapLanjutan  = $muridAktif->where('tahap', 'Lanjutan')->count();

        $aktifKembali = $muridAktif->filter(fn($m) => str_contains(strtolower($m->note ?? ''), 'aktif kembali'))->count();
        $cuti         = $muridAktif->filter(fn($m) => str_contains(strtolower($m->note ?? ''), 'cuti'))->count();
        $garansi      = $muridAktif->filter(fn($m) => str_contains(strtolower($m->note ?? ''), 'garansi'))->count();
        $pindahan     = $muridAktif->filter(fn($m) => str_contains(strtolower($m->note ?? ''), 'pindahan'))->count();

        // ================== DROPDOWN ==================
        $bulanOptions = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $tahunOptions = range(now()->year - 5, now()->year + 1);

        $unitOptions = [];
        if ($isAdmin) {
            $unitOptions = DB::table('units')
                ->orderBy('bimba_unit')
                ->pluck('bimba_unit', 'bimba_unit')
                ->toArray();
            $unitOptions = ['' => 'Semua Unit'] + $unitOptions;
        }

        $namaUnitTerpilih = (!$isAdmin && $userUnit)
            ? " - {$userUnit}"
            : ($selectedUnit ? " - {$selectedUnit}" : " - Semua Unit");

        return view('buku_induk.statistik_ringkasan', compact(
            'bulanAwal','tahunAwal','bulanAkhir','tahunAkhir',
            'bulanOptions','tahunOptions',
            'trialBaru','muridBaruCount','muridKeluarCount','muridAktifCount',
            'muridDhuafa','muridBNF','dataUsia','dataLama',
            'tahapPersiapan','tahapLanjutan','aktifKembali','cuti','garansi','pindahan',
            'unitOptions','selectedUnit','namaUnitTerpilih','isAdmin','userUnit'
        ));
    }
}