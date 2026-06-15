<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CashAdvance;
use App\Models\CashAdvanceInstallment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashAdvanceController extends Controller
{
    public function index()
    {
        // Untuk sementara, semua user bisa lihat semua pengajuan
        // Nanti kalau mau pakai role admin, tinggal tambah pengecekan
        $cashAdvances = CashAdvance::latest()->paginate(10);

        return view('cash-advance.index', compact('cashAdvances'));
    }

    public function create()
    {
        return view('cash-advance.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'profile_id'     => 'required|exists:profiles,id',
        'bulan'          => 'required|integer|min:1|max:12',
        'tahun'          => 'required|integer|min:2000|max:2100',
        'nominal_pinjam' => 'required|numeric|min:100000',
        'jangka_waktu'   => 'required|integer|min:1|max:36',
        'keperluan'      => 'required|string|max:1000',
    ]);

    $profile = \App\Models\Profile::findOrFail($request->profile_id);

    $angsuran = $request->nominal_pinjam / $request->jangka_waktu;

    if ($angsuran < 300000) {
        return back()->withErrors([
            'jangka_waktu' => 'Angsuran per bulan minimal Rp 300.000 (Rp ' . number_format($angsuran) . ')'
        ])->withInput();
    }

    // Gabungkan bulan dan tahun menjadi format teks "Januari 2026"
    $tanggal = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1);
    $bulanPengajuan = $tanggal->locale('id')->translatedFormat('F Y'); // "Januari 2026"

    // Atau jika kamu ingin simpan format Y-m (lebih aman untuk query)
    // $bulanPengajuan = $tanggal->format('Y-m'); // "2026-01"

    CashAdvance::create([
        'user_id'         => auth()->id(),
        'nama'            => $profile->nama,
        'jabatan'         => $profile->jabatan ?? '-',
        'no_telepon'      => $profile->no_telp ?? '-',
        'bulan_pengajuan' => $bulanPengajuan,
        'nominal_pinjam'  => $request->nominal_pinjam,
        'jangka_waktu'    => $request->jangka_waktu,
        'keperluan'       => $request->keperluan,
        'status'          => 'pending',
    ]);

    return redirect()->route('cash-advance.index')
        ->with('success', 'Pengajuan cash advance berhasil diajukan.');
}

    public function show(CashAdvance $cashAdvance)
    {
        return view('cash-advance.show', compact('cashAdvance'));
    }

    // Approval
    public function approve(CashAdvance $cashAdvance)
{
    $angsuran = $cashAdvance->nominal_pinjam / $cashAdvance->jangka_waktu;

    if ($angsuran < 300000) {
        return back()->with('error', 'Tidak dapat disetujui karena angsuran < Rp 300.000');
    }

    // Hitung jatuh tempo pertama di luar transaction (supaya bisa dipakai di flash message)
    $bulanPengajuanTeks = trim($cashAdvance->bulan_pengajuan);

    $bulanMulai = null;
    try {
        $bulanMulai = \Carbon\Carbon::createFromFormat('F Y', $bulanPengajuanTeks, 'id')
            ->startOfMonth();
    } catch (\Throwable $e) {}

    if (!$bulanMulai) {
        $bulanMap = [
            'januari'   => 1, 'februari' => 2, 'maret'    => 3, 'april'   => 4,
            'mei'       => 5, 'juni'     => 6, 'juli'     => 7, 'agustus' => 8,
            'september' => 9, 'oktober'  => 10, 'november' => 11, 'desember'=> 12,
        ];

        if (preg_match('/^(\w+)\s+(\d{4})$/i', $bulanPengajuanTeks, $m)) {
            $namaBulan = strtolower(trim($m[1]));
            $tahun     = (int) $m[2];

            if (isset($bulanMap[$namaBulan])) {
                $bulanMulai = \Carbon\Carbon::create($tahun, $bulanMap[$namaBulan], 1)
                    ->startOfMonth();
            }
        }
    }

    if (!$bulanMulai) {
        $bulanMulai = now()->startOfMonth();
    }

    // Cicilan pertama = bulan berikutnya setelah pengajuan
    $jatuhTempoPertama = $bulanMulai->copy()->addMonth();

    // Format untuk pesan success
    $bulanMulaiText = $jatuhTempoPertama->locale('id')->translatedFormat('F Y');

    DB::transaction(function () use ($cashAdvance, $angsuran, $jatuhTempoPertama) {
        $cashAdvance->update([
            'status'             => 'approved',
            'angsuran_per_bulan' => $angsuran,
            'approved_by'        => Auth::id(),
            'approved_at'        => now(),
        ]);

        // Hapus cicilan lama
        $cashAdvance->installments()->delete();

        // Generate cicilan
        for ($i = 1; $i <= $cashAdvance->jangka_waktu; $i++) {
            CashAdvanceInstallment::create([
                'cash_advance_id'   => $cashAdvance->id,
                'cicilan_ke'        => $i,
                'jatuh_tempo'       => $jatuhTempoPertama->copy()->addMonths($i - 1),
                'nominal_angsuran'  => $angsuran,
                'status'            => 'Belum', // lebih jelas
            ]);
        }
    });

    return back()->with('success', "Pengajuan berhasil disetujui dan jadwal cicilan dibuat mulai dari {$bulanMulaiText} ({$cashAdvance->jangka_waktu} bulan).");
}

    public function reject(CashAdvance $cashAdvance)
    {
        $cashAdvance->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan telah ditolak.');
    }
    // Tambahkan ini di dalam CashAdvanceController

public function destroy(CashAdvance $cashAdvance)
{
    // === HANYA ADMIN YANG BOLEH HAPUS ===
    if (!auth()->user()->isAdminUser()) { // sesuaikan dengan method cek admin Anda
        return redirect()->route('cash-advance.index')
            ->with('error', 'Anda tidak memiliki hak untuk menghapus pengajuan cash advance.');
    }

    // Hapus cicilan terkait jika ada (jika sudah approved)
    if ($cashAdvance->status === 'approved') {
        $cashAdvance->installments()->delete();
    }

    // Hapus pengajuan utama
    $cashAdvance->delete();

    return redirect()->route('cash-advance.index')
        ->with('success', 'Pengajuan cash advance berhasil dihapus beserta cicilannya (jika ada).');
}
}