<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerimaan;
use App\Models\PettyCash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RekapController extends Controller
{
    public function petty(Request $request)
{
    $perPage = max((int) $request->input('per_page', 50), 50);
    $start   = $request->input('start_date');
    $end     = $request->input('end_date');
    $unit    = $request->input('unit'); // Filter Bimba Unit

    // 1. Daftar unit untuk dropdown
    $unitList = \App\Models\Unit::withoutGlobalScopes()
        ->orderBy('biMBA_unit')
        ->get();

    // 2. Query Penerimaan — dengan filter unit & tanggal
    $penerimaanQuery = Penerimaan::query();

    if ($start) {
        $penerimaanQuery->whereDate('tanggal', '>=', Carbon::parse($start));
    }
    if ($end) {
        $penerimaanQuery->whereDate('tanggal', '<=', Carbon::parse($end));
    }
    if ($unit) {
        $penerimaanQuery->where('bimba_unit', $unit);
    }

    $penerimaan = (clone $penerimaanQuery)
        ->orderByDesc('tanggal')
        ->paginate($perPage)
        ->withQueryString();

    $allPenerimaan = (clone $penerimaanQuery)->get();

    // 3. Query Petty Cash — WAJIB ikut filter unit yang sama
    $pettyCashQuery = PettyCash::query();

    if ($start) {
        $pettyCashQuery->whereDate('tanggal', '>=', Carbon::parse($start));
    }
    if ($end) {
        $pettyCashQuery->whereDate('tanggal', '<=', Carbon::parse($end));
    }
    if ($unit) {
        $pettyCashQuery->where('bimba_unit', $unit); // Karena sudah ada kolom ini
    }

    $pettycash = $pettyCashQuery->orderBy('tanggal', 'asc')->get();

    // Saldo Awal — HARUS per unit juga
    $saldoAwalQuery = PettyCash::where('kategori', 'Saldo Awal');
    if ($unit) {
        $saldoAwalQuery->where('bimba_unit', $unit);
    }
    $saldoAwalRecord = $saldoAwalQuery->first();
    $saldoAwal = $saldoAwalRecord ? (float) $saldoAwalRecord->debit : 0;

    // Total debit & kredit (kecuali Saldo Awal)
    $pcOperasionalQuery = (clone $pettyCashQuery)->where('kategori', '!=', 'Saldo Awal');
    $totalDebit  = (float) $pcOperasionalQuery->sum('debit');
    $totalKredit = (float) $pcOperasionalQuery->sum('kredit');
    $saldoAkhir  = $saldoAwal + $totalDebit - $totalKredit;

    // Pengeluaran per kategori (exclude Petty Cash 500)
    $byKategori = $pcOperasionalQuery->get()
        ->groupBy('kategori')
        ->map(fn($group) => (float) $group->sum('kredit'))
        ->filter(fn($value, $key) => 
            $value > 0 && 
            !str_contains(strtolower($key), 'petty cash') && 
            !str_contains($key, '500')
        );

    // Ambil transaksi Petty Cash (kode 500) — ikut unit
    $pettyCashRow = $pettycash->first(fn($r) => 
        str_contains(strtolower($r->kategori ?? ''), 'petty cash') || 
        str_contains($r->kategori ?? '', '500')
    );

    $pettyCashAmount = $pettyCashRow
        ? (float) ($pettyCashRow->saldo ?? ($pettyCashRow->debit - $pettyCashRow->kredit))
        : null;

    // 4. Daftar kategori dengan kode (untuk urutan tampilan)
    $allKategoris = [
        '501 | Modul', '502 | Modul Mewarnai', '503 | Upah',
        '504 | Humas', '505 | Bagi Hasil', '506 | Sewa Tempat', '507 | Listrik',
        '508 | Air', '509 | Telepon', '510 | ATK, AMB, FC & Fax', '511 | Rumah Tangga',
        '512 | Iuran & Sumbangan', '513 | Transportasi', '514 | Kegiatan', '515 | Perawatan',
        '516 | Kaos', '517 | Sertifikat', '518 | Lain-lain', '519 | Gaji',
        '520 | Progresif', '521 | Bonus/THR',
    ];

    $kategoriOrder = [];
    foreach ($allKategoris as $cat) {
        if (preg_match('/^(\d+)\s*\|\s*(.+)$/u', trim($cat), $m)) {
            $kategoriOrder[$m[1]] = trim($m[2]);
        }
    }
    uksort($kategoriOrder, fn($a, $b) => (int)$a <=> (int)$b);

    // 5. Rekap Penerimaan (biMBA AIUEO) — dari data yang sudah difilter unit
    $itemColumns = [
        'Daftar' => 'daftar', 'Voucher' => 'voucher', 'SPP' => 'spp', 'Kaos' => 'kaos',
        'KPK' => 'kpk', 'Sertifikat' => 'sertifikat', 'STPB' => 'stpb', 'Tas' => 'tas',
        'BCABS' => 'bcabs', 'Event' => 'event', 'Lain-lain' => 'lain_lain',
    ];

    $masterItems = [
        ['kode' => '4-00001', 'label' => 'Daftar'],
        ['kode' => '4-00002', 'label' => 'Voucher'],
        ['kode' => '4-00003', 'label' => 'SPP (Cash/Transfer)'],
        ['kode' => '', 'label' => 'Cash'],
        ['kode' => '', 'label' => 'Transfer'],
        ['kode' => '', 'label' => 'EDC'],
        ['kode' => '4-00004', 'label' => 'Kaos'],
        ['kode' => '4-00005', 'label' => 'KPK'],
        ['kode' => '4-00006', 'label' => 'Sertifikat'],
        ['kode' => '4-00007', 'label' => 'STPB'],
        ['kode' => '4-00008', 'label' => 'Tas'],
        ['kode' => '4-00009', 'label' => 'BCABS'],
        ['kode' => '4-00010', 'label' => 'Event'],
        ['kode' => '4-00011', 'label' => 'Lain-lain'],
    ];

    $map = [];
    $methodTotals = ['CASH' => 0, 'TRANSFER' => 0];
    $normVia = fn($v) => mb_strtoupper(trim((string)$v));

    foreach ($allPenerimaan as $row) {
        $via = $normVia(data_get($row, 'via', data_get($row, 'metode_bayar', '')));
        $isVA = ($via === 'VA');

        $sppVal = (float) data_get($row, 'spp', 0);
        if ($sppVal > 0) {
            if ($via === 'CASH') $methodTotals['CASH'] += $sppVal;
            if ($via === 'TRANSFER') $methodTotals['TRANSFER'] += $sppVal;
        }

        foreach ($itemColumns as $label => $col) {
            if ($label === 'SPP') continue;
            $val = (float) data_get($row, $col, 0);
            if ($val == 0) continue;

            $map[$label] ??= ['va' => 0.0, 'non_va' => 0.0];
            if ($isVA) {
                $map[$label]['va'] += $val;
            } else {
                $map[$label]['non_va'] += $val;
            }
        }
    }

    // Total SPP gabungan
    $map['SPP (Cash/Transfer)'] = ['va' => 0.0, 'non_va' => $methodTotals['CASH'] + $methodTotals['TRANSFER']];

    // Hitung total VA & Non-VA
    $totalVA = 0;
    $totalNonVA = 0;
    foreach ($map as $vals) {
        $totalVA += $vals['va'] ?? 0;
        $totalNonVA += $vals['non_va'] ?? 0;
    }

    // Susun rekap untuk view
    $rekapAiueo = [];
    foreach ($masterItems as $m) {
        $label = $m['label'];
        $kode  = $m['kode'] ?? '';

        if (in_array($label, ['Cash', 'Transfer'])) {
            $key = mb_strtoupper($label);
            $sum = $methodTotals[$key] ?? 0;
            $rekapAiueo[] = ['kode' => $kode, 'type' => $label, 'va' => 0, 'non_va' => $sum];
            continue;
        }

        $va = $map[$label]['va'] ?? 0;
        $non_va = $map[$label]['non_va'] ?? 0;
        $rekapAiueo[] = ['kode' => $kode, 'type' => $label, 'va' => $va, 'non_va' => $non_va];
    }

    // Return view
    return view('rekap.petty.index', compact(
        'penerimaan', 'pettycash', 'perPage',
        'rekapAiueo', 'totalVA', 'totalNonVA',
        'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir',
        'byKategori', 'start', 'end', 'kategoriOrder', 'pettyCashAmount',
        'unitList', 'unit'
    ));
}
}
