<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\KartuSpp;
use App\Models\Penerimaan;
use App\Models\VoucherLama;
use App\Models\VoucherHistori;
use Carbon\Carbon;
use App\Models\Unit;
use App\Models\BukuInduk;
use Illuminate\Http\Request;

class KartuSppController extends Controller
{
    // Halaman index
    public function index(Request $request)
{
    // Ambil unit-unit unik untuk dropdown filter (hanya untuk admin)
    $unitOptions = Unit::orderBy('biMBA_unit')
        ->pluck('biMBA_unit')
        ->unique()
        ->values();

    // Query dasar murid aktif
    $query = BukuInduk::select('nim', 'nama', 'gol', 'kd', 'spp', 'tgl_masuk', 'bimba_unit')
        ->whereIn('status', ['aktif', 'baru']);

    // === LOGIKA FILTER BERDASARKAN ROLE USER ===
    if (auth()->user() && !auth()->user()->is_admin) {
        // User biasa → hanya lihat murid dari unitnya sendiri
        // Asumsi: user punya kolom 'bimba_unit' di tabel users
        // Jika tidak ada, sesuaikan dengan logika kamu (misal dari profile, dll)
        $userUnit = auth()->user()->bimba_unit; // atau cara lain untuk ambil unit user

        if ($userUnit) {
            $query->whereRaw('LOWER(TRIM(bimba_unit)) = ?', [strtolower(trim($userUnit))]);
        } else {
            // Jika user tidak punya unit, tampilkan kosong
            $query->whereRaw('1 = 0');
        }
    }

    // === FILTER MANUAL OLEH ADMIN (opsional via dropdown) ===
    $unitFilter = $request->get('unit');
    if ($unitFilter && auth()->user()?->is_admin) {
        $query->whereRaw('LOWER(TRIM(bimba_unit)) = ?', [strtolower(trim($unitFilter))]);
    }

    // Eksekusi query
    $murid = $query->orderBy('nim', 'asc')
        ->get()
        ->map(function ($m) {
            $m->spp     = $this->normalizeRupiah($m->spp);
            $m->spp_rp  = $this->rupiah($m->spp);
            $m->tgl_masuk_fmt = $m->tgl_masuk
                ? Carbon::parse($m->tgl_masuk)->translatedFormat('d M Y')
                : '-';
            return $m;
        });

    return view('kartu_spp.index', compact('murid', 'unitOptions', 'unitFilter'));
}

    public function create()
    {
        return view('kartu_spp.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_pembayaran'  => 'required|unique:kartu_spp',
            'nama_murid'     => 'required|string',
            'golongan'       => 'required|string',
            'pembayaran_spp' => 'required|numeric',
            'bimba_unit'     => 'required|string',
        ]);

        KartuSpp::create($request->all());

        return redirect()->route('kartu_spp.index')->with('success', 'Data kartu SPP berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $kartu = KartuSpp::findOrFail($id);
        return view('kartu_spp.edit', compact('kartu'));
    }

    public function update(Request $request, $id)
    {
        $kartu = KartuSpp::findOrFail($id);

        $request->validate([
            'no_pembayaran'  => 'required|unique:kartu_spp,no_pembayaran,' . $id,
            'nama_murid'     => 'required|string',
            'golongan'       => 'required|string',
            'pembayaran_spp' => 'required|numeric',
            'bimba_unit'     => 'required|string',
        ]);

        $kartu->update($request->all());

        return redirect()->route('kartu_spp.index')->with('success', 'Data kartu SPP berhasil diupdate!');
    }

    public function destroy($id)
    {
        $kartu = KartuSpp::findOrFail($id);
        $kartu->delete();

        return redirect()->route('kartu_spp.index')->with('success', 'Data kartu SPP berhasil dihapus!');
    }

