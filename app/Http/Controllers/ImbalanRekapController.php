<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImbalanRekap;
use App\Models\AbsensiVolunteer;
use App\Models\Adjustment;
use App\Models\CashAdvanceInstallment;
use App\Models\CashAdvance;
use App\Models\DurasiKegiatan;
use App\Models\Lembur;
use App\Models\ProfileHistory;
use App\Models\Ktr;
use App\Models\Profile;
use App\Models\PotonganTunjangan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Events\ProfileUpdated; // Tambahkan ini di atas class
use Illuminate\Support\Facades\Cache;
use Throwable;

class ImbalanRekapController extends Controller
{
    public function index(Request $request)
{
    /* ======================================================
     * 1. FILTER PARAM + DEFAULT LOGIKA PERIODE
     * ====================================================== */
    $start_bulan = $request->get('start_bulan');
    $start_tahun = $request->get('start_tahun');
    $end_bulan   = $request->get('end_bulan', $start_bulan);
    $end_tahun   = $request->get('end_tahun', $start_tahun);
    $bimba_unit  = $request->get('bimba_unit');
    $nama        = $request->get('nama');
    $showAll     = $request->has('all');

    Carbon::setLocale('id');

    // Jika tidak ada filter, default ke bulan SEBELUMNYA (karena periode 26 s.d 25)
    if (!$start_bulan || !$start_tahun) {
        $default = now()->subMonth(); // Bulan lalu
        $start_bulan = $default->format('m');
        $start_tahun = $default->format('Y');
        $end_bulan   = $start_bulan;
        $end_tahun   = $start_tahun;
    }

    Log::info('=== ImbalanRekap INDEX FILTER ===', [
        'start_bulan' => $start_bulan,
        'start_tahun' => $start_tahun,
        'end_bulan'   => $end_bulan,
        'end_tahun'   => $end_tahun,
    ]);

    /* ======================================================
     * 2. RANGE TANGGAL
     * ====================================================== */
    try {
        $startDate = Carbon::createFromFormat('Y-m', "$start_tahun-$start_bulan")->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', "$end_tahun-$end_bulan")->endOfMonth();
    } catch (\Exception $e) {
        $startDate = now()->subMonth()->startOfMonth();
        $endDate   = now()->subMonth()->endOfMonth();
    }

    /* ======================================================
     * 3. LIST BULAN LABELS
     * ====================================================== */
    $bulanLabels = [];
    $tmp = $startDate->copy();
    while ($tmp <= $endDate) {
        $bulanLabels[] = $tmp->translatedFormat('F Y');
        $tmp->addMonth();
    }

    Log::info('Bulan Labels untuk filter:', $bulanLabels);

    /* ======================================================
     * 4. LABEL PERIODE (untuk tampilan)
     * ====================================================== */
    $periodeLabel = $startDate->translatedFormat('F Y');
    if (!$startDate->isSameMonth($endDate)) {
        $periodeLabel .= ' — ' . $endDate->translatedFormat('F Y');
    }

    /* ======================================================
     * 5. CEK ADMIN
     * ====================================================== */
    $user = Auth::user();
    $isAdmin = $user && ($user->role === 'admin' || ($user->is_admin ?? false));

    /* ======================================================
     * 6. UNIT OPTIONS
     * ====================================================== */
    $unitOptions = $isAdmin
        ? \App\Models\Unit::orderBy('biMBA_unit')->pluck('biMBA_unit')
        : collect();

    /* ======================================================
     * 7. NAMA OPTIONS
     * ====================================================== */
    $namaOptionsQuery = \App\Models\Profile::query()
        ->select('profiles.nama')
        ->join('imbalan_rekaps', 'profiles.nama', '=', 'imbalan_rekaps.nama')
        ->distinct()
        ->orderBy('profiles.nama');

    if (!$showAll && !empty($bulanLabels)) {
        $namaOptionsQuery->whereIn('imbalan_rekaps.bulan', $bulanLabels);
    }
    if ($bimba_unit) {
        $namaOptionsQuery->where('profiles.biMBA_unit', $bimba_unit);
    }

    $namaOptions = $namaOptionsQuery->pluck('nama');

    /* ======================================================
     * 8. QUERY UTAMA (FILTER LEBIH AMAN)
     * ====================================================== */
    $query = \App\Models\ImbalanRekap::query()
        ->with('profile')
        ->join('profiles', 'imbalan_rekaps.nama', '=', 'profiles.nama')
        ->select(
            'imbalan_rekaps.*',
            'profiles.status_karyawan as profile_status',
            'profiles.tgl_masuk as profile_tgl_masuk',
            'profiles.masa_kerja as profile_masa_kerja'
        )
        ->orderByRaw("CAST(profiles.nik AS UNSIGNED) ASC")
        ->orderBy('imbalan_rekaps.nama', 'ASC');

    if (!$showAll && !empty($bulanLabels)) {
        $query->where(function($q) use ($bulanLabels) {
            $q->whereIn('imbalan_rekaps.bulan', $bulanLabels)
              ->orWhereIn(DB::raw("TRIM(imbalan_rekaps.bulan)"), $bulanLabels);
        });
    }

    if ($bimba_unit) {
        $query->where('profiles.biMBA_unit', $bimba_unit);
    }
    if ($nama) {
        $query->where('imbalan_rekaps.nama', $nama);
    }

    /* ======================================================
     * 9. AMBIL DATA
     * ====================================================== */
    $rekaps = $showAll ? $query->get() : $query->paginate(50);

    Log::info('Data yang ditampilkan:', [
        'total' => $rekaps->count(),
        'bulan_pertama' => $rekaps->first()?->bulan ?? 'TIDAK ADA'
    ]);

    /* ======================================================
 * 10. FIX DATA
 * ====================================================== */
foreach ($rekaps as $r) {
    // Prioritas status: imbalan_rekaps → profile
    $statusFinal = trim($r->status ?? $r->profile_status ?? '');

    $r->status = !empty($statusFinal) ? $statusFinal : '-';

    // Masa Kerja
    if (!empty($r->masa_kerja)) {
        $r->masa_kerja_formatted = $this->formatMasaKerja((int)$r->masa_kerja);
    } elseif (!empty($r->profile_masa_kerja)) {
        $r->masa_kerja_formatted = $this->formatMasaKerja((int)$r->profile_masa_kerja);
    } elseif (!empty($r->profile_tgl_masuk)) {
        $r->masa_kerja_formatted = $this->hitungMasaKerja($r->profile_tgl_masuk);
    } else {
        $r->masa_kerja_formatted = '-';
    }

    // Khusus Magang
    if (strtolower($r->status) === 'magang') {
        $r->imbalan_pokok = 0;
        $r->total_imbalan = 0;
        $r->yang_dibayarkan = 0;
    }
}

    /* ======================================================
     * 11. PROSES ADJUSTMENT
     * ====================================================== */
    if ($rekaps->isNotEmpty()) {
        $namaList = $rekaps->pluck('nama')->map(fn($n) => strtoupper(trim($n)))->unique()->toArray();

        $adjustments = Adjustment::whereIn(DB::raw('TRIM(UPPER(nama))'), $namaList)
            ->whereBetween('month', [$startDate->month, $endDate->month])
            ->whereBetween('year', [$startDate->year, $endDate->year])
            ->get()
            ->groupBy(fn($adj) => strtoupper(trim($adj->nama)));

        foreach ($rekaps as $r) {
            $namaUpper = strtoupper(trim($r->nama));
            $adjList = $adjustments->get($namaUpper, collect());

            $totalKekuranganAdj = 0;
            $totalKelebihanAdj  = 0;
            $ketKekurangan = [];
            $ketKelebihan  = [];

            foreach ($adjList as $adj) {
                $nominal = (float) ($adj->nominal ?? 0);
                $type    = strtolower(trim($adj->type ?? ''));

                if (str_contains($type, 'tambah') || $type === 'tambahan') {
                    $totalKekuranganAdj += $nominal;
                    if (!empty($adj->keterangan)) $ketKekurangan[] = $adj->keterangan;
                } elseif (str_contains($type, 'potong') || $type === 'potongan') {
                    $totalKelebihanAdj += $nominal;
                    if (!empty($adj->keterangan)) $ketKelebihan[] = $adj->keterangan;
                }
            }

            $r->total_kekurangan_adj = $totalKekuranganAdj;
            $r->total_kelebihan_adj  = $totalKelebihanAdj;
            $r->keterangan_kekurangan_adj = $ketKekurangan ? implode(' | ', $ketKekurangan) : null;
            $r->keterangan_kelebihan_adj  = $ketKelebihan ? implode(' | ', $ketKelebihan) : null;

            $r->kekurangan = ($r->kekurangan ?? 0) + $totalKekuranganAdj;
            $r->kelebihan  = ($r->kelebihan ?? 0) + $totalKelebihanAdj;

                        // ==================== HITUNG TOTAL (SUDAH TERMASUK LEMBUR) ====================
            $r->total_imbalan = 
                ($r->imbalan_pokok ?? 0) +
                ($r->lembur_nominal ?? 0) +           // ← TAMBAHKAN INI
                ($r->imbalan_lainnya ?? 0) +
                ($r->insentif_mentor ?? 0) +
                ($r->tambahan_transport ?? 0) +
                ($r->kekurangan ?? 0);

            $r->yang_dibayarkan = 
                $r->total_imbalan +
                ($r->jumlah_bagi_hasil ?? 0) -
                ($r->kelebihan ?? 0) -
                ($r->cicilan ?? 0);
        }
    }

    if (!$showAll) {
        $rekaps->appends($request->all());
    }

    return view('imbalan_rekap.index', compact(
        'rekaps',
        'periodeLabel',
        'start_bulan',
        'start_tahun',
        'end_bulan',
        'end_tahun',
        'bimba_unit',
        'nama',
        'namaOptions',
        'unitOptions',
        'startDate',
        'endDate',
        'showAll'
    ));
}

