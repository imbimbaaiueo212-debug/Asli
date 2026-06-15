<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerimaan;
use App\Models\PettyCash;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FinancialSummaryController extends Controller
{
    public function index(Request $request)
    {
        // 1. BULAN & TAHUN
        $tahun = (int) $request->get('tahun', now()->year);
        $bulan = (int) $request->get('bulan', now()->month);

        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $bulanNama = $namaBulanIndo[$bulan];

        // 2. USER & ROLE
        $user = Auth::user();
        $isAdmin = in_array($user->role ?? '', ['admin', 'superadmin']);

        // Ambil bimba_unit langsung dari user (ini yang paling akurat untuk user biasa)
        $userBimbaUnit = trim($user->bimba_unit ?? '');

        if (!$isAdmin && empty($userBimbaUnit)) {
            abort(403, 'Akun Anda belum terkait dengan unit biMBA. Hubungi administrator.');
        }

        // Dropdown unit hanya untuk admin
        $units = $isAdmin ? Unit::orderBy('biMBA_unit')->get() : collect();

        $selectedUnitId = $request->get('unit_id');

        // 3. TENTUKAN UNIT UNTUK FILTER & TAMPILAN
        if ($isAdmin && $selectedUnitId) {
            // Admin pilih unit spesifik
            $selectedUnit = Unit::findOrFail($selectedUnitId);
            $filterBimbaUnit = trim($selectedUnit->biMBA_unit);
            $filterNoCabang  = $selectedUnit->no_cabang;
            $displayUnitName = $selectedUnit->biMBA_unit;
        } elseif ($isAdmin) {
            // Admin lihat semua unit
            $filterBimbaUnit = null;
            $filterNoCabang  = null;
            $displayUnitName = 'SEMUA UNIT (TOTAL)';
        } else {
            // User biasa: pakai bimba_unit dari akunnya langsung
            $filterBimbaUnit = $userBimbaUnit;
            $filterNoCabang  = null;
            $displayUnitName = $userBimbaUnit;
        }

        // Apakah ini unit pusat (SAPTA)?
        $isPusat = $filterBimbaUnit && str_contains(strtoupper($filterBimbaUnit), 'SAPTA');

        // 4. PENERIMAAN biMBA
        $queryPenerimaan = Penerimaan::where('tahun', $tahun)
            ->whereRaw('LOWER(TRIM(bulan)) = ?', [strtolower($bulanNama)]);

        if ($filterBimbaUnit) {
            $queryPenerimaan->where(function ($q) use ($filterNoCabang, $filterBimbaUnit) {
                if ($filterNoCabang) {
                    $q->where('no_cabang', $filterNoCabang);
                }
                $q->orWhereRaw('LOWER(TRIM(bimba_unit)) LIKE ?', ['%' . strtolower(trim($filterBimbaUnit)) . '%']);
            });
        }

        $penerimaan = $queryPenerimaan->get();

        $daftar     = $penerimaan->sum('daftar');
        $spp        = $penerimaan->sum('spp');
        $voucher    = $penerimaan->sum('voucher');
        $sppVhb     = $spp + $voucher;

        $penjualan = $penerimaan->sum(fn($r) =>
            ($r->kaos ?? 0) + ($r->kpk ?? 0) + ($r->sertifikat ?? 0) +
            ($r->stpb ?? 0) + ($r->tas ?? 0) + ($r->event ?? 0) + ($r->lain_lain ?? 0)
        );

        $totalPenerimaan = $daftar + $sppVhb + $penjualan;

        // 5. PETTY CASH - DENGAN CARRY OVER OTOMATIS (sama seperti di PettyCash index)

$pettySaldoAwal = 0;

// Jika unit pusat (SAPTA), baru hitung petty cash
if ($isPusat) {
    // Tentukan tanggal awal bulan ini
    $startOfMonth = Carbon::create($tahun, $bulan, 1);

    // Cari transaksi terakhir sebelum bulan ini (carry over)
    $lastBeforeMonth = PettyCash::where('kategori', '!=', 'Saldo Awal')
        ->where('tanggal', '<', $startOfMonth)
        ->orderBy('tanggal', 'desc')
        ->orderBy('id', 'desc')
        ->first();

    if ($lastBeforeMonth) {
        // Ada transaksi sebelum bulan ini → pakai saldo akhir itu
        $pettySaldoAwal = $lastBeforeMonth->saldo;
    } else {
        // Belum ada transaksi sebelumnya → pakai base 104.000
        $pettySaldoAwal = PettyCash::where('kategori', 'Saldo Awal')
            ->value('debit') ?? 0;
    }
}

// Transaksi petty cash di bulan ini
$queryTransaksi = PettyCash::where('kategori', '!=', 'Saldo Awal')
    ->whereYear('tanggal', $tahun)
    ->whereMonth('tanggal', $bulan);

$queryTransaksi->where(function ($q) {
    $q->whereNotNull('bimba_unit')
      ->where('bimba_unit', '!=', '');
});

$transaksiBulanIni = $queryTransaksi->get();

$pettyDebit  = $transaksiBulanIni->sum('debit');
$pettyKredit = $transaksiBulanIni->sum('kredit');
$pettySaldoAkhir = $pettySaldoAwal + $pettyDebit - $pettyKredit;

        // 6. DATA UNTUK VIEW
        $data = [
            'unit_name'                  => $displayUnitName,
            'month_year'                 => $bulanNama . ' ' . $tahun,

            // Penerimaan biMBA
            'penerimaan_bimba_daftar'    => $daftar,
            'penerimaan_bimba_spp_vhb'   => $sppVhb,
            'penerimaan_bimba_penjualan' => $penjualan,
            'penerimaan_bimba_total'     => $totalPenerimaan,

            // Petty Cash
            'petty_cash_saldo_awal'      => $pettySaldoAwal,
            'petty_cash_debit'           => $pettyDebit,
            'petty_cash_kredit'          => $pettyKredit,
            'petty_cash_saldo_akhir'     => $pettySaldoAkhir,

            // Lain-lain
            'lain_penyerahan_vhb'        => $voucher,
            'spp_bimba'                  => $spp,

            // Placeholder (untuk fitur mendatang)
            'penerimaan_english_daftar'     => 0,
            'penerimaan_english_spp_vhb'    => 0,
            'penerimaan_english_penjualan'  => 0,
            'penerimaan_english_total'      => 0,
            'spp_english'                   => 0,
            'imbalan_bimba'                 => 0,
            'imbalan_english'               => 0,
            'komisi_bimba'                  => 0,
            'komisi_english'                => 0,
            'lain_pemakaian_vhb'            => 0,
            'lain_spp_gabungan'             => 0,
            'lain_rp_salah_transfer'        => 0,
            'lain_o_murid'                  => 0,
        ];

        return view('financial.summary', compact(
            'data', 'tahun', 'bulan', 'units', 'selectedUnitId', 'isAdmin'
        ));
    }
}