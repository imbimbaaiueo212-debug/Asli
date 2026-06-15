<?php

namespace App\Http\Controllers;

use App\Models\PettyCash;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PettyCashController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal     = $request->input('tanggal_awal');
        $tanggalAkhir    = $request->input('tanggal_akhir');
        $filterTransaksi = $request->input('filter_transaksi');
        $filterUnit      = $request->input('bimba_unit');

        $user = auth()->user();
        $userUnit = $user->bimba_unit ?? $user->unit?->biMBA_unit ?? null;
        $activeUnit = $filterUnit ?: $userUnit;

        // === SALDO AWAL: Prioritas carry over, fallback ke Saldo Awal manual ===
        $saldoAwal = PettyCash::where('kategori', 'Saldo Awal')
            ->where('bimba_unit', $activeUnit)
            ->value('debit') ?? 0;

        if ($tanggalAwal && $activeUnit) {
            $lastBefore = PettyCash::where('bimba_unit', $activeUnit)
                ->where('kategori', '!=', 'Saldo Awal')
                ->where('tanggal', '<', $tanggalAwal)
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBefore) {
                $saldoAwal = $lastBefore->saldo; // carry over otomatis
            }
            // Jika tidak ada transaksi sebelumnya → tetap pakai 104.000 dari record Saldo Awal
        }

        // === QUERY TRANSAKSI DALAM PERIODE (exclude Saldo Awal) ===
        $query = PettyCash::where('kategori', '!=', 'Saldo Awal');

        if ($activeUnit) {
            $query->where('bimba_unit', $activeUnit);
        }

        if ($tanggalAwal && $tanggalAkhir) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        }

        if ($filterTransaksi === 'debit') {
            $query->where('debit', '>', 0);
        } elseif ($filterTransaksi === 'kredit') {
            $query->where('kredit', '>', 0);
        }

        $pettycash = $query->orderBy('tanggal', 'asc')
                           ->orderBy('id', 'asc')
                           ->get();

        $totalDebit  = $pettycash->sum('debit');
        $totalKredit = $pettycash->sum('kredit');
        $saldoAkhir  = $saldoAwal + $totalDebit - $totalKredit;

        $units = Unit::orderBy('biMBA_unit')->get();

        return view('pettycash.index', compact(
            'pettycash', 'saldoAwal', 'totalDebit', 'totalKredit', 'saldoAkhir',
            'tanggalAwal', 'tanggalAkhir', 'filterTransaksi', 'filterUnit', 'units', 'activeUnit'
        ));
    }

    public function create()
    {
        $kategoris = [
            '500 | Petty Cash',
            '501 | Modul',
            '502 | Modul Mewarnai',
            '503 | Upah',
            '504 | Humas',
            '505 | Bagi Hasil',
            '506 | Sewa Tempat',
            '507 | Listrik',
            '508 | Air',
            '509 | Telepon',
            '510 | ATK, AMB, FC & Fax',
            '511 | Rumah Tangga',
            '512 | Iuran & Sumbangan',
            '513 | Transportasi',
            '514 | Kegiatan',
            '515 | Perawatan',
            '516 | Kaos',
            '517 | Sertifikat',
            '518 | Lain-lain',
            '519 | Gaji',
            '520 | Progresif',
            '521 | Bonus/THR',
        ];

        $user = auth()->user();
        $bimbaKey = $user->bimba_unit ?? $user->unit?->biMBA_unit ?? null;

        $currentUnit = null;
        if ($bimbaKey) {
            $currentUnit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($bimbaKey))])->first();
        }

        return view('pettycash.create', compact('kategoris', 'currentUnit'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_bukti'   => 'required|unique:petty_cash,no_bukti',
            'tanggal'    => 'required|date',
            'kategori'   => 'required|string',
            'keterangan' => 'required|string',
            'debit'      => 'nullable|integer|min:0',
            'kredit'     => 'nullable|integer|min:0',
            'bukti'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $bimbaUnit = $user->bimba_unit ?? $user->unit?->biMBA_unit ?? null;

        // Base saldo dari record Saldo Awal
        $baseSaldo = PettyCash::where('kategori', 'Saldo Awal')
            ->where('bimba_unit', $bimbaUnit)
            ->value('debit') ?? 0;

        // Cari transaksi terakhir sebelum tanggal ini
        $lastTransaksi = PettyCash::where('bimba_unit', $bimbaUnit)
            ->where('kategori', '!=', 'Saldo Awal')
            ->where('tanggal', '<', $request->tanggal)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $lastSaldo = $lastTransaksi?->saldo ?? $baseSaldo;

        $debit  = $request->debit ?? 0;
        $kredit = $request->kredit ?? 0;

        if (str_starts_with($request->kategori, '500 | Petty Cash')) {
            $kredit = 0;
        }

        $saldoBaru = $lastSaldo + $debit - $kredit;

        $filename = null;
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('bukti', $filename, 'public');
        }

        PettyCash::create([
            'no_bukti'   => $request->no_bukti,
            'tanggal'    => $request->tanggal,
            'kategori'   => $request->kategori,
            'keterangan' => $request->keterangan,
            'debit'      => $debit,
            'kredit'     => $kredit,
            'saldo'      => $saldoBaru,
            'bukti'      => $filename,
            'bimba_unit' => $bimbaUnit,
            'no_cabang'  => $user->no_cabang ?? $user->unit?->no_cabang,
        ]);

        return redirect()->route('pettycash.index')->with('success', 'Transaksi berhasil ditambahkan');
    }

    public function edit(PettyCash $pettycash)
    {
        $kategoris = [
            '500 | Petty Cash',
            '501 | Modul',
            '502 | Modul Mewarnai',
            '503 | Upah',
            '504 | Humas',
            '505 | Bagi Hasil',
            '506 | Sewa Tempat',
            '507 | Listrik',
            '508 | Air',
            '509 | Telepon',
            '510 | ATK, AMB, FC & Fax',
            '511 | Rumah Tangga',
            '512 | Iuran & Sumbangan',
            '513 | Transportasi',
            '514 | Kegiatan',
            '515 | Perawatan',
            '516 | Kaos',
            '517 | Sertifikat',
            '518 | Lain-lain',
            '519 | Gaji',
            '520 | Progresif',
            '521 | Bonus/THR',
        ];

        $units = Unit::orderBy('biMBA_unit')->get();

        return view('pettycash.edit', compact('pettycash', 'kategoris', 'units'));
    }

    public function update(Request $request, PettyCash $pettycash)
    {
        $request->validate([
            'tanggal'    => 'required|date',
            'kategori'   => 'required|string',
            'keterangan' => 'required|string',
            'debit'      => 'nullable|integer|min:0',
            'kredit'     => 'nullable|integer|min:0',
            'bukti'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'bimba_unit' => 'nullable|string',
        ]);

        $bimbaUnit = $pettycash->bimba_unit ?? ($request->bimba_unit ? Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($request->bimba_unit))])->value('biMBA_unit') : $pettycash->bimba_unit);

        // Base saldo
        $baseSaldo = PettyCash::where('kategori', 'Saldo Awal')
            ->where('bimba_unit', $bimbaUnit)
            ->value('debit') ?? 0;

        // Saldo sebelum transaksi ini
        $previous = PettyCash::where('bimba_unit', $bimbaUnit)
            ->where('kategori', '!=', 'Saldo Awal')
            ->where('id', '<', $pettycash->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $previousSaldo = $previous?->saldo ?? $baseSaldo;

        $debit  = $request->debit ?? 0;
        $kredit = $request->kredit ?? 0;

        $newSaldo = $previousSaldo + $debit - $kredit;

        $filename = $pettycash->bukti;
        if ($request->hasFile('bukti')) {
            if ($filename && Storage::exists('public/bukti/' . $filename)) {
                Storage::delete('public/bukti/' . $filename);
            }
            $file = $request->file('bukti');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('bukti', $filename, 'public');
        }

        $noCabang = $pettycash->no_cabang;
        if ($request->filled('bimba_unit')) {
            $unitModel = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($request->bimba_unit))])->first();
            if ($unitModel) {
                $bimbaUnit = $unitModel->biMBA_unit;
                $noCabang = $unitModel->no_cabang;
            }
        }

        $pettycash->update([
            'tanggal'     => $request->tanggal,
            'kategori'    => $request->kategori,
            'keterangan'  => $request->keterangan,
            'debit'       => $debit,
            'kredit'      => $kredit,
            'saldo'       => $newSaldo,
            'bukti'       => $filename,
            'bimba_unit'  => $bimbaUnit,
            'no_cabang'   => $noCabang,
        ]);

        return redirect()->route('pettycash.index')->with('success', 'Transaksi berhasil diperbarui');
    }

    public function destroy(PettyCash $pettycash)
    {
        if ($pettycash->bukti && Storage::exists('public/bukti/' . $pettycash->bukti)) {
            Storage::delete('public/bukti/' . $pettycash->bukti);
        }

        $pettycash->delete();

        return redirect()->route('pettycash.index')->with('success', 'Transaksi berhasil dihapus');
    }

    public function updateSaldoAwal(Request $request)
{
    // Hanya admin yang boleh
    if (!auth()->user()->isAdminUser()) { // sesuaikan dengan method cek admin Anda
        return redirect()->back()->with('error', 'Anda tidak memiliki hak akses untuk mengubah Saldo Awal.');
    }

    $request->validate([
        'bimba_unit' => 'required|string|exists:units,biMBA_unit',
        'saldo_awal' => 'required|numeric|min:0',
    ]);

    $bimbaUnit = $request->bimba_unit;
    $saldo = $request->saldo_awal;

    // Cari no_cabang dari unit
    $noCabang = Unit::where('biMBA_unit', $bimbaUnit)->value('no_cabang');

    PettyCash::updateOrCreate(
        [
            'kategori'   => 'Saldo Awal',
            'bimba_unit' => $bimbaUnit,
        ],
        [
            'no_bukti'    => 'SALDO-AWAL-' . strtoupper(str_replace(' ', '-', $bimbaUnit)), // contoh: SALDO-AWAL-GRIYA-PESONA-MADANI
            'tanggal'     => now(),
            'keterangan'  => 'Saldo awal petty cash (ditetapkan oleh Admin)',
            'debit'       => $saldo,
            'kredit'      => 0,
            'saldo'       => $saldo,
            'bukti'       => null,
            'no_cabang'   => $noCabang,
        ]
    );

    return redirect()->back()->with('success', 'Saldo Awal untuk unit ' . $bimbaUnit . ' berhasil diperbarui.');
}
}