    private function hitungMasaKerja($tanggalMasuk)
    {
        if (!$tanggalMasuk)
            return null;
        try {
            $mulai = Carbon::parse($tanggalMasuk);
            $sekarang = Carbon::now();
            $tahun = $sekarang->diffInYears($mulai);
            $bulan = $sekarang->copy()->subYears($tahun)->diffInMonths($mulai);

            if ($tahun > 0 && $bulan > 0)
                return "$tahun tahun $bulan bulan";
            if ($tahun > 0)
                return "$tahun tahun";
            if ($bulan > 0)
                return "$bulan bulan";
            return "0 bulan";
        } catch (\Exception $e) {
            return null;
        }
    }

    public function truncate()
    {
        ImbalanRekap::truncate();
        return redirect()->route('imbalan_rekap.index')->with('success', 'Semua data ImbalanRekap dihapus.');
    }

    private function formatMasaKerja($bulan)
    {
        if ($bulan === null || $bulan < 0)
            return '-';
        $tahun = intdiv($bulan, 12);
        $sisaBulan = $bulan % 12;
        $parts = [];
        if ($tahun > 0)
            $parts[] = "$tahun tahun";
        if ($sisaBulan > 0)
            $parts[] = "$sisaBulan bulan";
        return $parts ? implode(' ', $parts) : '0 bulan';
    }