    // AJAX untuk detail kartu SPP berdasarkan NIM
   public function detail($nim, Request $request)
{
    /* ===============================
       DATA MURID
    =============================== */
    $murid = BukuInduk::where('nim', $nim)->first();
    if (!$murid) {
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    /* ===============================
       FILTER PERIODE (BARU)
    =============================== */
    $filterBulan = $request->get('bulan');   // contoh: 2
    $filterTahun = $request->get('tahun');   // contoh: 2026

    /* ===============================
       UNIT
    =============================== */
    $unit = null;
    if ($murid->bimba_unit) {
        $unit = Unit::whereRaw(
            'LOWER(TRIM(biMBA_unit)) = ?',
            [strtolower(trim($murid->bimba_unit))]
        )->first();
    }
    $unit = $unit ?? Unit::first();

    /* ===============================
       SPP & TANGGAL MASUK
    =============================== */
    $sppMurid = $this->normalizeRupiah($murid->spp);

    $tglMasuk = $murid->tgl_masuk
        ? Carbon::parse($murid->tgl_masuk)->startOfMonth()
        : now()->startOfMonth();

    /* ===============================
       PENERIMAAN (SEMUA)
    =============================== */
    $penerimaan = Penerimaan::where('nim', $murid->nim)->get();

    /* ===============================
       LOGIKA GRATIS
    =============================== */
    $gol = strtolower(trim($murid->gol ?? ''));
    $isGratis = in_array($gol, ['d', 'dhuafa']);
    $nominalPerVoucher = 50000;

    $riwayat = collect();

    /* ===============================
       RIWAYAT BULANAN
    =============================== */
    if ($filterBulan && $filterTahun) {
        // ==================== HANYA SATU BULAN (FILTER) ====================
        $bulan = Carbon::create((int)$filterTahun, (int)$filterBulan, 1);
        $bulanNama = $bulan->translatedFormat('F');
        $tahun = $bulan->year;
        $akhirBulan = $bulan->copy()->endOfMonth();

        $pembayaran = $penerimaan
            ->filter(fn ($p) =>
                strtolower(trim($p->bulan ?? '')) === strtolower($bulanNama)
                && (int)$p->tahun === $tahun
            )
            ->sortByDesc(fn($p) => (int)($p->spp ?? 0))
            ->first();

        $jumlahSpp = $pembayaran ? (int)($pembayaran->spp ?? 0) : 0;

        $voucherJumlah = VoucherLama::where('nim', $murid->nim)
            ->whereDate('tanggal', '<=', $akhirBulan)
            ->count();

        $voucherDipakai = VoucherHistori::where('nim', $murid->nim)
            ->whereDate('tanggal_pemakaian', '<=', $akhirBulan)
            ->count();

        $voucherSisa = max(0, $voucherJumlah - $voucherDipakai);

        $statusBulan = $isGratis ? 'Sudah bayar' : ($jumlahSpp > 0 || $voucherDipakai > 0 ? 'Sudah bayar' : 'Belum bayar');

        $tanggalTransaksi = '-';
        if ($pembayaran && $pembayaran->tanggal) {
            $tanggalTransaksi = Carbon::parse($pembayaran->tanggal)->translatedFormat('d M Y');
        } else {
            $tglVoucher = VoucherHistori::where('nim', $murid->nim)
                ->whereYear('tanggal_pemakaian', $tahun)
                ->whereMonth('tanggal_pemakaian', $bulan->month)
                ->orderBy('tanggal_pemakaian')
                ->value('tanggal_pemakaian');

            if ($tglVoucher) {
                $tanggalTransaksi = Carbon::parse($tglVoucher)->translatedFormat('d M Y');
            }
        }

        $voucherJumlahText = $voucherJumlah > 0 ? $voucherJumlah . ' (' . $this->rupiah($voucherJumlah * $nominalPerVoucher) . ')' : '-';
        $voucherDipakaiText = $voucherDipakai > 0 ? $voucherDipakai . ' (' . $this->rupiah($voucherDipakai * $nominalPerVoucher) . ')' : '0';
        $voucherSisaText = $voucherSisa > 0 ? $voucherSisa . ' (' . $this->rupiah($voucherSisa * $nominalPerVoucher) . ')' : '0';

        $riwayat->push([
            'bulan'             => $bulanNama . ' ' . $tahun,
            'status'            => $statusBulan,
            'tanggal_transaksi' => $tanggalTransaksi,
            'voucher_jumlah'    => $voucherJumlahText,
            'voucher_dipakai'   => $voucherDipakaiText,
            'voucher_sisa'      => $voucherSisaText,
            'jumlah'            => $jumlahSpp,
        ]);

    } else {
        // ==================== TANPA FILTER (Kode Asli Anda) ====================
        $mapBulan = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $lastPembayaranDate = null;
        foreach ($penerimaan as $p) {
            if (!$p->bulan || !$p->tahun) continue;
            $bulanKey = strtolower(trim($p->bulan));
            if (!isset($mapBulan[$bulanKey])) continue;

            $tgl = Carbon::create((int)$p->tahun, $mapBulan[$bulanKey], 1);
            if (!$lastPembayaranDate || $tgl->gt($lastPembayaranDate)) {
                $lastPembayaranDate = $tgl;
            }
        }

        $lastVoucherDate = VoucherLama::where('nim', $murid->nim)->max('tanggal');

        $bulanAkhir = collect([
            now()->startOfMonth(),
            $lastPembayaranDate?->startOfMonth(),
            $lastVoucherDate ? Carbon::parse($lastVoucherDate)->startOfMonth() : null,
        ])->filter()->max();

        $bulan = $tglMasuk->copy();

        while ($bulan->lte($bulanAkhir)) {
            $bulanNama  = $bulan->translatedFormat('F');
            $tahun      = $bulan->year;
            $akhirBulan = $bulan->copy()->endOfMonth();

            $pembayaran = $penerimaan
                ->filter(fn ($p) =>
                    strtolower(trim($p->bulan)) === strtolower($bulanNama)
                    && (int)$p->tahun === $tahun
                )
                ->sortByDesc(function ($p) {
                    return (int)($p->spp ?? 0);
                })
                ->first();

            $jumlahSpp = $pembayaran ? (int)($pembayaran->spp ?? 0) : 0;

            $voucherJumlah = VoucherLama::where('nim', $murid->nim)
                ->whereDate('tanggal', '<=', $akhirBulan)
                ->count();

            $voucherDipakai = VoucherHistori::where('nim', $murid->nim)
                ->whereDate('tanggal_pemakaian', '<=', $akhirBulan)
                ->count();

            $voucherSisa = max(0, $voucherJumlah - $voucherDipakai);

            $statusBulan = $isGratis ? 'Sudah bayar' : ($jumlahSpp > 0 || $voucherDipakai > 0 ? 'Sudah bayar' : 'Belum bayar');

            $tanggalTransaksi = '-';
            if ($pembayaran && $pembayaran->tanggal) {
                $tanggalTransaksi = Carbon::parse($pembayaran->tanggal)->translatedFormat('d M Y');
            } else {
                $tglVoucher = VoucherHistori::where('nim', $murid->nim)
                    ->whereYear('tanggal_pemakaian', $bulan->year)
                    ->whereMonth('tanggal_pemakaian', $bulan->month)
                    ->orderBy('tanggal_pemakaian')
                    ->value('tanggal_pemakaian');

                if ($tglVoucher) {
                    $tanggalTransaksi = Carbon::parse($tglVoucher)->translatedFormat('d M Y');
                }
            }

            $voucherJumlahText = $voucherJumlah > 0 ? $voucherJumlah . ' (' . $this->rupiah($voucherJumlah * $nominalPerVoucher) . ')' : '-';
            $voucherDipakaiText = $voucherDipakai > 0 ? $voucherDipakai . ' (' . $this->rupiah($voucherDipakai * $nominalPerVoucher) . ')' : '0';
            $voucherSisaText = $voucherSisa > 0 ? $voucherSisa . ' (' . $this->rupiah($voucherSisa * $nominalPerVoucher) . ')' : '0';

            $riwayat->push([
                'bulan'             => $bulanNama . ' ' . $tahun,
                'status'            => $statusBulan,
                'tanggal_transaksi' => $tanggalTransaksi,
                'voucher_jumlah'    => $voucherJumlahText,
                'voucher_dipakai'   => $voucherDipakaiText,
                'voucher_sisa'      => $voucherSisaText,
                'jumlah'            => $jumlahSpp,
            ]);

            $bulan->addMonth();
        }
    }

    /* ===============================
       STATUS HEADER
    =============================== */
    if ($filterBulan && $filterTahun) {
        $bulanHeader = Carbon::create($filterTahun, $filterBulan, 1)->translatedFormat('F Y');
        $statusBayar = $isGratis 
            ? 'Khusus Golongan (' . strtoupper($gol) . ')'
            : 'Status Bulan ' . $bulanHeader;
    } else {
        $bulanSekarang = now()->locale('id')->translatedFormat('F');
        $tahunSekarang = now()->year;

        if ($isGratis) {
            $statusBayar = 'Khusus Golongan (' . strtoupper($gol) . ')';
        } else {
            $statusBayar = (
                $penerimaan->first(fn ($p) =>
                    strtolower(trim($p->bulan)) === strtolower($bulanSekarang)
                    && (int)$p->tahun === $tahunSekarang
                    && (int)($p->spp ?? 0) > 0
                )
                || VoucherHistori::whereYear('tanggal_pemakaian', $tahunSekarang)
                    ->whereMonth('tanggal_pemakaian', now()->month)
                    ->exists()
            )
                ? 'Sudah bayar SPP bulan ' . ucfirst($bulanSekarang)
                : 'Belum bayar SPP bulan ' . ucfirst($bulanSekarang);
        }
    }

    /* ===============================
       RINGKASAN VOUCHER
    =============================== */
    $totalVoucher = VoucherLama::where('nim', $murid->nim)->count();
    $dipakaiTotal = VoucherHistori::where('nim', $murid->nim)->count();
    $sisaTotal    = max(0, $totalVoucher - $dipakaiTotal);

    return response()->json([
        'nim'             => $murid->nim,
        'nama'            => $murid->nama,
        'golongan'        => $murid->gol . ' | ' . $murid->kd,
        'spp'             => $sppMurid,
        'unit'            => $unit->biMBA_unit ?? '-',
        'rekening'        => $unit ? ($unit->bank_nama . ' | ' . $unit->bank_nomor) : '-',
        'status_bayar'    => $statusBayar,
        'tgl_masuk'       => $murid->tgl_masuk
            ? Carbon::parse($murid->tgl_masuk)->translatedFormat('d M Y')
            : '-',
        'riwayat'         => $riwayat->values(),
        'voucher_summary' => [
            'jumlah'        => $totalVoucher,
            'nominal_total' => $this->rupiah($totalVoucher * $nominalPerVoucher),
            'digunakan'     => $dipakaiTotal,
            'sisa'          => $sisaTotal,
            'sisa_nominal'  => $this->rupiah($sisaTotal * $nominalPerVoucher),
        ],
    ]);
}

    public function getDetailByNimFull($nim)
{
    $murid = BukuInduk::where('nim', $nim)->first();
    if (!$murid) {
        return response()->json(['error' => 'Murid tidak ditemukan'], 404);
    }

    // CARI UNIT berdasarkan field di BukuInduk (misal: bimba_unit)
    $unit = null;
    if (!empty($murid->bimba_unit)) {
        $unit = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($murid->bimba_unit))])->first();
    }
    $unit = $unit ?? Unit::first();

    $tahunSekarang = now()->year;

    // Normalisasi SPP murid
    $sppMurid = $this->normalizeRupiah($murid->spp);

    $bulanAwal  = Carbon::createFromDate($tahunSekarang, 1, 1)->locale('id');
    $bulanAkhir = now()->copy()->locale('id');

    $penerimaan = Penerimaan::whereRaw("CAST(nim AS CHAR) = ?", [$murid->nim])
        ->whereNotNull('spp')->where('spp', '>', 0)
        ->get();

    $riwayat = collect();

    while ($bulanAwal->lte($bulanAkhir)) {
        $bulanNama = $bulanAwal->translatedFormat('F');
        $tahun     = $bulanAwal->year;

        $pembayaran = $penerimaan->first(function ($p) use ($bulanNama, $tahun) {
            return strtolower(trim($p->bulan)) === strtolower(trim($bulanNama))
                && (int)$p->tahun === (int)$tahun;
        });

        $jumlah    = $pembayaran ? (int)$pembayaran->spp : 0;
        $jumlah_rp = $this->rupiah($jumlah);

        // voucher display
        $voucher_nominal = $pembayaran ? ($pembayaran->voucher ?? 0) : 0;
        $voucher_display = '-';
        if ($voucher_nominal > 0 && $pembayaran && $pembayaran->tanggal) {
            $histori = VoucherHistori::where('nim', $murid->nim)
                ->whereDate('tanggal_pemakaian', $pembayaran->tanggal)->first();
            if ($histori) {
                $voucherLama = VoucherLama::find($histori->voucher_lama_id);
                $voucher_display = $voucherLama
                    ? ($voucherLama->voucher . ' (Rp ' . $this->rupiah($voucher_nominal) . ')')
                    : ('Rp ' . $this->rupiah($voucher_nominal));
            } else {
                $voucher_display = 'Rp ' . $this->rupiah($voucher_nominal);
            }
        }

        $tanggal_transaksi = $pembayaran && $pembayaran->tanggal
            ? Carbon::parse($pembayaran->tanggal)->translatedFormat('d M Y')
            : '-';

        $riwayat->push([
            'bulan'             => $bulanNama . ' ' . $tahun,
            'jumlah'            => $jumlah,
            'jumlah_rp'         => $jumlah_rp,
            'status'            => $jumlah > 0 ? 'Sudah bayar' : 'Belum bayar',
            'tanggal_transaksi' => $tanggal_transaksi,
            'voucher'           => $voucher_display,
        ]);

        $bulanAwal->addMonth();
    }

    $kartu = KartuSpp::where('nim', $murid->nim)->first();
    $bulanSekarangNama = now()->locale('id')->translatedFormat('F');

    $pembayaranBulanIni = $penerimaan->first(function ($p) use ($bulanSekarangNama, $tahunSekarang) {
        return strtolower(trim($p->bulan)) === strtolower(trim($bulanSekarangNama))
            && (int)$p->tahun === (int)$tahunSekarang;
    });

    return response()->json([
        'nim'            => $murid->nim,
        'nama'           => $murid->nama,
        'golongan'       => $murid->gol . ' | ' . $murid->kd,
        'spp'            => $sppMurid,
        'spp_rp'         => $this->rupiah($sppMurid),
        'unit'           => $unit->biMBA_unit ?? '-',
        'billPayment'    => $kartu->bill_payment ?? 0,
        'virtualAccount' => $kartu->virtual_account ?? '-',
        'rekening'       => $unit ? ($unit->bank_nama . ' | ' . $unit->bank_nomor) : 'MANDIRI | -',
        'bulanTahun'     => $bulanSekarangNama . ' ' . $tahunSekarang,
        'status_bayar'   => ($pembayaranBulanIni && (int)$pembayaranBulanIni->spp > 0)
            ? 'Sudah bayar SPP bulan ' . $bulanSekarangNama
            : 'Belum bayar SPP bulan ' . $bulanSekarangNama,
        'tgl_masuk'  => $murid->tgl_masuk
            ? Carbon::parse($murid->tgl_masuk)->translatedFormat('d M Y')
            : '-',
        'riwayat'        => $riwayat->values(),
    ]);
}


    private function normalizeRupiah($value): int
    {
        $n = (int) str_replace(['.', ','], '', (string) $value);
        if ($n > 0 && $n < 1000) $n *= 1000;
        return $n;
    }

    /** Format angka ke "300.000" */
    private function rupiah($value): string
    {
        return number_format((int)$value, 0, ',', '.');
    }




    public function exportPdf($nim)
{
    $response = $this->detail($nim);

    if ($response->getStatusCode() !== 200) {
        return $response;
    }

    $data = $response->getData(true);

    // 1. Tentukan tahun & bulan mulai berdasarkan tgl_masuk
    $startDate = now();
    if (!empty($data['tgl_masuk'])) {
        try {
            $startDate = \Carbon\Carbon::parse($data['tgl_masuk']);
        } catch (\Exception $e) {
            // fallback jika parsing gagal
        }
    }

    // Mulai dari bulan masuk (jika tanggal > 1, mulai bulan berikutnya)
    $startMonth = $startDate->month;
    $startYear  = $startDate->year;

    // Jika masuk di akhir bulan, mulai bulan depan (opsional, bisa diubah)
    // $startMonth = $startDate->day > 15 ? $startDate->addMonth()->month : $startDate->month;
    // $startYear  = $startDate->day > 15 ? $startDate->addMonth()->year : $startDate->year;

    $tahunSekarang = now()->year;

    // 2. Kumpulkan semua riwayat yang ada menjadi map berdasarkan key YYYY-MM
    $riwayatByKey = [];
    if (!empty($data['riwayat'])) {
        foreach ($data['riwayat'] as $r) {
            $key = $this->getBulanTahunKey($r['bulan']);
            if ($key) {
                $riwayatByKey[$key] = $r;
            }
        }
    }

    // 3. Bangun daftar bulan secara kronologis
    $semuaBulan = [];

    $currentYear  = $startYear;
    $currentMonth = $startMonth;

    while ($currentYear < $tahunSekarang || ($currentYear == $tahunSekarang && $currentMonth <= 12)) {
        $key = sprintf("%d-%02d", $currentYear, $currentMonth);

        $namaBulan = \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)
            ->translatedFormat('F');

        $default = [
            'bulan'            => $namaBulan . ' ' . $currentYear,
            'status'           => 'Belum bayar',
            'tanggal_transaksi'=> '-',
            'voucher_jumlah'   => '-',
            'voucher_dipakai'  => '0',
            'voucher_sisa'     => '0',
            'jumlah'           => $data['spp'] ?? 0,
        ];

        if (isset($riwayatByKey[$key])) {
            $r = $riwayatByKey[$key];
            $semuaBulan[] = [
                'bulan'            => $r['bulan'] ?? $default['bulan'],
                'status'           => $r['status'] ?? 'Sudah bayar',
                'tanggal_transaksi'=> $r['tanggal_transaksi'] ?? '-',
                'voucher_jumlah'   => $r['voucher_jumlah'] ?? '-',
                'voucher_dipakai'  => $r['voucher_dipakai'] ?? '0',
                'voucher_sisa'     => $r['voucher_sisa'] ?? '0',
                'jumlah'           => $r['jumlah'] ?? ($data['spp'] ?? 0),
            ];
        } else {
            $semuaBulan[] = $default;
        }

        // Lanjut ke bulan berikutnya
        $currentMonth++;
        if ($currentMonth > 12) {
            $currentMonth = 1;
            $currentYear++;
        }
    }

    // 4. Ganti riwayat dengan daftar yang sudah urut benar
    $data['riwayat'] = $semuaBulan;

    $statusClass = str_contains($data['status_bayar'] ?? '', 'Belum')
        ? 'text-danger fw-bold'
        : 'text-success fw-bold';

    $pdf = Pdf::loadView('kartu_spp.print', [
        'data'        => $data,
        'statusClass' => $statusClass,
        'currentDate' => now()->translatedFormat('d F Y'),
    ]);

    $pdf->setPaper('A5', 'landscape');
    $pdf->setOption('margin-top', 10);
    $pdf->setOption('margin-bottom', 10);
    $pdf->setOption('margin-left', 10);
    $pdf->setOption('margin-right', 10);

    return $pdf->stream("kartu-spp-{$nim}.pdf");
}
private function getBulanTahunKey($bulanString)
{
    $bulanIndonesia = [
        'Januari'   => '01', 'Februari'  => '02', 'Maret'     => '03', 'April'    => '04',
        'Mei'       => '05', 'Juni'      => '06', 'Juli'      => '07', 'Agustus'  => '08',
        'September' => '09', 'Oktober'   => '10', 'November'  => '11', 'Desember' => '12'
    ];

    $tahun = null;
    $bulanNum = null;

    // Ambil tahun dari string
    if (preg_match('/(\d{4})/', $bulanString, $match)) {
        $tahun = $match[1];
    }

    // Ambil nama bulan
    foreach ($bulanIndonesia as $nama => $nomor) {
        if (stripos($bulanString, $nama) !== false) {
            $bulanNum = $nomor;
            break;
        }
    }

    // Format langsung YYYY-MM jika cocok
    if (preg_match('/(\d{4})-(\d{2})/', $bulanString, $m)) {
        return $m[1] . '-' . $m[2];
    }

    if ($tahun && $bulanNum) {
        return "$tahun-$bulanNum";
    }

    return null;
}
}