    public function refresh(Request $request)
{
    $start_bulan = $request->get('start_bulan', now()->format('m'));
    $start_tahun = $request->get('start_tahun', now()->format('Y'));

    try {
        $carbon = Carbon::createFromFormat('Y-m', "$start_tahun-$start_bulan");
        $periodeLabel = $carbon->locale('id')->translatedFormat('F Y');
    } catch (\Exception $e) {
        $periodeLabel = now()->locale('id')->translatedFormat('F Y');
    }

    DB::beginTransaction();

    try {

        // ❌ JANGAN DELETE DATA
        // ImbalanRekap::where('bulan', $periodeLabel)->delete();

        // regenerate (akan update jika sudah ada)
        $result = $this->createRekapsForPeriode($periodeLabel);

        DB::commit();

        return redirect()->route('imbalan_rekap.index', $request->query())
            ->with('success', "Refresh berhasil untuk periode {$periodeLabel}");

    } catch (\Throwable $e) {

        DB::rollBack();

        return redirect()->route('imbalan_rekap.index')
            ->with('error', 'Gagal refresh: ' . $e->getMessage());
    }
}
    public function updateInline(Request $request)
{
    $request->validate([
        'id' => 'required|exists:imbalan_rekaps,id',
    ]);

    $rekap = \App\Models\ImbalanRekap::findOrFail($request->id);

    // ======================================================
    // 1. Ambil SEMUA field yang dikirim (bisa > 1 field)
    // ======================================================
    $fields = $request->except(['id', '_token']);

    if (empty($fields)) {
        return response()->json([
            'success' => false,
            'message' => 'Tidak ada field yang dikirim'
        ], 422);
    }

    // ======================================================
    // 2. Set semua field ke model
    // ======================================================
    foreach ($fields as $field => $value) {
        // Normalisasi nilai kosong
        if ($value === '') {
            $value = null;
        }
        $rekap->$field = $value;
    }

    // ======================================================
    // 3. Logika khusus field tertentu
    // ======================================================

    // AT HARI → hitung tambahan transport
    if (array_key_exists('at_hari', $fields)) {
        $hari = (int) ($rekap->at_hari ?? 0);
        $rekap->tambahan_transport = $hari * 24000;
    }

    // ======================================================
    // 4. Hitung ulang TOTAL IMBALAN
    // ======================================================
    $rekap->total_imbalan =
        ($rekap->imbalan_pokok ?? 0) +
        ($rekap->imbalan_lainnya ?? 0) +
        ($rekap->insentif_mentor ?? 0) +
        ($rekap->tambahan_transport ?? 0) +
        ($rekap->kekurangan ?? 0);

    // ======================================================
    // 5. Hitung ulang YANG DIBAYARKAN (FINAL)
    // ======================================================
    $rekap->yang_dibayarkan =
        $rekap->total_imbalan +
        ($rekap->jumlah_bagi_hasil ?? 0) -
        ($rekap->kelebihan ?? 0) -
        ($rekap->cicilan ?? 0);

    // ======================================================
    // 6. Bersihkan keterangan cicilan jika cicilan = 0
    // ======================================================
    if (
        array_key_exists('cicilan', $fields) ||
        array_key_exists('keterangan_cicilan', $fields)
    ) {
        if (($rekap->cicilan ?? 0) == 0) {
            $rekap->keterangan_cicilan = null;

            if ($rekap->catatan) {
                $parts = explode(' | ', $rekap->catatan);

                $parts = array_filter($parts, function ($part) {
                    $part = strtolower(trim($part));
                    return !str_contains($part, 'cicilan cash advance')
                        && !str_contains($part, 'angsuran ke');
                });

                $rekap->catatan = $parts
                    ? implode(' | ', $parts)
                    : null;
            }
        }
    }

    // ======================================================
    // 7. Simpan perubahan ke imbalan_rekaps
    // ======================================================
    $rekap->save();

    // ======================================================
// 8. UPDATE STATUS CICILAN – VERSI AMAN & SESUAI DATABASE
// ======================================================
if (array_key_exists('installment_id', $fields)) {
    $newInstallmentId = $rekap->installment_id;

    // Ambil nama relawan
    $namaRelawan = trim($rekap->nama ?? $rekap->profile?->nama ?? '');

    if ($namaRelawan) {
        // 1. Reset semua cicilan relawan ini yang sebelumnya lunas via potong gaji
        \App\Models\CashAdvanceInstallment::whereHas('cashAdvance', function ($q) use ($namaRelawan) {
            $q->whereRaw('TRIM(UPPER(nama)) = ?', [strtoupper($namaRelawan)]);
        })
        ->where('keterangan', 'like', '%Dipotong via Imbalan Relawan%')
        ->update([
            'status'         => 'belum',   // <-- SESUAIKAN DENGAN NILAI ASLI
            'sudah_dibayar'  => 0,
            'tanggal_bayar'  => null,
            'keterangan'     => null,
        ]);
    }

    // 2. Jika ada cicilan baru dipilih → tandai lunas
    if ($newInstallmentId) {
        $installment = \App\Models\CashAdvanceInstallment::find($newInstallmentId);

        if ($installment) {
            $periode = \Carbon\Carbon::now()->locale('id')->translatedFormat('F Y');
            if (request()->has('start_bulan') && request()->has('start_tahun')) {
                $periode = \Carbon\Carbon::createFromFormat('m-Y', request('start_bulan') . '-' . request('start_tahun'))
                    ->locale('id')
                    ->translatedFormat('F Y');
            }

            $installment->update([
                'status'         => 'lunas',   // <-- SESUAIKAN DENGAN NILAI ASLI
                'sudah_dibayar'  => 1,
                'tanggal_bayar'  => \Carbon\Carbon::now(),
                'keterangan'     => 'Dipotong via Imbalan Relawan periode ' . $periode,
            ]);
        }
    }
}
    // ======================================================
    // 9. Response untuk frontend (real-time update)
    // ======================================================
    return response()->json([
        'success'             => true,
        'message'             => 'Data berhasil diperbarui',
        'total_imbalan'       => $rekap->total_imbalan,
        'yang_dibayarkan'     => $rekap->yang_dibayarkan,
        'tambahan_transport'  => $rekap->tambahan_transport ?? 0,
        'at_hari'             => $rekap->at_hari ?? '',
        'cicilan'             => $rekap->cicilan ?? 0,
        'keterangan_cicilan'  => $rekap->keterangan_cicilan ?? '',
        'catatan'             => $rekap->catatan ?? '',
    ]);
}

    public function pdf(Request $request, $id)
{
    $rekap = ImbalanRekap::findOrFail($id);
    $profile = Profile::where('nama', $rekap->nama)->first();

    /* ======================================================
     * 🔥 FIX STATUS / KATEGORI
     * ====================================================== */
    $statusAktual   = strtolower(trim($profile->status_karyawan ?? ''));
    $kategoriAktual = strtolower(trim($profile->kategori ?? ''));

    if ($kategoriAktual && !str_contains($kategoriAktual, 'magang')) {
        $statusFinal = $kategoriAktual;
    } else {
        $statusFinal = $statusAktual;
    }

    $rekap->status_karyawan = $statusFinal;
    $rekap->kategori        = $statusFinal;

    $isMagang = str_contains($statusFinal, 'magang');

    /* ======================================================
     * PERIODE
     * ====================================================== */
    $periodeValue = $request->query('periode');

    $bulan = $periodeValue
        ? Carbon::createFromFormat('Y-m', $periodeValue)->format('Y-m')
        : ($rekap->bulan
            ? Carbon::createFromFormat('F Y', $rekap->bulan, 'id')->format('Y-m')
            : Carbon::now()->subMonth()->format('Y-m'));

    /* ======================================================
     * POTONGAN
     * ====================================================== */
    $potongan = null;
    if ($rekap->pendapatan_id) {
        $potongan = PotonganTunjangan::where('pendapatan_id', $rekap->pendapatan_id)
            ->where('bulan', $bulan)
            ->first();
    }
    if (!$potongan) {
        $potongan = PotonganTunjangan::where('nama', $rekap->nama)
            ->where('bulan', $bulan)
            ->first();
    }
    if (!$potongan && $profile?->nik) {
        $potongan = PotonganTunjangan::where('nik', $profile->nik)
            ->where('bulan', $bulan)
            ->first();
    }

    /* ======================================================
     * ADJUSTMENT
     * ====================================================== */
    $totalKekuranganAdj = 0;
    $totalKelebihanAdj = 0;
    $keteranganKekuranganAdj = null;
    $keteranganKelebihanAdj = null;

    try {
        $carbonPeriode = Carbon::createFromFormat('Y-m', $bulan);
        $month = $carbonPeriode->month;
        $year = $carbonPeriode->year;

        $adjustments = Adjustment::whereRaw('TRIM(UPPER(nama)) = ?', [strtoupper(trim($rekap->nama))])
            ->where('month', $month)
            ->where('year', $year)
            ->get();

        foreach ($adjustments as $adj) {
            $nominal = (float) $adj->nominal;
            $type = strtolower(trim($adj->type ?? ''));

            if (str_contains($type, 'tambah') || $type === 'tambahan') {
                $totalKekuranganAdj += $nominal;
                $keteranganKekuranganAdj = $keteranganKekuranganAdj 
                    ? $keteranganKekuranganAdj . ' | ' . trim($adj->keterangan ?? '')
                    : trim($adj->keterangan ?? '');
            } elseif (str_contains($type, 'potong') || $type === 'potongan') {
                $totalKelebihanAdj += $nominal;
                $keteranganKelebihanAdj = $keteranganKelebihanAdj 
                    ? $keteranganKelebihanAdj . ' | ' . trim($adj->keterangan ?? '')
                    : trim($adj->keterangan ?? '');
            }
        }
    } catch (\Exception $e) {
        Log::warning("Gagal ambil adjustment PDF: " . $e->getMessage());
    }

    /* ======================================================
     * 🔥 HITUNG LEMBUR (DITAMBAHKAN)
     * ====================================================== */
    $totalJamLembur = $rekap->lembur_jam ?? 0;
    $totalNominalLembur = $rekap->lembur_nominal ?? 0;

    /* ======================================================
     * HITUNG IMBALAN (DITAMBAHKAN LEMBUR)
     * ====================================================== */
    $pokok      = $rekap->imbalan_pokok ?? 0;
    $lainnya    = $rekap->imbalan_lainnya ?? 0;
    $insentif   = $rekap->insentif_mentor ?? 0;
    $transport  = $rekap->tambahan_transport ?? 0;

    $totalPendapatan = 
        $pokok +
        $lainnya +
        $insentif +
        $transport +
        $totalKekuranganAdj +
        $totalNominalLembur;   // ← LEMBUR DITAMBAHKAN

    /* ======================================================
     * POTONGAN (DISPLAY ONLY)
     * ====================================================== */
    $totalPotonganTetap = 0;
    if ($potongan) {
        $totalPotonganTetap = 
            ($potongan->sakit ?? 0) +
            ($potongan->izin ?? 0) +
            ($potongan->alpa ?? 0) +
            ($potongan->tidak_aktif ?? 0) +
            ($potongan->cash_advance_nominal ?? 0) +
            ($potongan->lainnya ?? 0);
    }

    $cicilanNilai = $rekap->cicilan ?? 0;
    $cicilanKeterangan = $rekap->keterangan_cicilan ?? 'Cicilan Cash Advance';

    $totalPotongan = $totalPotonganTetap + $cicilanNilai + $totalKelebihanAdj;

    /* ======================================================
     * YANG DIBAYARKAN
     * ====================================================== */
    $yangDibayarkan = 
        $totalPendapatan +
        ($rekap->jumlah_bagi_hasil ?? 0) -
        ($rekap->kelebihan ?? 0) -
        $cicilanNilai;

    /* ======================================================
     * HANDLE MAGANG
     * ====================================================== */
    if ($isMagang) {
        $rekap->imbalan_pokok = 0;
        $totalNominalLembur = 0;
        $totalPendapatan = 0;
        $yangDibayarkan = 0;
    }

    /* ======================================================
     * UNIT & DATA LAIN
     * ====================================================== */
    $unitName = $rekap->bimba_unit ?? $rekap->biMBA_unit ?? $profile?->bimba_unit ?? $profile?->unit ?? '-';
    $noCabang = $rekap->no_cabang ?? $profile?->no_cabang ?? null;
    $unitDisplay = ($noCabang && $unitName !== '-') ? $noCabang . ' - ' . strtoupper($unitName) : strtoupper($unitName);

    $periodeLabel = $periodeValue
        ? Carbon::createFromFormat('Y-m', $periodeValue)->locale('id')->translatedFormat('F Y')
        : ($rekap->bulan ?? Carbon::now()->subMonth()->locale('id')->translatedFormat('F Y'));

    $tanggalMasukFormatted = $profile?->tgl_masuk
        ? Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y')
        : '-';

    /* ======================================================
     * DATA VIEW
     * ====================================================== */
    $data = [
        'rekap'                   => $rekap,
        'profile'                 => $profile,
        'masaKerja'               => $this->formatMasaKerja($profile?->masa_kerja ?? $rekap->masa_kerja ?? 0),
        'periode'                 => $periodeLabel,
        'periodeValue'            => $periodeValue,
        'tanggalSekarang'         => Carbon::now()->translatedFormat('d F Y'),
        'tanggalMasukFormatted'   => $tanggalMasukFormatted,
        'biMBA_unit'              => $unitDisplay,
        'no_cabang'               => $noCabang ?? '-',
        'noRekening'              => $profile?->no_rekening ?? '-',
        'bank'                    => $profile?->bank ?? '-',
        'atasNama'                => $profile?->nama_rekening ?? $rekap->nama ?? '-',
        'potongan'                => $potongan,
        'cicilanNilai'            => $cicilanNilai,
        'cicilanKeterangan'       => $cicilanKeterangan,
        'totalPendapatan'         => $totalPendapatan,
        'totalPotongan'           => $totalPotongan,
        'yangDibayarkan'          => $yangDibayarkan,
        'totalKekuranganAdj'      => $totalKekuranganAdj,
        'totalKelebihanAdj'       => $totalKelebihanAdj,
        'keteranganKekuranganAdj' => $keteranganKekuranganAdj,
        'keteranganKelebihanAdj'  => $keteranganKelebihanAdj,

        // LEMBUR UNTUK PDF
        'totalJamLembur'          => $totalJamLembur,
        'totalNominalLembur'      => $totalNominalLembur,

        'isPdf'                   => true,
    ];

    $pdf = Pdf::loadView('imbalan_rekap.slip_pdf', $data)
        ->setPaper('a5', 'landscape');

    $fileName = 'slip-imbalan-' . preg_replace('/[^A-Za-z0-9\-]/', '-', strtolower($rekap->nama ?? 'rekap')) . '-' . $periodeLabel . '.pdf';

    return $pdf->stream($fileName);
}


    public function generateMonth(Request $request)
{
    // Ambil periode dari query string (GET), sama seperti refresh
    $start_bulan = $request->get('start_bulan', now()->format('m'));
    $start_tahun = $request->get('start_tahun', now()->format('Y'));

    try {
        $carbon = Carbon::createFromFormat('Y-m', "$start_tahun-$start_bulan");
        $labelBulan = $carbon->locale('id')->translatedFormat('F Y');
    } catch (\Exception $e) {
        $labelBulan = now()->locale('id')->translatedFormat('F Y');
    }

    $result = $this->createRekapsForPeriode($labelBulan);

    return redirect()
        ->route('imbalan_rekap.index', $request->query())
        ->with('success', "Berhasil generate ulang {$result['created']} data untuk periode {$labelBulan}");
}

      public function createRekapsForPeriode(string $labelBulan): array
{
    $created = 0;
    $updated = 0;
    $errors  = [];

    $labelBulan = trim(preg_replace('/\s+/', ' ', $labelBulan));

    Log::info("=== MULAI GENERATE REKAP: {$labelBulan} ===");

    $normalize = fn($str) => strtoupper(preg_replace('/\s+/', '', $str ?? ''));

    $extractRbNumber = function ($val, $default = 30) {
        if (!empty($val) && preg_match('/(\d+)/', $val, $m)) {
            return (int)$m[1];
        }
        return $default;
    };

    // ================= PARSING BULAN =================
    $monthMap = [
        'Januari'=>'01','Februari'=>'02','Maret'=>'03','April'=>'04',
        'Mei'=>'05','Juni'=>'06','Juli'=>'07','Agustus'=>'08',
        'September'=>'09','Oktober'=>'10','November'=>'11','Desember'=>'12'
    ];

    $split = explode(' ', $labelBulan);
    $bulanFormatYm = ($split[1] ?? date('Y')) . '-' . ($monthMap[$split[0]] ?? date('m'));

    // ================= DEFINISI PERIODE =================
    $year  = (int) ($split[1] ?? date('Y'));
    $month = (int) ($monthMap[$split[0]] ?? date('m'));

    $startDate = Carbon::create($year, $month, 1)->subMonth()->day(26)->startOfDay();
    $endDate   = Carbon::create($year, $month, 25)->endOfDay();

    Log::info("Periode Tanggal: {$startDate->format('Y-m-d')} s.d {$endDate->format('Y-m-d')}");

    // Sync Potongan
    try {
        app(\App\Http\Controllers\PotonganTunjanganController::class)
            ->runSyncFromAbsensi($bulanFormatYm);
    } catch (\Throwable $e) {
        Log::warning("Gagal sync potongan: " . $e->getMessage());
    }

    $profiles = Profile::orderBy('nama')->get();
    $histories = ProfileHistory::where('periode', $bulanFormatYm)
                ->get()
                ->keyBy('profile_id');

    Log::info("Total Profile diproses: " . $profiles->count());

    $ktrList = Ktr::all();

    $rbConfig = [
        60 => ['min_jam' => 220, 'jam_label' => 240],
        55 => ['min_jam' => 200, 'jam_label' => 220],
        50 => ['min_jam' => 180, 'jam_label' => 200],
        45 => ['min_jam' => 160, 'jam_label' => 180],
        40 => ['min_jam' => 140, 'jam_label' => 160],
        35 => ['min_jam' => 130, 'jam_label' => 140],
        30 => ['min_jam' => 110, 'jam_label' => 120],
        25 => ['min_jam' =>  90, 'jam_label' => 100],
        20 => ['min_jam' =>  70, 'jam_label' =>  80],
        15 => ['min_jam' =>  50, 'jam_label' =>  60],
        10 => ['min_jam' =>  35, 'jam_label' =>  40],
        8  => ['min_jam' =>  25, 'jam_label' =>  32],
        5  => ['min_jam' =>   0, 'jam_label' =>  20],
    ];

    foreach ($profiles as $p) {
        try {
            $rekap = ImbalanRekap::firstOrNew([
                'profile_id' => $p->id,
                'bulan'      => $labelBulan
            ]);

            $isNew = !$rekap->exists;

            $history = $histories->get($p->id);
            $statusKaryawan = trim($history?->status_karyawan ?? $p->status_karyawan ?? '');
            $statusLower = strtolower($statusKaryawan);

            if (!in_array($statusLower, ['aktif', 'magang', 'kepala', 'kepala unit'])) {
                Log::info("SKIP - {$p->nama} | Status: {$statusKaryawan}");
                continue;
            }

            $isMagang = $statusLower === 'magang';

            // ==================== HITUNG LEMBUR ====================
            $totalJamLembur = 0;
            $totalNominalLembur = 0;

            $lemburData = Lembur::where('profile_id', $p->id)
                ->whereBetween('tgl_lembur', [$startDate, $endDate])
                ->where('status', 'Disetujui')
                ->get();

            if ($lemburData->isNotEmpty()) {
                $totalJamLembur = $lemburData->sum('total_jam');
                $totalNominalLembur = $totalJamLembur * 12500;

                Log::info("✅ LEMBUR DITEMUKAN - {$p->nama} | Jam: {$totalJamLembur} | Nominal: Rp " . number_format($totalNominalLembur));
            } else {
                Log::info("❌ Tidak ada lembur disetujui untuk {$p->nama} di periode ini");
            }

            // ==================== PERHITUNGAN LAIN (tetap sama) ====================
            $hariKerjaMaksimal = 25;
            $tglMasuk = !empty($p->tgl_masuk) ? Carbon::parse($p->tgl_masuk)->startOfDay() : null;

            $hariKerjaEfektif = $hariKerjaMaksimal;
            if ($tglMasuk && $tglMasuk->gte($startDate) && $tglMasuk->lte($endDate)) {
                $hariTerlewat = 0;
                $cursor = $startDate->copy();
                while ($cursor < $tglMasuk) {
                    if ($cursor->dayOfWeek !== Carbon::SUNDAY) $hariTerlewat++;
                    $cursor->addDay();
                }
                $hariKerjaEfektif = max(0, $hariKerjaMaksimal - $hariTerlewat);
            }

            $hariDipotong = 0;
            $potongan = PotonganTunjangan::where('nama', $p->nama)
                ->where('bulan', $bulanFormatYm)
                ->first();

            if ($potongan) {
                $totalPotong = ($potongan->sakit ?? 0) + ($potongan->izin ?? 0) + 
                               ($potongan->alpa ?? 0) + ($potongan->tidak_aktif ?? 0) + 
                               ($potongan->lain_lain ?? 0);
                $hariDipotong = (int) floor($totalPotong / 24000);
            }

            $hariTransport = max(0, $hariKerjaEfektif - $hariDipotong);
            $tambahan_transport = $hariTransport * 24000;

            $rbAwal = (str_contains(strtolower($p->jabatan ?? ''), 'kepala')) ? 40 : 30;
            $rbAwal = $extractRbNumber($p->rb ?? $p->rb_tambahan ?? '', $rbAwal);

            $ktrInput = trim($p->ktr ?? $p->ktr_tambahan ?? '');

            $durasiFull = $rbConfig[$rbAwal]['jam_label'] ?? 140;
            $jamEfektif = max(0, $durasiFull - ($hariDipotong * 7));

            $rbFinal = $rbAwal;
            $durasiFinal = $durasiFull;

            if ($jamEfektif < $durasiFull) {
                foreach ($rbConfig as $rb => $cfg) {
                    if ($jamEfektif >= $cfg['min_jam']) {
                        $rbFinal = $rb;
                        $durasiFinal = $cfg['jam_label'];
                        break;
                    }
                }
            }

            $imbalanPokokFull = 1050000;
            if ($ktrInput && $rbFinal) {
                $targetRb = 'RB' . $rbFinal;
                $ktrClean = $normalize($ktrInput);

                $ktr = $ktrList->first(function ($item) use ($ktrClean, $targetRb, $normalize) {
                    $kategori = $normalize($item->kategori ?? '');
                    $waktu    = $normalize($item->waktu ?? '');
                    return (str_contains($kategori, $ktrClean) || str_contains($ktrClean, $kategori)) &&
                           str_contains($waktu, $targetRb);
                });

                if ($ktr && $ktr->jumlah > 0) {
                    $imbalanPokokFull = (int) $ktr->jumlah;
                }
            }

            // ==================== FILL DATA ====================
            $rekap->fill([
                'nama'                => $p->nama,
                'bulan'               => $labelBulan,
                'profile_id'          => $p->id,
                'posisi'              => $p->jabatan,
                'status'              => $statusKaryawan,
                'departemen'          => $p->departemen,
                'bimba_unit'          => $p->bimba_unit ?? $p->biMBA_unit,
                'no_cabang'           => $p->no_cabang,
                'masa_kerja'          => $p->masa_kerja,
                'nik'                 => $p->nik,
                'jabatan'             => $p->jabatan,
                'kategori'            => $p->kategori,
                'status_karyawan'     => $statusKaryawan,

                'waktu_mgg'           => 'RB ' . $rbFinal,
                'waktu_bln'           => $durasiFinal . ' Jam',
                'durasi_kerja'        => $jamEfektif,
                'persen'              => $durasiFull > 0 ? round(($jamEfektif / $durasiFull) * 100, 2) : 0,
                'ktr'                 => $ktrInput ?: null,
                'imbalan_pokok'       => round($imbalanPokokFull),

                // ==================== LEMBUR ====================
                'lembur_jam'          => round($totalJamLembur, 2),
                'lembur_nominal'      => round($totalNominalLembur),

                'tambahan_transport'  => $tambahan_transport,
                'at_hari'             => $hariTransport,

                'imbalan_lainnya'     => $p->imbalan_lainnya_default ?? 0,
                'insentif_mentor'     => $p->insentif_mentor ?? 0,
                'cicilan'             => $p->cicilan_default ?? 0,
            ]);

            // ==================== HITUNG TOTAL (DITAMBAHKAN LEMBUR DENGAN AMAN) ====================
            $totalImbalan = 
                ($rekap->imbalan_pokok ?? round($imbalanPokokFull)) + 
                ($totalNominalLembur) +                                 // ← LEMBUR DITAMBAHKAN LANGSUNG
                ($rekap->imbalan_lainnya ?? 0) + 
                ($rekap->insentif_mentor ?? 0) + 
                ($rekap->tambahan_transport ?? 0);

            $rekap->total_imbalan = $totalImbalan;
            $rekap->yang_dibayarkan = $totalImbalan - ($rekap->cicilan ?? 0);

            if ($isMagang) {
                $rekap->imbalan_pokok = 0;
                $rekap->lembur_nominal = 0;
                $rekap->total_imbalan = 0;
                $rekap->yang_dibayarkan = 0;
            }

            $rekap->save();

            $isNew ? $created++ : $updated++;

        } catch (\Throwable $e) {
            $errors[] = $p->nama . ' => ' . $e->getMessage();
            Log::error("Gagal generate rekap {$p->nama}", ['error' => $e->getMessage()]);
        }
    }

    Log::info("=== SELESAI GENERATE REKAP {$labelBulan} | Created: {$created} | Updated: {$updated} ===");

    return [
        'created' => $created,
        'updated' => $updated,
        'total'   => $created + $updated,
        'errors'  => $errors
    ];
}


    private function parseRbDurasi(?string $durasi): array
    {
        $result = ['rb' => null, 'hours' => null];
        if (empty($durasi) || !is_string($durasi))
            return $result;

        $s = trim(preg_replace('/[[:punct:]]+/', ' ', $durasi));
        $s = preg_replace('/\s+/', ' ', $s);

        if (preg_match('/\bR\s*B\s*0*([0-9]{1,4})\b/i', $s, $m)) {
            $result['rb'] = 'RB' . $m[1];
        } elseif (preg_match('/\bRB0*([0-9]{1,4})\b/i', $s, $m)) {
            $result['rb'] = 'RB' . $m[1];
        }

        preg_match_all('/\b([0-9]{1,5})\b/', $s, $allNums);
        if (!empty($allNums[1])) {
            $nums = array_map('intval', $allNums[1]);
            $result['hours'] = max($nums);
        }

        return $result;
    }

  public function indexSlip(Request $request)
{
    $user = Auth::user();

    $rekapId  = $request->get('rekap_id');
    $unitId   = $request->get('unit_id');
    $periode  = $request->get('periode');

    $isAdmin = $user && (
        $user->role === 'admin' || ($user->is_admin ?? false)
    );

    $userUnit = $user->biMBA_unit ?? $user->bimba_unit ?? $user->unit ?? null;

    // ==================== NORMALISASI PERIODE ====================
    $normalizedPeriode = null;
    $bulanLabel = null;

    if ($periode) {
        try {
            $normalizedPeriode = Carbon::createFromFormat('Y-m', $periode)->format('Y-m');
        } catch (\Exception $e) {}
    }

    if (!$normalizedPeriode && $rekapId) {
        $rekapTmp = ImbalanRekap::find($rekapId);
        if ($rekapTmp?->bulan) {
            try {
                $normalizedPeriode = Carbon::createFromFormat('F Y', $rekapTmp->bulan, 'id')->format('Y-m');
            } catch (\Exception $e) {}
        }
    }

    if ($normalizedPeriode) {
        $bulanLabel = Carbon::createFromFormat('Y-m', $normalizedPeriode)
            ->locale('id')
            ->translatedFormat('F Y');
    }

    // ==================== RESOLVE UNIT ====================
    $displayUnit = null;
    if ($isAdmin && $unitId) {
        $displayUnit = optional(\App\Models\Unit::find($unitId))->biMBA_unit;
    }
    if (!$isAdmin && $userUnit) {
        $displayUnit = $userUnit;
    }
    if (!$displayUnit && $rekapId) {
        $rekapTmp = ImbalanRekap::find($rekapId);
        $displayUnit = $rekapTmp->biMBA_unit ?? null;
    }
    $displayUnit = $displayUnit ?: 'biMBA AIUEO';

    // ==================== DATA UTAMA ====================
    $rekap   = $rekapId ? ImbalanRekap::find($rekapId) : null;
    $profile = $rekap ? Profile::where('nama', $rekap->nama)->first() : null;

    $units = $isAdmin ? \App\Models\Unit::orderBy('biMBA_unit')->get() : collect();
    $allRekaps = ImbalanRekap::where('biMBA_unit', $displayUnit)
                ->orderBy('nama')->get(['id', 'nama']);

    // ==================== STATUS ====================
    $statusFinal = trim($rekap->status ?? $rekap->status_karyawan ?? 
                       $profile?->status_karyawan ?? $profile?->status ?? 'Aktif');

    $kategoriFinal = strtolower(trim($statusFinal)) === 'magang' ? 'Magang' : 'Aktif';

    // ==================== POTONGAN & CICILAN ====================
    $potongan = $rekap ? PotonganTunjangan::where('nama', $rekap->nama)
                        ->where('bulan', $normalizedPeriode)->first() : null;

    $cicilan = collect();
    $totalCicilan = (int) ($rekap->cicilan ?? 0);
    if ($totalCicilan > 0) {
        $cicilan->push((object)[
            'keterangan' => $rekap->keterangan_cicilan ?? 'Cicilan Cash Advance',
            'jumlah'     => $totalCicilan,
        ]);
    }

    // ==================== ADJUSTMENT ====================
    $adjustments = collect();
    $totalKekuranganAdj = 0;
    $totalKelebihanAdj = 0;
    $keteranganKekuranganAdj = '';
    $keteranganKelebihanAdj = '';

    if ($rekap && $normalizedPeriode) {
        try {
            $carbon = Carbon::createFromFormat('Y-m', $normalizedPeriode);
            $adjustments = Adjustment::whereRaw('TRIM(UPPER(nama)) = ?', [strtoupper(trim($rekap->nama))])
                ->where('month', $carbon->month)
                ->where('year', $carbon->year)
                ->get();

            foreach ($adjustments as $adj) {
                $nominal = (float) $adj->nominal;
                $type = strtolower(trim($adj->type ?? ''));
                if (str_contains($type, 'tambah')) {
                    $totalKekuranganAdj += $nominal;
                    if (!empty($adj->keterangan)) 
                        $keteranganKekuranganAdj .= ($keteranganKekuranganAdj ? ' | ' : '') . $adj->keterangan;
                } elseif (str_contains($type, 'potong')) {
                    $totalKelebihanAdj += $nominal;
                    if (!empty($adj->keterangan)) 
                        $keteranganKelebihanAdj .= ($keteranganKelebihanAdj ? ' | ' : '') . $adj->keterangan;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Adjustment error: ' . $e->getMessage());
        }
    }

    // ==================== 🔥 HITUNG LEMBUR ====================
    $totalJamLembur = 0;
    $totalNominalLembur = 0;

    if ($rekap && $normalizedPeriode) {
        try {
            $carbon = Carbon::createFromFormat('Y-m', $normalizedPeriode);
            $startDate = $carbon->copy()->subMonth()->day(26)->startOfDay();
            $endDate   = $carbon->copy()->day(25)->endOfDay();

            $lemburData = Lembur::where('profile_id', $rekap->profile_id)
                ->whereBetween('tgl_lembur', [$startDate, $endDate])
                ->where('status', 'Disetujui')
                ->get();

            if ($lemburData->isNotEmpty()) {
                $totalJamLembur = $lemburData->sum('total_jam');
                $totalNominalLembur = $totalJamLembur * 12500;
            }
        } catch (\Exception $e) {
            Log::error("Gagal hitung lembur di slip: " . $e->getMessage());
        }
    }

    // ==================== MASA KERJA ====================
    $masaKerja = '-';
    $tanggalMasuk = '-';
    if ($profile?->tgl_masuk) {
        $masaKerja = $this->hitungMasaKerja($profile->tgl_masuk);
        $tanggalMasuk = Carbon::parse($profile->tgl_masuk)->translatedFormat('d F Y');
    }

    // ==================== HITUNG TOTAL ====================
    $imbalanPokok   = $rekap->imbalan_pokok ?? 0;
    $imbalanLainnya = $rekap->imbalan_lainnya ?? 0;
    $insentif       = $rekap->insentif_mentor ?? 0;
    $transport      = $rekap->tambahan_transport ?? 0;
    $kekurangan     = $rekap->kekurangan ?? 0;

    $totalPendapatan = $imbalanPokok + $imbalanLainnya + $insentif + $transport 
                       + $kekurangan + $totalKekuranganAdj + $totalNominalLembur;

    $totalPotongan = ($potongan->total ?? 0) + $totalCicilan + $totalKelebihanAdj;

    $yangDibayarkan = $totalPendapatan 
                      + ($rekap->jumlah_bagi_hasil ?? 0) 
                      - ($rekap->kelebihan ?? 0) 
                      - ($rekap->cicilan ?? 0);

    // ==================== RETURN VIEW ====================
    return view('imbalan_rekap.slip_index', [
        'rekap'                   => $rekap,
        'profile'                 => $profile,
        'potongan'                => $potongan,
        'cicilan'                 => $cicilan,
        'totalCicilan'            => $totalCicilan,
        'allRekaps'               => $allRekaps,
        'periodeOptions'          => $this->getPeriodeOptions(),
        'periodeValue'            => $normalizedPeriode,
        'periode'                 => $bulanLabel,
        'masaKerja'               => $masaKerja,
        'tanggalMasuk'            => $tanggalMasuk,
        'noRekening'              => $profile?->no_rekening ?? '-',
        'bank'                    => $profile?->bank ?? '-',
        'atasNama'                => $profile?->nama_rekening ?? $rekap?->nama,
        'unit'                    => $displayUnit,
        'isAdmin'                 => $isAdmin,
        'units'                   => $units,

        'adjustments'             => $adjustments,
        'totalKekuranganAdj'      => $totalKekuranganAdj,
        'totalKelebihanAdj'       => $totalKelebihanAdj,
        'keteranganKekuranganAdj' => $keteranganKekuranganAdj,
        'keteranganKelebihanAdj'  => $keteranganKelebihanAdj,

        // LEMBUR
        'totalJamLembur'          => $totalJamLembur,
        'totalNominalLembur'      => $totalNominalLembur,

        'totalPotongan'           => $totalPotongan,
        'totalPendapatan'         => $totalPendapatan,
        'yangDibayarkan'          => $yangDibayarkan,

        'statusFinal'             => $statusFinal,
        'kategoriFinal'           => $kategoriFinal,
        'statusKaryawan'          => $statusFinal,
    ]);
}

/* ======================================================
 * OPSI PERIODE (DROPDOWN BULAN)
 * ====================================================== */
private function getPeriodeOptions(): array
{
    $out = [];

    for ($i = 0; $i < 12; $i++) {
        $d = now()->subMonths($i);
        $out[] = [
            'value' => $d->format('Y-m'),
            'label' => $d->locale('id')->translatedFormat('F Y'),
        ];
    }

    return $out;
}

public function getRelawansByFilter(Request $request)
{
    $periode  = $request->query('periode');     // format Y-m
    $unit_id  = $request->query('unit_id');

    $query = ImbalanRekap::query()
        ->select('id', 'nama')
        ->distinct()
        ->orderBy('nama');

    // Filter periode (wajib)
    if ($periode) {
        // Karena kolom 'bulan' di tabel simpan format "Januari 2025", bukan Y-m
        // Kita perlu konversi dulu
        try {
            $carbonPeriode = Carbon::createFromFormat('Y-m', $periode);
            $bulanLabel = $carbonPeriode->locale('id')->translatedFormat('F Y');
            $query->where('bulan', $bulanLabel);
        } catch (\Exception $e) {
            // Jika format salah → return kosong atau fallback
            return response()->json([]);
        }
    } else {
        return response()->json([]);
    }

    // Filter unit (opsional, hanya jika admin & dipilih)
    if ($unit_id) {
        $query->where('biMBA_unit', function ($sub) use ($unit_id) {
            $sub->select('biMBA_unit')
                ->from('units')
                ->where('id', $unit_id)
                ->limit(1);
        });
        // Atau jika unit disimpan langsung sebagai string di imbalan_rekaps:
        // $query->where('biMBA_unit', $unitName);  // sesuaikan
    }

    $relawans = $query->get();

    return response()->json(
        $relawans->map(fn($r) => [
            'id'   => $r->id,
            'nama' => $r->nama,
        ])
    );
}
public function bayarPeriode(Request $request)
{

    $start_bulan = $request->start_bulan;
    $start_tahun = $request->start_tahun;

    $periode = Carbon::createFromFormat('Y-m', "$start_tahun-$start_bulan")
        ->locale('id')
        ->translatedFormat('F Y');

    $updated = ImbalanRekap::where('bulan', $periode)
        ->update([
            'status_pembayaran' => 'dibayar',
            'tanggal_dibayar' => now(),
            'dibayar_oleh' => Auth::user()->name
        ]);

    return back()->with(
        'success',
        "Berhasil membayar {$updated} relawan untuk periode {$periode}"
    );

}

public function bayarSingle(Request $request)
{
    $request->validate([
        'id' => 'required|exists:imbalan_rekaps,id'
    ]);

    $rekap = ImbalanRekap::findOrFail($request->id);

    if ($rekap->status_pembayaran === 'dibayar') {
        return response()->json([
            'success' => false,
            'message' => 'Sudah ditandai dibayar sebelumnya'
        ], 422);
    }

    $rekap->update([
        'status_pembayaran' => 'dibayar',
        'tanggal_dibayar'   => now(),
        'dibayar_oleh'      => Auth::user()->name ?? 'System'
    ]);

    // Refresh model agar accessor bekerja (opsional, tapi aman)
    $rekap->refresh();

    $tanggalFormatted = $rekap->tanggal_dibayar 
        ? $rekap->tanggal_dibayar->format('d/m/Y H:i') 
        : now()->format('d/m/Y H:i');  // fallback jika entah kenapa null

    return response()->json([
        'success' => true,
        'message' => 'Berhasil ditandai sudah dibayar',
        'tanggal' => $tanggalFormatted
    ]);
}
}