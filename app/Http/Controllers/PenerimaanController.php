<?php

namespace App\Http\Controllers;

use App\Models\Penerimaan;
use App\Models\BukuInduk;
use App\Models\Unit;
use App\Imports\PenerimaanImport;
use App\Exports\PenerimaanExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DaftarMuridDeposit;
use App\Models\HargaSaptataruna;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\VoucherLama;
use App\Models\VoucherHistori;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PindahGolongan;
use App\Traits\HasBulanIndo;
use Illuminate\Support\Facades\Schema;

class PenerimaanController extends Controller
{
    use HasBulanIndo;

    /**
     * Helper untuk membersihkan input mata uang berformat (e.g., "100.000") menjadi integer.
     * @param string|null $input
     * @return int
     */
    protected function cleanMoneyInput($input): int
    {
        return $input ? (int) str_replace(['.', ','], '', $input) : 0;
    }

    public function index(Request $request)
{
    $perPage   = (int) $request->input('per_page', 10);
    $search    = trim($request->input('search', ''));
    $bulan     = $request->input('bulan');
    $tahun     = $request->input('tahun');
    $bimbaUnit = $request->input('bimba_unit');

    $query = Penerimaan::query();

    // Filter pencarian Nama Murid / NIM
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('nim', 'like', "%{$search}%")
              ->orWhere('nama_murid', 'like', "%{$search}%");
        });
    }

    if ($bulan) $query->where('bulan', $bulan);
    if ($tahun) $query->where('tahun', $tahun);
    if ($bimbaUnit) $query->where('bimba_unit', $bimbaUnit);

    $queryForSum = clone $query;

    $penerimaan = $query->orderBy('tanggal', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate($perPage)
                        ->withQueryString();

                        

    // Total Ringkasan
    $totalVoucher = (clone $queryForSum)
    ->whereNotNull('voucher')
    ->where('voucher', '!=', 0)
    ->count() * 50000;
    $totalSpp         = $queryForSum->sum('spp');
    $totalKaosPendek  = $queryForSum->sum('kaos');
    $totalKaosPanjang = $queryForSum->sum('kaos_lengan_panjang');
    $totalKpk         = $queryForSum->sum('kpk');
    $totalTas         = $queryForSum->sum('tas');
    $totalRbas        = $queryForSum->sum('RBAS');
    $totalBcabs01     = $queryForSum->sum('BCABS01');
    $totalBcabs02     = $queryForSum->sum('BCABS02');
    $totalSertifikat  = $queryForSum->sum('sertifikat');
    $totalStpb        = $queryForSum->sum('stpb');
    $totalEvent       = $queryForSum->sum('event');
    $totalLainLain    = $queryForSum->sum('lain_lain');

    // Data untuk Autocomplete Nama Murid
    $muridQuery = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru', 'keluar']);

if (!empty($bimbaUnit)) {
    $muridQuery->where('bimba_unit', $bimbaUnit);
}

$muridList = $muridQuery
    ->select('nim', 'nama as nama_murid')
    ->orderBy('nama_murid')
    ->get();

    // Unit List untuk Admin
    $unitList = Unit::orderBy('biMBA_unit')
        ->pluck('biMBA_unit', 'biMBA_unit')
        ->toArray();

    return view('penerimaan.index', compact(
        'penerimaan',
        'totalVoucher',
        'totalSpp',
        'totalKaosPendek',
        'totalKaosPanjang',
        'totalKpk',
        'totalTas',
        'totalRbas',
        'totalBcabs01',
        'totalBcabs02',
        'totalSertifikat',
        'totalStpb',
        'totalEvent',
        'totalLainLain',
        'muridList',     // ← Nama variabel ini harus sama dengan Blade
        'unitList',
        'perPage',
        'search',
        'bulan',
        'tahun',
        'bimbaUnit'
    ));
}

public function updateUkuranKaos(Request $request)
{
    $request->validate([
        'id'    => 'required|integer|exists:penerimaan,id',
        'type'  => 'required|in:pendek,panjang',
        'ukuran'=> 'nullable|string|max:255',
    ]);

    $allowedSizes = ['KAS', 'KAM', 'KAL', 'KAXL', 'KAXXL', 'KAXXXL', 'KAXXXLS'];

    $ukuranString = null;

    if ($request->filled('ukuran')) {
        // Pisahkan berdasarkan koma
        $sizes = array_filter(array_map('trim', explode(',', strtoupper($request->ukuran))));

        // Validasi setiap ukuran
        foreach ($sizes as $size) {
            if (!in_array($size, $allowedSizes)) {
                return response()->json([
                    'success' => false,
                    'message' => "Ukuran tidak valid: {$size}. Pilih dari: " . implode(', ', $allowedSizes)
                ], 422);
            }
        }

        // Hilangkan duplikat dan urutkan (opsional, agar rapi)
        $sizes = array_unique($sizes);
        sort($sizes);

        // Gabung kembali jadi string koma
        $ukuranString = implode(',', $sizes);
    }
    // Jika ukuran kosong → simpan null (artinya hapus ukuran)

    try {
        $penerimaan = Penerimaan::findOrFail($request->id);

        if ($request->type === 'pendek') {
            $penerimaan->ukuran_kaos_pendek = $ukuranString;
        } else {
            $penerimaan->ukuran_kaos_panjang = $ukuranString;
        }

        $penerimaan->save();

        return response()->json([
            'success' => true,
            'ukuran'  => $ukuranString ?: '-'
        ]);

    } catch (\Exception $e) {
        Log::error('Gagal update ukuran kaos: ' . $e->getMessage(), $request->all());

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
        ], 500);
    }
}

    // FUNGSI BARU: Mengambil daftar bulan yang sudah dibayar via AJAX
    public function getPaidMonths(Request $request)
{
    $nim = $request->nim;

    $data = Penerimaan::where('nim', $nim)
        ->whereNotNull('bulan_bayar')
        ->whereNotNull('tahun_bayar')
        ->get(['bulan_bayar', 'tahun_bayar']);

    $result = [];

    foreach ($data as $item) {

        $bulanArray = is_array($item->bulan_bayar)
            ? $item->bulan_bayar
            : json_decode($item->bulan_bayar, true);

        $tahunArray = is_array($item->tahun_bayar)
            ? $item->tahun_bayar
            : json_decode($item->tahun_bayar, true);

        if (!$bulanArray || !$tahunArray) {
            continue;
        }

        foreach ($bulanArray as $i => $bulan) {

            $tahun = $tahunArray[$i] ?? null;

            if ($bulan && $tahun) {
                $result[] = [
                    'bulan' => trim($bulan),
                    'tahun' => trim($tahun),
                ];
            }
        }
    }

    return response()->json($result);
}


    public function create(Request $request)
{
    // ==================== PREFILL DARI REGISTRASI ====================
    $selectedNim = $request->nim ?? $request->query('nim') ?? null;

    // Murid yang dipilih (untuk prefill otomatis)
    $selectedMurid = null;
    if ($selectedNim) {
        $selectedMurid = BukuInduk::where('nim', $selectedNim)->first();
    }

    // ==================== SEMUA MURID AKTIF/BARU ====================
    $murids = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru'])
        ->orderBy('nama')
        ->get()
        ->map(function ($murid) {
            $nominal = $this->cleanMoneyInput($murid->spp ?? 0);
            if ($nominal > 0 && $nominal < 1000) {
                $nominal *= 1000;
            }
            $murid->spp = $nominal;
            return $murid;
        });

    // ==================== VOUCHER ====================
    $vouchers = collect();
    if ($selectedNim) {
        $vouchers = VoucherLama::where('nim', $selectedNim)
            ->where('jumlah_voucher', '>', 0)
            ->where('status', 'penyerahan')
            ->orderBy('no_voucher')
            ->get();
    } else {
        $vouchers = VoucherLama::where('jumlah_voucher', '>', 0)
            ->where('status', 'penyerahan')
            ->orderBy('no_voucher')
            ->get();
    }

    // ==================== SPP LUNAS (VALIDASI) ====================
    $sppLunas = Penerimaan::where('spp', '>', 0)
        ->select('nim', 'bulan', 'tahun')
        ->get()
        ->map(fn($item) => strtolower("{$item->nim}-{$item->bulan}-{$item->tahun}"))
        ->unique()
        ->toArray();

    // ==================== UNIT & CABANG OPTIONS ====================
    $unitOptions = BukuInduk::query()
        ->whereNotNull('bimba_unit')
        ->whereRaw("TRIM(COALESCE(bimba_unit,'')) <> ''")
        ->selectRaw('TRIM(bimba_unit) as bimba_unit')
        ->distinct()
        ->orderBy('bimba_unit')
        ->pluck('bimba_unit')
        ->toArray();

    $cabangOptions = BukuInduk::query()
        ->whereNotNull('no_cabang')
        ->whereRaw("TRIM(COALESCE(no_cabang,'')) <> ''")
        ->selectRaw('TRIM(no_cabang) as no_cabang')
        ->distinct()
        ->orderBy('no_cabang')
        ->pluck('no_cabang')
        ->toArray();

    // ==================== HARGA KAOS ====================
    $hargaKaos = HargaSaptataruna::where('kategori', 'PENJUALAN')
        ->where('nama', 'LIKE', '%kaos%')
        ->get();

    $kaosPendekList = $hargaKaos->filter(function ($item) {
        return stripos($item->nama, 'pendek') !== false || 
               stripos($item->nama, 'lengan pendek') !== false;
    })->map(function ($item) {
        return [
            'kode'   => $item->kode,
            'nama'   => $item->nama,
            'harga'  => (float)$item->harga,
            'ukuran' => $this->extractUkuran($item->nama),
        ];
    })->values();

    $kaosPanjangList = $hargaKaos->filter(function ($item) {
        return stripos($item->nama, 'panjang') !== false || 
               stripos($item->nama, 'lengan panjang') !== false;
    })->map(function ($item) {
        return [
            'kode'   => $item->kode,
            'nama'   => $item->nama,
            'harga'  => (float)$item->harga,
            'ukuran' => $this->extractUkuran($item->nama),
        ];
    })->values();

    // ==================== DAFTAR LIST ====================
    $daftarList = HargaSaptataruna::whereIn('kode', ['bA', 'Eb'])
        ->orWhere('nama', 'LIKE', '%biMBA-AIUEO%')
        ->orWhere('nama', 'LIKE', '%English biMBA%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'          => $item->kode,
                'nama'          => $item->nama,
                'harga_duafa'   => (float)($item->duafa ?? 0),
                'harga_promo'   => (float)($item->promo_2019 ?? 0),
                'harga_daftar'  => (float)($item->daftar_ulang ?? 0),
                'harga_spesial' => (float)($item->spesial ?? 0),
                'harga_umum1'   => (float)($item->umum1 ?? 0),
                'harga_promo_umum'   => (float)($item->promo_umum ?? 0),
            ];
        });

    // ==================== KPK ====================
    $kpkList = HargaSaptataruna::where('nama', 'LIKE', '%KPK%')
        ->orWhere('kode', 'LIKE', '%KPK%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    // ==================== TAS ====================
    $tasList = HargaSaptataruna::where('nama', 'LIKE', '%TAS%')
        ->orWhere('kode', 'LIKE', '%TAS%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    // ==================== SERTIFIKAT ====================
    $sertifikatList = HargaSaptataruna::where('nama', 'LIKE', '%SERTIFIKAT%')
        ->orWhere('nama', 'LIKE', '%STF%')
        ->orWhere('kode', 'LIKE', '%STF%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    // ==================== STPB ====================
    $stpbList = HargaSaptataruna::where('nama', 'LIKE', '%STPB%')
        ->orWhere('kode', 'LIKE', '%STPB%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    // ==================== RBAS ====================
    $rbasList = HargaSaptataruna::where('nama', 'LIKE', '%RBAS%')
        ->orWhere('kode', 'LIKE', '%RBAS%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    // ==================== BCABS01 & BCABS02 ====================
    $bcabs01List = HargaSaptataruna::where('kode', 'BCABS.01')
        ->orWhere('kode', 'LIKE', '%BCABS01%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    $bcabs02List = HargaSaptataruna::where('kode', 'BCABS.02')
        ->orWhere('kode', 'LIKE', '%BCABS02%')
        ->orderBy('nama')
        ->get()
        ->map(function ($item) {
            return [
                'kode'  => $item->kode,
                'nama'  => $item->nama,
                'harga' => (float)$item->harga,
            ];
        })->values();

    return view('penerimaan.create', compact(
        'murids',
        'selectedMurid',     // ← Untuk prefill otomatis
        'selectedNim',       // ← Untuk prefill otomatis
        'vouchers',
        'sppLunas',
        'unitOptions',
        'cabangOptions',
        'kaosPendekList',
        'kaosPanjangList',
        'daftarList',
        'kpkList',
        'tasList',
        'sertifikatList',
        'stpbList',
        'rbasList',
        'bcabs01List',
        'bcabs02List',
    ));
}

private function extractUkuran($nama)
{
    if (preg_match('/\((S|M|L|XL|XXL|XXXL)\)/i', $nama, $matches)) {
        return strtoupper($matches[1]);
    }
    return 'Standar';
}

public function store(Request $request)
{
    // === BERSIHKAN NOMINAL ===
    $spp         = $this->cleanMoneyInput($request->spp);
    $daftar      = $this->cleanMoneyInput($request->daftar);
    $kaosPendek  = $this->cleanMoneyInput($request->kaos_pendek);
    $kaosPanjang = $this->cleanMoneyInput($request->kaos_panjang);
    $kpk         = $this->cleanMoneyInput($request->kpk);
    $sertifikat  = $this->cleanMoneyInput($request->sertifikat);
    $stpb        = $this->cleanMoneyInput($request->stpb);
    $tas         = $this->cleanMoneyInput($request->tas);
    $event       = $this->cleanMoneyInput($request->event);
    $lainLain    = $this->cleanMoneyInput($request->lain_lain);
    $bcabs01     = $this->cleanMoneyInput($request->BCABS01);
    $bcabs02     = $this->cleanMoneyInput($request->BCABS02);

    if ($spp > 0 && $spp < 1000) $spp *= 1000;

    // ================================================
    // PROSES KAOS PENDEK & PANJANG (MULTIPLE UKURAN)
    // ================================================
    $kaosPendekKode = $request->input('kaos_pendek_kode', []);
    $kaosPendekQty  = $request->input('kaos_pendek_qty', []);

    $kaosPanjangKode = $request->input('kaos_panjang_kode', []);
    $kaosPanjangQty  = $request->input('kaos_panjang_qty', []);

    // Proses Kaos Pendek
    $ukuranKaosPendekString = '';
    $kaosPendekDetails = [];
    $kaosPendekKodeArray = [];

    foreach ($kaosPendekKode as $i => $kode) {
        if (!empty($kode) && isset($kaosPendekQty[$i]) && $kaosPendekQty[$i] > 0) {
            $qty = (int)$kaosPendekQty[$i];
            $ukuranKaosPendekString .= $kode . ($qty > 1 ? "×{$qty}" : '') . ', ';
            $kaosPendekDetails[$kode] = ($kaosPendekDetails[$kode] ?? 0) + $qty;
            $kaosPendekKodeArray[] = $kode;
        }
    }
    $ukuranKaosPendekString = rtrim($ukuranKaosPendekString, ', ');

    // Proses Kaos Panjang
    $ukuranKaosPanjangString = '';
    $kaosPanjangDetails = [];
    $kaosPanjangKodeArray = [];

    foreach ($kaosPanjangKode as $i => $kode) {
        if (!empty($kode) && isset($kaosPanjangQty[$i]) && $kaosPanjangQty[$i] > 0) {
            $qty = (int)$kaosPanjangQty[$i];
            $ukuranKaosPanjangString .= $kode . ($qty > 1 ? "×{$qty}" : '') . ', ';
            $kaosPanjangDetails[$kode] = ($kaosPanjangDetails[$kode] ?? 0) + $qty;
            $kaosPanjangKodeArray[] = $kode;
        }
    }
    $ukuranKaosPanjangString = rtrim($ukuranKaosPanjangString, ', ');

    // === VALIDASI ===
    $rules = [
        'via'                => 'required|in:cash,transfer,edc',
        'tanggal'            => 'required|date',
        'nim'                => 'required|exists:buku_induk,nim',
        'nama_murid'         => 'required',
        'voucher'            => 'nullable|array',
        'voucher.*'          => 'string|distinct',
        'bulan_bayar.*'      => 'required_if:spp,>,0',
        'tahun_bayar.*'      => 'required_if:spp,>,0|numeric|min:2020',
    ];

    // Bukti Transfer WAJIB jika via = transfer
    if ($request->via === 'transfer') {
        $rules['bukti_transfer'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
    } else {
        $rules['bukti_transfer'] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
    }

    $request->validate($rules);

    // === DATA MURID ===
    $murid = BukuInduk::where('nim', $request->nim)->firstOrFail();

    $sppPerBulan = $this->cleanMoneyInput($murid->spp);
    if ($sppPerBulan > 0 && $sppPerBulan < 1000) $sppPerBulan *= 1000;

    $dataMurid = [
        'kelas'      => $murid->kelas ?? '',
        'status'     => $murid->status ?? 'aktif',
        'guru'       => $murid->guru ?? '',
        'gol'        => $murid->gol ?? '',
        'kd'         => $murid->kd ?? '',
        'bimba_unit' => $murid->bimba_unit ?? null,
        'no_cabang'  => $murid->no_cabang ?? null,
        'RBAS'       => $murid->RBAS ?? null,
        'BCABS01'    => $murid->BCABS01 ?? null,
        'BCABS02'    => $murid->BCABS02 ?? null,
    ];

    $rbas    = $this->cleanMoneyInput($request->RBAS)    ?: ($dataMurid['RBAS'] ?? 0);
    $bcabs01 = $this->cleanMoneyInput($request->BCABS01) ?: ($dataMurid['BCABS01'] ?? 0);
    $bcabs02 = $this->cleanMoneyInput($request->BCABS02) ?: ($dataMurid['BCABS02'] ?? 0);

    // === PERIODE SPP ===
    $periodeTerpilih = [];
    if ($spp > 0) {
        foreach (($request->bulan_bayar ?? []) as $i => $bulan) {
            if (!empty($bulan) && !empty($request->tahun_bayar[$i] ?? null)) {
                $key = strtolower($bulan) . '-' . $request->tahun_bayar[$i];
                $periodeTerpilih[$key] = [
                    'bulan' => trim($bulan),
                    'tahun' => (int)$request->tahun_bayar[$i]
                ];
            }
        }

        if (empty($periodeTerpilih)) {
            return back()->withErrors(['bulan_bayar' => 'Pilih minimal satu bulan untuk SPP!'])->withInput();
        }

        foreach ($periodeTerpilih as $bt) {
            $exists = Penerimaan::where('nim', $request->nim)
                ->whereRaw('LOWER(TRIM(bulan)) = ?', [strtolower($bt['bulan'])])
                ->where('tahun', $bt['tahun'])
                ->where('spp', '>', 0)
                ->exists();

            if ($exists) {
                return back()->withErrors(['bulan_bayar' => "SPP {$bt['bulan']} {$bt['tahun']} sudah dibayar!"])->withInput();
            }
        }
    }

    // === HITUNG TOTAL ===
    $totalBiayaLain = $daftar + $kaosPendek + $kaosPanjang + $kpk + $sertifikat + $stpb + $tas + $event + $lainLain + $rbas + $bcabs01 + $bcabs02;
    $adaBiayaLain   = $totalBiayaLain > 0;
    $kelebihanSpp   = $spp > 0 ? ($spp - count($periodeTerpilih) * $sppPerBulan) : 0;

    // === VOUCHER ===
    $voucherTerpakai = [];
    $diskonPerVoucher = 50000;

    if ($request->has('voucher') && is_array($request->voucher)) {
        $voucherDipilih = array_filter($request->voucher);

        if (count($voucherDipilih) > count($periodeTerpilih)) {
            return back()->withErrors(['voucher' => 'Jumlah voucher melebihi jumlah bulan SPP'])->withInput();
        }

        foreach ($voucherDipilih as $noVoucher) {
            $v = VoucherLama::where('no_voucher', $noVoucher)
                ->where('status', 'penyerahan')
                ->where('jumlah_voucher', '>', 0)
                ->where('nim', $request->nim)
                ->first();

            if (!$v) {
                return back()->withErrors(['voucher' => "Voucher {$noVoucher} tidak ditemukan atau bukan milik murid ini."])->withInput();
            }

            $v->decrement('jumlah_voucher', 1);

            VoucherHistori::create([
                'voucher_lama_id'   => $v->id,
                'nim'               => $request->nim,
                'nama_murid'        => $request->nama_murid,
                'tanggal'           => $request->tanggal,
                'tanggal_pemakaian' => $request->tanggal,
                'jumlah_voucher'    => 1,
                'voucher'           => $diskonPerVoucher,
                'status'            => 'digunakan',
            ]);

            $voucherTerpakai[] = $v->id;
        }
    }

    $buktiPath = $request->hasFile('bukti_transfer')
        ? $request->file('bukti_transfer')->store('bukti_transfer', 'public')
        : null;

    $tanggal = Carbon::parse($request->tanggal);

    // ==================================================================
    // GENERATE KWITANSI & SIMPAN DATA
    // ==================================================================
    DB::beginTransaction();
    try {
        $kwitansiList = [];
        $index = 1;
        $firstSppId = null;

        // Generate Base Kwitansi
        $isManual = $request->boolean('manual_kwitansi') && $request->filled('kwitansi_base_manual');
        if ($isManual) {
            $kwitansiBase = trim($request->kwitansi_base_manual);
            $kwitansiBase = preg_replace('/-\d+$/', '', $kwitansiBase);
        } else {
            $nimLast3 = str_pad(substr((string)$request->nim, -3), 3, '0', STR_PAD_LEFT);
            $tahun2   = str_pad($tanggal->year % 100, 2, '0', STR_PAD_LEFT);
            $bulan2   = str_pad($tanggal->month, 2, '0', STR_PAD_LEFT);
            $tanggal2 = str_pad($tanggal->day, 2, '0', STR_PAD_LEFT);
            $kwitansiBase = "KW{$nimLast3}{$tahun2}{$bulan2}{$tanggal2}";
        }

        // ====================== LOGIKA BARU ======================
        $combineSPPAndLain = ($spp > 0 && count($periodeTerpilih) === 1 && $adaBiayaLain);

        if ($combineSPPAndLain) {
            // === SPP + BIAYA LAIN → 1 KWITANSI ===
            $bt = reset($periodeTerpilih);
            $diskon = !empty($voucherTerpakai) ? $diskonPerVoucher : 0;

            $kwitansi = $isManual ? $kwitansiBase : $kwitansiBase . str_pad($index, 2, '0', STR_PAD_LEFT);

            $p = Penerimaan::create([
                'kwitansi'                => $kwitansi,
                'via'                     => $request->via,
                'tanggal'                 => $request->tanggal,
                'nim'                     => $request->nim,
                'nama_murid'              => $request->nama_murid,
                'kelas'                   => $dataMurid['kelas'],
                'status'                  => $dataMurid['status'],
                'guru'                    => $dataMurid['guru'],
                'gol'                     => $dataMurid['gol'] ?? null,
                'kd'                      => $dataMurid['kd'] ?? null,
                'bulan'                   => $bt['bulan'],
                'tahun'                   => $bt['tahun'],
                'spp'                     => $sppPerBulan,
                'voucher'                 => $diskon,
                'daftar'                  => $daftar,
                'kaos'                    => $kaosPendek,
                'kaos_lengan_panjang'     => $kaosPanjang,
                'ukuran_kaos_pendek'      => $ukuranKaosPendekString,
                'ukuran_kaos_panjang'     => $ukuranKaosPanjangString,
                'kaos_pendek_details'     => !empty($kaosPendekDetails) ? $kaosPendekDetails : null,
                'kaos_panjang_details'    => !empty($kaosPanjangDetails) ? $kaosPanjangDetails : null,
                'kpk'                     => $kpk,
                'sertifikat'              => $sertifikat,
                'stpb'                    => $stpb,
                'tas'                     => $tas,
                'event'                   => $event,
                'lain_lain'               => $lainLain,
                'RBAS'                    => $rbas,
                'BCABS01'                 => $bcabs01,
                'BCABS02'                 => $bcabs02,
                'total'                   => ($sppPerBulan - $diskon) + $totalBiayaLain,
                'bimba_unit'              => $dataMurid['bimba_unit'],
                'no_cabang'               => $dataMurid['no_cabang'],
                'bukti_transfer_path'     => $buktiPath,
                'is_manual_kwitansi'      => $isManual,
                'kaos_pendek_kode'        => !empty($kaosPendekKodeArray) ? implode(',', $kaosPendekKodeArray) : null,
                'kaos_panjang_kode'       => !empty($kaosPanjangKodeArray) ? implode(',', $kaosPanjangKodeArray) : null,
            ]);

            $firstSppId = $p->id;
            $kwitansiList[] = $kwitansi;
            $index++;

        } else {
            // === LOGIKA LAMA (SPP Multiple Bulan atau Hanya SPP atau Hanya Biaya Lain) ===

            // 1. SPP
            foreach ($periodeTerpilih as $bt) {
                $diskon = isset($voucherTerpakai[$index - 1]) ? $diskonPerVoucher : 0;

                $kwitansi = ($isManual && $totalItem === 1) 
                            ? $kwitansiBase 
                            : $kwitansiBase . str_pad($index, 2, '0', STR_PAD_LEFT);

                $p = Penerimaan::create([
                    'kwitansi'               => $kwitansi,
                    'via'                    => $request->via,
                    'tanggal'                => $request->tanggal,
                    'nim'                    => $request->nim,
                    'nama_murid'             => $request->nama_murid,
                    'kelas'                  => $dataMurid['kelas'],
                    'status'                 => $dataMurid['status'],
                    'guru'                   => $dataMurid['guru'],
                    'gol'                    => $dataMurid['gol'] ?? null,
                    'kd'                     => $dataMurid['kd'] ?? null,
                    'bulan'                  => $bt['bulan'],
                    'tahun'                  => $bt['tahun'],
                    'spp'                    => $sppPerBulan,
                    'voucher'                => $diskon,
                    'total'                  => $sppPerBulan - $diskon,
                    'bimba_unit'             => $dataMurid['bimba_unit'],
                    'no_cabang'              => $dataMurid['no_cabang'],
                    'RBAS'                   => $rbas,
                    'BCABS01'                => $bcabs01,
                    'BCABS02'                => $bcabs02,
                    'bukti_transfer_path'    => $buktiPath,
                    'is_manual_kwitansi'     => $isManual,
                ]);

                if (!$firstSppId) $firstSppId = $p->id;
                $kwitansiList[] = $kwitansi;
                $index++;
            }

            // 4. BIAYA LAIN (jika ada)
            if ($adaBiayaLain) {
                $kwitansi = ($isManual && $totalItem === 1) 
                            ? $kwitansiBase 
                            : $kwitansiBase . str_pad($index, 2, '0', STR_PAD_LEFT);

                Penerimaan::create([
                    'kwitansi'                => $kwitansi,
                    'via'                     => $request->via,
                    'tanggal'                 => $request->tanggal,
                    'nim'                     => $request->nim,
                    'nama_murid'              => $request->nama_murid,
                    'kelas'                   => $dataMurid['kelas'],
                    'status'                  => $dataMurid['status'],
                    'guru'                    => $dataMurid['guru'],
                    'gol'                     => $dataMurid['gol'] ?? null,
                    'kd'                      => $dataMurid['kd'] ?? null,
                    'daftar'                  => $daftar,
                    'kaos'                    => $kaosPendek,
                    'kaos_lengan_panjang'     => $kaosPanjang,
                    'ukuran_kaos_pendek'      => $ukuranKaosPendekString,
                    'ukuran_kaos_panjang'     => $ukuranKaosPanjangString,
                    'kaos_pendek_details'     => !empty($kaosPendekDetails) ? $kaosPendekDetails : null,
                    'kaos_panjang_details'    => !empty($kaosPanjangDetails) ? $kaosPanjangDetails : null,
                    'kpk'                     => $kpk,
                    'sertifikat'              => $sertifikat,
                    'stpb'                    => $stpb,
                    'tas'                     => $tas,
                    'event'                   => $event,
                    'lain_lain'               => $lainLain,
                    'RBAS'                    => $rbas,
                    'BCABS01'                 => $bcabs01,
                    'BCABS02'                 => $bcabs02,
                    'total'                   => $totalBiayaLain,
                    'bimba_unit'              => $dataMurid['bimba_unit'],
                    'no_cabang'               => $dataMurid['no_cabang'],
                    'bukti_transfer_path'     => $buktiPath,
                    'is_manual_kwitansi'      => $isManual,
                    'kaos_pendek_kode'        => !empty($kaosPendekKodeArray) ? implode(',', $kaosPendekKodeArray) : null,
                    'kaos_panjang_kode'       => !empty($kaosPanjangKodeArray) ? implode(',', $kaosPanjangKodeArray) : null,
                ]);

                $kwitansiList[] = $kwitansi;
                $index++;
            }
        }

        // === DEPOSIT KELEBIHAN SPP ===
        if ($kelebihanSpp > 0) {
            $last = collect($periodeTerpilih)->last();
            $bulanDeposit = Carbon::create($last['tahun'], $this->bulanKeAngka($last['bulan']))->addMonth();

            DaftarMuridDeposit::create([
                'tanggal_transaksi'   => $tanggal->toDateString(),
                'nim'                 => $request->nim,
                'nama_murid'          => $request->nama_murid,
                'jumlah_deposit'      => $kelebihanSpp,
                'kategori_deposit'    => 'Deposit ' . $this->namaBulanIndonesia($bulanDeposit->month) . ' ' . $bulanDeposit->year,
                'status_deposit'      => 'Aktif',
                'keterangan_deposit'  => 'Kelebihan bayar SPP',
                'penerimaan_id'       => $firstSppId,
                'kwitansi'            => $kwitansiList[0] ?? null,
                'bimba_unit'          => $dataMurid['bimba_unit'],
                'no_cabang'           => $dataMurid['no_cabang'],
            ]);
        }

        // === DHUAFA / GRATIS ===
        if ($spp == 0 && !$adaBiayaLain) {
            $kwitansi = ($isManual && $totalItem === 1) 
                        ? $kwitansiBase 
                        : $kwitansiBase . str_pad($index, 2, '0', STR_PAD_LEFT);

            Penerimaan::create([
                'kwitansi'            => $kwitansi,
                'via'                 => $request->via,
                'tanggal'             => $request->tanggal,
                'nim'                 => $request->nim,
                'nama_murid'          => $request->nama_murid,
                'kelas'               => $dataMurid['kelas'],
                'status'              => $dataMurid['status'],
                'guru'                => $dataMurid['guru'],
                'gol'                 => $dataMurid['gol'] ?? null,
                'kd'                  => $dataMurid['kd'] ?? null,
                'bulan'               => $request->bulan_bayar[0] ?? null,
                'tahun'               => $request->tahun_bayar[0] ?? null,
                'spp'                 => 0,
                'voucher'             => 0,
                'total'               => 0,
                'keterangan'          => 'Dhuafa / Gratis',
                'bimba_unit'          => $dataMurid['bimba_unit'],
                'no_cabang'           => $dataMurid['no_cabang'],
                'RBAS'                => $rbas,
                'BCABS01'             => $bcabs01,
                'BCABS02'             => $bcabs02,
                'bukti_transfer_path' => $buktiPath,
                'is_manual_kwitansi'  => $isManual,
            ]);

            $kwitansiList[] = $kwitansi;
        }

        DB::commit();

        return redirect()->route('penerimaan.index')
            ->with('success', 'Pembayaran berhasil! Kwitansi: ' . implode(', ', $kwitansiList));

    } catch (\Exception $e) {
        DB::rollBack();
        if ($buktiPath) {
            Storage::disk('public')->delete($buktiPath);
        }
        return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()])->withInput();
    }
}

    private function bulanKeAngka($bulanNama)
{
    $map = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12
    ];
    return $map[strtolower($bulanNama)] ?? 1;
}

    private function namaBulanIndonesia($bulan)
    {
        $nama = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        return $nama[$bulan] ?? 'Bulan Tidak Valid';
    }



    public function edit(Penerimaan $penerimaan)
{
    // Murid aktif/baru + normalisasi SPP
    $murids = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru'])
        ->orderBy('nama')
        ->get()
        ->map(function ($m) {
            $nominal = $this->cleanMoneyInput($m->spp);
            if ($nominal > 0 && $nominal < 1000) {
                $nominal *= 1000;
            }
            $m->spp = $nominal;
            return $m;
        });

    // ==================== DAFTAR LIST ====================
$daftarList = HargaSaptataruna::where(function($q) {
        $q->whereIn('kode', ['bA', 'Eb'])
          ->orWhere('nama', 'LIKE', '%biMBA-AIUEO%');
    })
    ->where('nama', 'NOT LIKE', '%English%')   // ← Tambahkan ini
    ->orderBy('nama')
    ->get()
    ->map(function ($item) {
        return [
            'kode'          => $item->kode,
            'nama'          => $item->nama,
            'harga_duafa'   => (float)($item->duafa ?? 0),
            'harga_promo'   => (float)($item->promo_2019 ?? 0),
            'harga_daftar'  => (float)($item->daftar_ulang ?? 0),
            'harga_spesial' => (float)($item->spesial ?? 0),
            'harga_umum1'   => (float)($item->umum1 ?? 0),
            'harga_promo_umum' => (float)($item->promo_umum ?? 0),
        ];
    });

    // ==================== HARGA KAOS ====================
    $hargaKaos = HargaSaptataruna::where('kategori', 'PENJUALAN')
        ->where('nama', 'LIKE', '%kaos%')
        ->get();

    $kaosPendekList = $hargaKaos->filter(function ($item) {
        return stripos($item->nama, 'pendek') !== false || 
               stripos($item->nama, 'lengan pendek') !== false;
    })->map(function ($item) {
        return ['kode' => $item->kode, 'harga' => (float)$item->harga];
    })->values();

    $kaosPanjangList = $hargaKaos->filter(function ($item) {
        return stripos($item->nama, 'panjang') !== false || 
               stripos($item->nama, 'lengan panjang') !== false;
    })->map(function ($item) {
        return ['kode' => $item->kode, 'harga' => (float)$item->harga];
    })->values();

    // ==================== LIST LAINNYA ====================
    $kpkList       = $this->getHargaList('KPK');
    $tasList       = $this->getHargaList('TAS');
    $sertifikatList= $this->getHargaList('SERTIFIKAT');
    $stpbList      = $this->getHargaList('STPB');
    $rbasList      = $this->getHargaList('RBAS');
    $bcabs01List   = $this->getHargaList('BCABS.01');
    $bcabs02List   = $this->getHargaList('BCABS.02');

    $vouchers = VoucherLama::where(function ($q) use ($penerimaan) {
        $q->where('jumlah_voucher', '>', 0)
          ->orWhere('no_voucher', $penerimaan->voucher ?? ''); 
    })
    ->where(function ($q) use ($penerimaan) {
        $q->whereNull('nim')->orWhere('nim', $penerimaan->nim);
    })
    ->select('id', 'no_voucher', 'jumlah_voucher')
    ->orderBy('no_voucher')
    ->get();

        $existingKaosPendek  = $penerimaan->ukuran_kaos_pendek;
    $existingKaosPanjang = $penerimaan->ukuran_kaos_panjang;

    // Existing data untuk semua item
    $existingKpk        = $penerimaan->kpk ?? $penerimaan->kpk_kode ?? '';
    $existingTas        = $penerimaan->tas ?? $penerimaan->tas_kode ?? '';
    $existingSertifikat = $penerimaan->sertifikat ?? $penerimaan->sertifikat_kode ?? '';
    $existingStpb       = $penerimaan->stpb ?? $penerimaan->stpb_kode ?? '';
    $existingRbas       = $penerimaan->RBAS ?? $penerimaan->rbas ?? $penerimaan->rbas_kode ?? 0;  // ← Perbaikan utama
    $existingBcabs01    = $penerimaan->BCABS01 ?? $penerimaan->bcabs01_kode ?? '';
    $existingBcabs02    = $penerimaan->BCABS02 ?? $penerimaan->bcabs02_kode ?? '';

    return view('penerimaan.edit', compact(
        'penerimaan',
        'murids',
        'daftarList',
        'kaosPendekList',
        'kaosPanjangList',
        'kpkList',
        'tasList',
        'sertifikatList',
        'stpbList',
        'rbasList',
        'bcabs01List',
        'bcabs02List',
        'vouchers',
        'existingKaosPendek',
        'existingKaosPanjang',
        'existingKpk',
        'existingTas',
        'existingSertifikat',
        'existingStpb',
        'existingRbas',
        'existingBcabs01',
        'existingBcabs02'
    ));
}
// Helper baru (opsional, taruh di class)
private function getHargaList($keyword)
{
    return HargaSaptataruna::where('nama', 'LIKE', "%{$keyword}%")
        ->orWhere('kode', 'LIKE', "%{$keyword}%")
        ->orderBy('nama')
        ->get()
        ->map(fn($item) => [
            'kode'  => $item->kode,
            'harga' => (float)$item->harga,
        ])->values();
}

public function update(Request $request, Penerimaan $penerimaan)
{
    DB::beginTransaction();
    try {
        // Clean Input
        $spp         = $this->cleanMoneyInput($request->input('spp'));
        $daftar      = $this->cleanMoneyInput($request->input('daftar'));
        $kaosPendek  = $this->cleanMoneyInput($request->input('kaos_pendek'));
        $kaosPanjang = $this->cleanMoneyInput($request->input('kaos_panjang'));
        $kpk         = $this->cleanMoneyInput($request->input('kpk'));
        $tas         = $this->cleanMoneyInput($request->input('tas'));
        $sertifikat  = $this->cleanMoneyInput($request->input('sertifikat'));
        $stpb        = $this->cleanMoneyInput($request->input('stpb'));
        $event       = $this->cleanMoneyInput($request->input('event'));
        $lainLain    = $this->cleanMoneyInput($request->input('lain_lain'));

        if ($spp > 0 && $spp < 1000) $spp *= 1000;

        // ==================== PROSES KAOS ====================
        $kaosPendekKode = $request->input('kaos_pendek_kode', []);
        $kaosPendekQty  = $request->input('kaos_pendek_qty', []);

        $kaosPanjangKode = $request->input('kaos_panjang_kode', []);
        $kaosPanjangQty  = $request->input('kaos_panjang_qty', []);

        $kaosPendekDetails = [];
        $ukuranPendekString = '';
        $kaosPendekKodeArray = [];

        foreach ($kaosPendekKode as $i => $kode) {
            if (!empty($kode) && isset($kaosPendekQty[$i]) && $kaosPendekQty[$i] > 0) {
                $qty = (int)$kaosPendekQty[$i];
                $kaosPendekDetails[$kode] = ($kaosPendekDetails[$kode] ?? 0) + $qty;
                $ukuranPendekString .= $kode . ($qty > 1 ? "×{$qty}" : '') . ', ';
                $kaosPendekKodeArray[] = $kode;
            }
        }
        $ukuranPendekString = rtrim($ukuranPendekString, ', ');

        $kaosPanjangDetails = [];
        $ukuranPanjangString = '';
        $kaosPanjangKodeArray = [];

        foreach ($kaosPanjangKode as $i => $kode) {
            if (!empty($kode) && isset($kaosPanjangQty[$i]) && $kaosPanjangQty[$i] > 0) {
                $qty = (int)$kaosPanjangQty[$i];
                $kaosPanjangDetails[$kode] = ($kaosPanjangDetails[$kode] ?? 0) + $qty;
                $ukuranPanjangString .= $kode . ($qty > 1 ? "×{$qty}" : '') . ', ';
                $kaosPanjangKodeArray[] = $kode;
            }
        }
        $ukuranPanjangString = rtrim($ukuranPanjangString, ', ');

        // ==================== UPDATE ====================
        $updateData = [
            'via'                     => $request->via,
            'tanggal'                 => $request->tanggal,
            'nama_murid'              => $request->nama_murid,
            'spp'                     => $spp,
            'daftar'                  => $daftar,
            'kaos'                    => $kaosPendek,
            'kaos_lengan_panjang'     => $kaosPanjang,
            'kpk'                     => $kpk,
            'tas'                     => $tas,
            'sertifikat'              => $sertifikat,
            'stpb'                    => $stpb,
            'event'                   => $event,
            'lain_lain'               => $lainLain,
            'voucher'                 => $request->voucher,
            'RBAS'                    => $this->cleanMoneyInput($request->RBAS),
            'BCABS01'                 => $this->cleanMoneyInput($request->BCABS01),
            'BCABS02'                 => $this->cleanMoneyInput($request->BCABS02),

            // Kaos Details
            'ukuran_kaos_pendek'      => $ukuranPendekString,
            'ukuran_kaos_panjang'     => $ukuranPanjangString,
            'kaos_pendek_details'     => !empty($kaosPendekDetails) ? $kaosPendekDetails : null,
            'kaos_panjang_details'    => !empty($kaosPanjangDetails) ? $kaosPanjangDetails : null,
            'kaos_pendek_kode'        => !empty($kaosPendekKodeArray) ? implode(',', $kaosPendekKodeArray) : null,
            'kaos_panjang_kode'       => !empty($kaosPanjangKodeArray) ? implode(',', $kaosPanjangKodeArray) : null,
        ];

        $total = $spp + $daftar + $kaosPendek + $kaosPanjang + $kpk + $tas + 
                 $sertifikat + $stpb + $event + $lainLain + 
                 $updateData['RBAS'] + $updateData['BCABS01'] + $updateData['BCABS02'] - 
                 ($request->voucher ? 50000 : 0);

        $updateData['total'] = max(0, $total);

        $penerimaan->update($updateData);

        // Update Bukti Transfer
        if ($request->hasFile('bukti_transfer')) {
            $this->deleteBuktiTransferFile($penerimaan->bukti_transfer_path);
            $penerimaan->bukti_transfer_path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $penerimaan->save();
        }

        DB::commit();

        return redirect()->route('penerimaan.index')
            ->with('success', 'Data penerimaan berhasil diperbarui.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
}
    public function destroy(Penerimaan $penerimaan)
    {
        // Perluas logika destroy jika Anda ingin mengembalikan voucher yang sudah terpakai

        // 1. Cek apakah ada voucher yang digunakan pada transaksi ini
        $kodeVoucher = $penerimaan->voucher;
        $nim = $penerimaan->nim;

        DB::beginTransaction();
        try {
            // Hapus data Penerimaan
            $penerimaan->delete();

            // 2. Jika kode voucher ada dan transaksi ini mencakup nominal voucher > 0 (asumsi 50000)
            // Logika ini hanya relevan jika Anda menyimpan kode voucher yang digunakan di tabel Penerimaan
            // atau jika Anda ingin mengembalikan voucher yang terpakai di VoucherHistori

            // Asumsi: Kita mencari histori yang dibuat saat transaksi ini
            $voucherHistori = VoucherHistori::where('nim', $nim)
                ->where('voucher', 50000) // Nominal per voucher
                ->where('tanggal_pemakaian', $penerimaan->tanggal) // Mungkin perlu penyesuaian jika tanggal tidak unik
                ->first();

            if ($voucherHistori) {
                // 3. Kembalikan jumlah voucher di VoucherLama
                $voucherLama = VoucherLama::find($voucherHistori->voucher_lama_id);
                if ($voucherLama) {
                    $voucherLama->increment('jumlah_voucher', 1);
                    // Jika sebelumnya status 'Digunakan' dan sekarang jumlah > 0, ubah status
                    if ($voucherLama->status == 'Digunakan' && $voucherLama->jumlah_voucher > 0) {
                        $voucherLama->update(['status' => 'Aktif']);
                    }
                }
                // 4. Hapus histori penggunaan
                $voucherHistori->delete();
            }

            DB::commit();
            return redirect()->route('penerimaan.index')->with('success', 'Data penerimaan berhasil dihapus. Voucher (jika ada) telah dikembalikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('penerimaan.index')->with('error', 'Gagal menghapus data penerimaan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    ini_set('max_execution_time', 300);
    ini_set('memory_limit', '1024M');

    Excel::import(
        new PenerimaanImport,
        $request->file('file')
    );

    return back()->with(
        'success',
        'Import penerimaan berhasil'
    );
}
    public function updateBulanTahun(Request $request, $id)
    {
        $request->validate([
            'bulan' => 'required|string',
            'tahun' => 'required|integer',
        ]);

        $penerimaan = Penerimaan::findOrFail($id);
        $penerimaan->bulan = $request->bulan;
        $penerimaan->tahun = $request->tahun;
        $penerimaan->save();

        return redirect()->route('penerimaan.index')->with('success', 'Bulan & Tahun berhasil diperbarui!');
    }
    public function updateTahun(Request $request, $id)
    {
        $request->validate([
            'tahun' => 'required|numeric',
        ]);

        $penerimaan = Penerimaan::findOrFail($id);
        $penerimaan->tahun = $request->tahun;
        $penerimaan->save();

        return back()->with('success', 'Tahun berhasil diperbarui');
    }
    public function spp(Request $request)
{
    $perPage       = (int) $request->input('per_page', 10);
    $search        = trim($request->input('search'));
    $unitKode      = $request->input('unit');
    $periodeDari   = $request->input('periode_dari');
    $periodeSampai = $request->input('periode_sampai');

    // Dropdowns
    $unitList = Unit::withoutGlobalScopes()->orderBy('biMBA_unit')->get();

    $muridQuery = Penerimaan::query()
        ->select('nim', 'nama_murid')
        ->whereNotNull('nama_murid')
        ->whereRaw("TRIM(nama_murid) <> ''")
        ->whereNotNull('nim');

    if ($unitKode) {
        $muridQuery->where('bimba_unit', $unitKode);
    }

    $muridList = $muridQuery->distinct()->orderBy('nama_murid')->get();

    // ==================== QUERY UTAMA ====================
    $query = Penerimaan::query()
        ->where('spp', '>', 0)
        ->whereNotNull('spp');

    if ($unitKode) {
        $query->where('bimba_unit', $unitKode);
    }

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('nim', 'LIKE', "%{$search}%")
              ->orWhere('nama_murid', 'LIKE', "%{$search}%");
        });
    }

    // === FILTER PERIODE (tanggal + bulan/tahun) ===
    if ($periodeDari || $periodeSampai) {
        $query->where(function ($q) use ($periodeDari, $periodeSampai) {

            // 1. Kondisi Tanggal
            if (Schema::hasColumn('penerimaan', 'tanggal')) {
                $from = $periodeDari ? Carbon::createFromFormat('Y-m', $periodeDari)->startOfMonth()->toDateString() : null;
                $to   = $periodeSampai ? Carbon::createFromFormat('Y-m', $periodeSampai)->endOfMonth()->toDateString() : null;

                if ($from || $to) {
                    $q->orWhere(function ($sub) use ($from, $to) {
                        if ($from) $sub->where('tanggal', '>=', $from);
                        if ($to)   $sub->where('tanggal', '<=', $to);
                    });
                }
            }

            // 2. Kondisi Bulan + Tahun
            $q->orWhere(function ($sub) use ($periodeDari, $periodeSampai) {
                $rawCondition = "STR_TO_DATE(CONCAT(tahun, '-', LPAD(CASE 
                    WHEN LOWER(TRIM(bulan)) IN ('januari','jan') THEN 1
                    WHEN LOWER(TRIM(bulan)) IN ('februari','feb') THEN 2
                    WHEN LOWER(TRIM(bulan)) IN ('maret','mar') THEN 3
                    WHEN LOWER(TRIM(bulan)) IN ('april','apr') THEN 4
                    WHEN LOWER(TRIM(bulan)) IN ('mei','may') THEN 5
                    WHEN LOWER(TRIM(bulan)) IN ('juni','jun') THEN 6
                    WHEN LOWER(TRIM(bulan)) IN ('juli','jul') THEN 7
                    WHEN LOWER(TRIM(bulan)) IN ('agustus','agu','aug') THEN 8
                    WHEN LOWER(TRIM(bulan)) IN ('september','sep') THEN 9
                    WHEN LOWER(TRIM(bulan)) IN ('oktober','okt','oct') THEN 10
                    WHEN LOWER(TRIM(bulan)) IN ('november','nov') THEN 11
                    WHEN LOWER(TRIM(bulan)) IN ('desember','des','dec') THEN 12
                    ELSE 0 END, 2, '0'), '-01'), '%Y-%m-%d')";

                if ($periodeDari && $periodeSampai) {
                    $sub->whereRaw("$rawCondition BETWEEN ? AND ?", [
                        Carbon::createFromFormat('Y-m', $periodeDari)->startOfMonth()->toDateString(),
                        Carbon::createFromFormat('Y-m', $periodeSampai)->endOfMonth()->toDateString()
                    ]);
                } elseif ($periodeDari) {
                    $sub->whereRaw("$rawCondition >= ?", [
                        Carbon::createFromFormat('Y-m', $periodeDari)->startOfMonth()->toDateString()
                    ]);
                } elseif ($periodeSampai) {
                    $sub->whereRaw("$rawCondition <= ?", [
                        Carbon::createFromFormat('Y-m', $periodeSampai)->endOfMonth()->toDateString()
                    ]);
                }
            });
        });
    }

    // Total
    $totalSPP = (clone $query)->sum('spp');
    $totalVoucher = (clone $query)->where('voucher', '>', 0)->count() * 50000;

    // ==================== PAGINATION + SORTING BARU ====================
    $penerimaan = (clone $query)
        // === SORTIR UTAMA BERDASARKAN NIM (URUT NAIK) ===
        ->orderBy('nim', 'asc')
        
        // Secondary sort (opsional tapi direkomendasikan)
        ->orderByDesc('tanggal')                    // Tanggal terbaru di atas
        ->orderByRaw("CONCAT(tahun, '-', LPAD(CASE 
            WHEN LOWER(TRIM(bulan)) IN ('januari','jan') THEN 1
            WHEN LOWER(TRIM(bulan)) IN ('februari','feb') THEN 2
            WHEN LOWER(TRIM(bulan)) IN ('maret','mar') THEN 3
            WHEN LOWER(TRIM(bulan)) IN ('april','apr') THEN 4
            WHEN LOWER(TRIM(bulan)) IN ('mei','may') THEN 5
            WHEN LOWER(TRIM(bulan)) IN ('juni','jun') THEN 6
            WHEN LOWER(TRIM(bulan)) IN ('juli','jul') THEN 7
            WHEN LOWER(TRIM(bulan)) IN ('agustus','agu','aug') THEN 8
            WHEN LOWER(TRIM(bulan)) IN ('september','sep') THEN 9
            WHEN LOWER(TRIM(bulan)) IN ('oktober','okt','oct') THEN 10
            WHEN LOWER(TRIM(bulan)) IN ('november','nov') THEN 11
            WHEN LOWER(TRIM(bulan)) IN ('desember','des','dec') THEN 12
            ELSE 0 END, 2, '0')) DESC")
        
        ->paginate($perPage)
        ->appends($request->query());

    return view('penerimaan.spp', compact(
        'penerimaan', 'unitList', 'muridList', 'totalSPP', 'totalVoucher',
        'perPage', 'search', 'unitKode', 'periodeDari', 'periodeSampai'
    ));
}
public function produk(Request $request)
{
    $perPage = (int) $request->input('per_page', 10);

    // Input filter
    $search        = $request->input('search');                  // NIM murid yang dipilih
    $unitKode      = $request->input('unit');                     // Kode unit dari dropdown (misal: '01045')
    $periodeDari   = $request->filled('periode_dari') ? $request->input('periode_dari') : null;
    $periodeSampai = $request->filled('periode_sampai') ? $request->input('periode_sampai') : null;

    // === DROPDOWN BIMBA UNIT ===
    $unitList = Unit::withoutGlobalScopes()
        ->orderBy('biMBA_unit')
        ->get();

    // === DROPDOWN NAMA MURID — DINAMIS: IKUT FILTER UNIT ===
    $namaQuery = Penerimaan::query()
        ->select('nim', 'nama_murid')
        ->whereNotNull('nama_murid')
        ->whereRaw("TRIM(nama_murid) <> ''")
        ->whereNotNull('nim');

    // Jika unit sudah dipilih, hanya ambil murid dari unit tersebut
    if ($unitKode) {
        $namaQuery->where('bimba_unit', $unitKode);
    }

    $namaList = $namaQuery
        ->distinct()
        ->orderBy('nama_murid')
        ->get();

    // Query dasar: hanya transaksi yang ada produk
    $query = Penerimaan::query()
        ->where(function ($q) {
            $q->where('kaos', '>', 0)
                ->orWhere('kaos_lengan_panjang', '>', 0)
                ->orWhere('kpk', '>', 0)
                ->orWhere('tas', '>', 0)
                ->orWhere('sertifikat', '>', 0)
                ->orWhere('stpb', '>', 0)
                ->orWhere('event', '>', 0)
                ->orWhere('lain_lain', '>', 0)
                ->orWhere('RBAS', '>', 0)
                ->orWhere('BCABS01', '>', 0)
                ->orWhere('BCABS02', '>', 0);
        });

    // Filter pencarian murid (jika dipilih)
    if (!empty($search)) {
        $query->where('nim', $search); // karena dropdown pakai value = nim
    }

    // Filter unit
    if ($unitKode) {
        $query->where('bimba_unit', $unitKode);
    }

    // Filter periode
    if ($periodeDari || $periodeSampai) {
        $from = $periodeDari
            ? Carbon::createFromFormat('Y-m', $periodeDari)->startOfMonth()
            : Carbon::createFromDate(1900, 1, 1);

        $to = $periodeSampai
            ? Carbon::createFromFormat('Y-m', $periodeSampai)->endOfMonth()
            : Carbon::createFromDate(2999, 12, 31);

        if (Schema::hasColumn('penerimaan', 'tanggal')) {
            $query->whereBetween('tanggal', [$from, $to]);
        } else {
            $periodeExpr = DB::raw("STR_TO_DATE(CONCAT(tahun, '-', LPAD(CASE
                WHEN LOWER(TRIM(bulan))='januari' THEN 1 WHEN LOWER(TRIM(bulan))='februari' THEN 2 WHEN LOWER(TRIM(bulan))='maret' THEN 3
                WHEN LOWER(TRIM(bulan))='april' THEN 4 WHEN LOWER(TRIM(bulan))='mei' THEN 5 WHEN LOWER(TRIM(bulan))='juni' THEN 6
                WHEN LOWER(TRIM(bulan))='juli' THEN 7 WHEN LOWER(TRIM(bulan))='agustus' THEN 8 WHEN LOWER(TRIM(bulan))='september' THEN 9
                WHEN LOWER(TRIM(bulan))='oktober' THEN 10 WHEN LOWER(TRIM(bulan))='november' THEN 11 WHEN LOWER(TRIM(bulan))='desember' THEN 12
                END, 2, '0'), '-01'), '%Y-%m-%d')");

            $query->whereBetween($periodeExpr, [$from->format('Y-m-d'), $to->format('Y-m-d')]);
        }
    }

    // Ordering: terbaru dulu
    $listQuery = (clone $query)->orderByRaw(
        Schema::hasColumn('penerimaan', 'tanggal')
            ? 'tanggal DESC'
            : "CONCAT(tahun, '-', LPAD(CASE
                WHEN LOWER(TRIM(bulan))='januari' THEN 1 WHEN LOWER(TRIM(bulan))='februari' THEN 2 WHEN LOWER(TRIM(bulan))='maret' THEN 3
                WHEN LOWER(TRIM(bulan))='april' THEN 4 WHEN LOWER(TRIM(bulan))='mei' THEN 5 WHEN LOWER(TRIM(bulan))='juni' THEN 6
                WHEN LOWER(TRIM(bulan))='juli' THEN 7 WHEN LOWER(TRIM(bulan))='agustus' THEN 8 WHEN LOWER(TRIM(bulan))='september' THEN 9
                WHEN LOWER(TRIM(bulan))='oktober' THEN 10 WHEN LOWER(TRIM(bulan))='november' THEN 11 WHEN LOWER(TRIM(bulan))='desember' THEN 12
                END, 2, '0')) DESC"
    );

    // Harga satuan produk
    $hargaKaosPendek  = 70000;
    $hargaKaosPanjang = 85000;
    $hargaKpk         = 10000;
    $hargaTas         = 80000;
    $hargaSertifikat  = 15000;
    $hargaStpb        = 25000;
    $hargaEvent       = 100000;
    $hargaLainLain    = 30000;
    $hargaRbas        = 15000;
    $hargaBcabs01     = 15000;
    $hargaBcabs02     = 15000;

    // Data tabel + pagination
    $penerimaan = $listQuery
        ->selectRaw('penerimaan.*')
        ->selectRaw('FLOOR(COALESCE(kaos, 0) / ?) AS kaos_pendek_pcs', [$hargaKaosPendek])
        ->selectRaw('FLOOR(COALESCE(kaos_lengan_panjang, 0) / ?) AS kaos_panjang_pcs', [$hargaKaosPanjang])
        ->selectRaw('FLOOR(COALESCE(kpk, 0) / ?) AS kpk_pcs', [$hargaKpk])
        ->selectRaw('FLOOR(COALESCE(tas, 0) / ?) AS tas_pcs', [$hargaTas])
        ->selectRaw('FLOOR(COALESCE(sertifikat, 0) / ?) AS sertifikat_pcs', [$hargaSertifikat])
        ->selectRaw('FLOOR(COALESCE(stpb, 0) / ?) AS stpb_pcs', [$hargaStpb])
        ->selectRaw('FLOOR(COALESCE(event, 0) / ?) AS event_pcs', [$hargaEvent])
        ->selectRaw('FLOOR(COALESCE(lain_lain, 0) / ?) AS lainlain_pcs', [$hargaLainLain])
        ->selectRaw('FLOOR(COALESCE(RBAS, 0) / ?) AS rbas_pcs', [$hargaRbas])
        ->selectRaw('FLOOR(COALESCE(BCABS01, 0) / ?) AS bcabs01_pcs', [$hargaBcabs01])
        ->selectRaw('FLOOR(COALESCE(BCABS02, 0) / ?) AS bcabs02_pcs', [$hargaBcabs02])
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_kaos_pendek, '%d-%m-%Y') AS tgl_kaos_pendek_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_kaos_panjang, '%d-%m-%Y') AS tgl_kaos_panjang_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_kpk, '%d-%m-%Y') AS tgl_kpk_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_tas, '%d-%m-%Y') AS tgl_tas_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_rbas, '%d-%m-%Y') AS tgl_rbas_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_bcabs01, '%d-%m-%Y') AS tgl_bcabs01_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_bcabs02, '%d-%m-%Y') AS tgl_bcabs02_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_sertifikat, '%d-%m-%Y') AS tgl_sertifikat_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_stpb, '%d-%m-%Y') AS tgl_stpb_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_event, '%d-%m-%Y') AS tgl_event_fmt")
        ->selectRaw("DATE_FORMAT(tanggal_penyerahan_lainlain, '%d-%m-%Y') AS tgl_lainlain_fmt")
        ->paginate($perPage)
        ->appends($request->query());

    // === TOTAL PCS (SUDAH DIBAYAR) ===
    $totalKaosPendek  = (clone $query)->sum(DB::raw("FLOOR(COALESCE(kaos, 0) / $hargaKaosPendek)"));
    $totalKaosPanjang = (clone $query)->sum(DB::raw("FLOOR(COALESCE(kaos_lengan_panjang, 0) / $hargaKaosPanjang)"));
    $totalKpk         = (clone $query)->sum(DB::raw("FLOOR(COALESCE(kpk, 0) / $hargaKpk)"));
    $totalTas         = (clone $query)->sum(DB::raw("FLOOR(COALESCE(tas, 0) / $hargaTas)"));
    $totalSertifikat  = (clone $query)->sum(DB::raw("FLOOR(COALESCE(sertifikat, 0) / $hargaSertifikat)"));
    $totalStpb        = (clone $query)->sum(DB::raw("FLOOR(COALESCE(stpb, 0) / $hargaStpb)"));
    $totalEvent       = (clone $query)->sum(DB::raw("FLOOR(COALESCE(event, 0) / $hargaEvent)"));
    $totalLainLain    = (clone $query)->sum(DB::raw("FLOOR(COALESCE(lain_lain, 0) / $hargaLainLain)"));
    $totalRbas        = (clone $query)->sum(DB::raw("FLOOR(COALESCE(RBAS, 0) / $hargaRbas)"));
    $totalBcabs01     = (clone $query)->sum(DB::raw("FLOOR(COALESCE(BCABS01, 0) / $hargaBcabs01)"));
    $totalBcabs02     = (clone $query)->sum(DB::raw("FLOOR(COALESCE(BCABS02, 0) / $hargaBcabs02)"));

    $totalSemuaProdukPcs = $totalKaosPendek + $totalKaosPanjang + $totalKpk + $totalTas + $totalSertifikat +
                           $totalStpb + $totalEvent + $totalLainLain + $totalRbas + $totalBcabs01 + $totalBcabs02;

    // === BELUM DISERAHKAN ===
    $belumKaosPendek  = (clone $query)->whereNull('tanggal_penyerahan_kaos_pendek')->sum(DB::raw("FLOOR(COALESCE(kaos, 0) / $hargaKaosPendek)"));
    $belumKaosPanjang = (clone $query)->whereNull('tanggal_penyerahan_kaos_panjang')->sum(DB::raw("FLOOR(COALESCE(kaos_lengan_panjang, 0) / $hargaKaosPanjang)"));
    $belumKpk         = (clone $query)->whereNull('tanggal_penyerahan_kpk')->sum(DB::raw("FLOOR(COALESCE(kpk, 0) / $hargaKpk)"));
    $belumTas         = (clone $query)->whereNull('tanggal_penyerahan_tas')->sum(DB::raw("FLOOR(COALESCE(tas, 0) / $hargaTas)"));
    $belumRbas        = (clone $query)->whereNull('tanggal_penyerahan_rbas')->sum(DB::raw("FLOOR(COALESCE(RBAS, 0) / $hargaRbas)"));
    $belumBcabs01     = (clone $query)->whereNull('tanggal_penyerahan_bcabs01')->sum(DB::raw("FLOOR(COALESCE(BCABS01, 0) / $hargaBcabs01)"));
    $belumBcabs02     = (clone $query)->whereNull('tanggal_penyerahan_bcabs02')->sum(DB::raw("FLOOR(COALESCE(BCABS02, 0) / $hargaBcabs02)"));
    $belumSertifikat  = (clone $query)->whereNull('tanggal_penyerahan_sertifikat')->sum(DB::raw("FLOOR(COALESCE(sertifikat, 0) / $hargaSertifikat)"));
    $belumStpb        = (clone $query)->whereNull('tanggal_penyerahan_stpb')->sum(DB::raw("FLOOR(COALESCE(stpb, 0) / $hargaStpb)"));
    $belumEvent       = (clone $query)->whereNull('tanggal_penyerahan_event')->sum(DB::raw("FLOOR(COALESCE(event, 0) / $hargaEvent)"));
    $belumLainlain    = (clone $query)->whereNull('tanggal_penyerahan_lainlain')->sum(DB::raw("FLOOR(COALESCE(lain_lain, 0) / $hargaLainLain)"));

    $totalBelumDiserahkan = $belumKaosPendek + $belumKaosPanjang + $belumKpk + $belumTas + $belumRbas +
                            $belumBcabs01 + $belumBcabs02 + $belumSertifikat + $belumStpb + $belumEvent + $belumLainlain;

        // === SUDAH DISERAHKAN (per produk) ===
    $sudahKaosPendek  = $totalKaosPendek - $belumKaosPendek;
    $sudahKaosPanjang = $totalKaosPanjang - $belumKaosPanjang;
    $sudahKpk         = $totalKpk - $belumKpk;
    $sudahTas         = $totalTas - $belumTas;
    $sudahRbas        = $totalRbas - $belumRbas;
    $sudahBcabs01     = $totalBcabs01 - $belumBcabs01;
    $sudahBcabs02     = $totalBcabs02 - $belumBcabs02;
    $sudahSertifikat  = $totalSertifikat - $belumSertifikat;
    $sudahStpb        = $totalStpb - $belumStpb;
    $sudahEvent       = $totalEvent - $belumEvent;
    $sudahLainlain    = $totalLainLain - $belumLainlain;

    $totalSudahDiserahkan = $totalSemuaProdukPcs - $totalBelumDiserahkan;

    // === RINGKASAN UKURAN KAOS ===
$ukuranPendekOptions  = ['KAS', 'KAM', 'KAL', 'KAXL', 'KAXXL', 'KAXXXL', 'KAXXXLS'];
$ukuranPanjangOptions = ['KAS01', 'KAM01', 'KAL01', 'KAXL01', 'KAXXL01', 'KAXXXL01', 'KAXXXLS01'];

$belumUkuranPendek  = array_fill_keys($ukuranPendekOptions, 0);
$sudahUkuranPendek  = array_fill_keys($ukuranPendekOptions, 0);
$belumUkuranPanjang = array_fill_keys($ukuranPanjangOptions, 0);
$sudahUkuranPanjang = array_fill_keys($ukuranPanjangOptions, 0);

$recordsWithKaos = (clone $query)
    ->where(function ($q) {
        $q->where('kaos', '>', 0)->orWhere('kaos_lengan_panjang', '>', 0);
    })
    ->select([
        'kaos', 'kaos_lengan_panjang',
        'ukuran_kaos_pendek', 'ukuran_kaos_panjang',
        'tanggal_penyerahan_kaos_pendek', 'tanggal_penyerahan_kaos_panjang'
    ])
    ->get();

foreach ($recordsWithKaos as $row) {

    // ====================== KAOS PENDEK ======================
    $pcsPendek = (int) floor(($row->kaos ?? 0) / 70000);

    if ($pcsPendek > 0 && $row->ukuran_kaos_pendek) {
        $sizes = array_filter(array_map('trim', explode(',', strtoupper($row->ukuran_kaos_pendek))));

        // Jika jumlah ukuran yang tercatat == pcs → gunakan per ukuran
        if (count($sizes) === $pcsPendek) {
            foreach ($sizes as $size) {
                if (in_array($size, $ukuranPendekOptions)) {
                    is_null($row->tanggal_penyerahan_kaos_pendek)
                        ? $belumUkuranPendek[$size]++
                        : $sudahUkuranPendek[$size]++;
                }
            }
        } 
        // Jika tidak sama (contoh: hanya 1 ukuran untuk banyak pcs) → distribusikan ke ukuran tersebut
        else if (count($sizes) === 1) {
            $size = $sizes[0];
            if (in_array($size, $ukuranPendekOptions)) {
                is_null($row->tanggal_penyerahan_kaos_pendek)
                    ? $belumUkuranPendek[$size] += $pcsPendek
                    : $sudahUkuranPendek[$size] += $pcsPendek;
            }
        }
        // Kasus lain (jarang): abaikan atau log warning
    }

    // ====================== KAOS PANJANG ======================
    $pcsPanjang = (int) floor(($row->kaos_lengan_panjang ?? 0) / 85000);

    if ($pcsPanjang > 0 && $row->ukuran_kaos_panjang) {
        $sizes = array_filter(array_map('trim', explode(',', strtoupper($row->ukuran_kaos_panjang))));

        if (count($sizes) === $pcsPanjang) {
            foreach ($sizes as $size) {
                if (in_array($size, $ukuranPanjangOptions)) {
                    is_null($row->tanggal_penyerahan_kaos_panjang)
                        ? $belumUkuranPanjang[$size]++
                        : $sudahUkuranPanjang[$size]++;
                }
            }
        } else if (count($sizes) === 1) {
            $size = $sizes[0];
            if (in_array($size, $ukuranPanjangOptions)) {
                is_null($row->tanggal_penyerahan_kaos_panjang)
                    ? $belumUkuranPanjang[$size] += $pcsPanjang
                    : $sudahUkuranPanjang[$size] += $pcsPanjang;
            }
        }
    }
}

    return view('penerimaan.produk', compact(
        'penerimaan',
        'namaList',
        'unitList',

        // Total dibayar
        'totalKaosPendek', 'totalKaosPanjang', 'totalKpk', 'totalTas', 'totalSertifikat',
        'totalStpb', 'totalEvent', 'totalLainLain', 'totalRbas', 'totalBcabs01', 'totalBcabs02',
        'totalSemuaProdukPcs',

        // Belum diserahkan
        'belumKaosPendek', 'belumKaosPanjang', 'belumKpk', 'belumTas', 'belumRbas',
        'belumBcabs01', 'belumBcabs02', 'belumSertifikat', 'belumStpb', 'belumEvent', 'belumLainlain',
        'totalBelumDiserahkan',

        // Sudah diserahkan
        'sudahKaosPendek', 'sudahKaosPanjang', 'sudahKpk', 'sudahTas', 'sudahRbas',
        'sudahBcabs01', 'sudahBcabs02', 'sudahSertifikat', 'sudahStpb', 'sudahEvent', 'sudahLainlain',
        'totalSudahDiserahkan',

        // RINCIAN UKURAN KAOS — INI YANG SEBELUMNYA HILANG
        'belumUkuranPendek', 'sudahUkuranPendek',
        'belumUkuranPanjang', 'sudahUkuranPanjang',

        // Filter state
        'perPage', 'search', 'unitKode', 'periodeDari', 'periodeSampai'
    ));
}
public function updateTanggalPenyerahan(Request $request)
{
    // Isi method tetap sama persis
    try {
        $request->validate([
            'id'     => 'required|integer|exists:penerimaan,id',
            'field'  => 'required|string',
            'tanggal'=> 'nullable|date'
        ]);

        $fieldMap = [
            'kaos_pendek'   => 'tanggal_penyerahan_kaos_pendek',
            'kaos_panjang'  => 'tanggal_penyerahan_kaos_panjang',
            'kpk'           => 'tanggal_penyerahan_kpk',
            'tas'           => 'tanggal_penyerahan_tas',
            'rbas'          => 'tanggal_penyerahan_rbas',
            'bcabs01'       => 'tanggal_penyerahan_bcabs01',
            'bcabs02'       => 'tanggal_penyerahan_bcabs02',
            'sertifikat'    => 'tanggal_penyerahan_sertifikat',
            'stpb'          => 'tanggal_penyerahan_stpb',
            'event'         => 'tanggal_penyerahan_event',
            'lainlain'      => 'tanggal_penyerahan_lainlain',
        ];

        if (!isset($fieldMap[$request->field])) {
            return response()->json(['success' => false, 'message' => 'Field tidak valid'], 400);
        }

        $column = $fieldMap[$request->field];

        $penerimaan = Penerimaan::findOrFail($request->id);
        $penerimaan->update([$column => $request->tanggal ?: null]);

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        Log::error('Update tanggal produk error: ' . $e->getMessage(), $request->all());

        return response()->json([
            'success' => false,
            'message' => 'Gagal: ' . $e->getMessage()
        ], 500);
    }
}
    protected function storeBuktiTransferFile(?\Illuminate\Http\UploadedFile $file): ?string
    {
        if (!$file)
            return null;
        // simpan ke storage/app/public/bukti_transfer/...
        return $file->store('bukti_transfer', 'public'); // returns e.g. "bukti_transfer/abc.jpg"
    }

    protected function deleteBuktiTransferFile(?string $path): void
    {
        if (!$path)
            return;
        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable $e) {
            Log::warning('deleteBuktiTransferFile failed', ['path' => $path, 'err' => $e->getMessage()]);
        }
    }
    public function rbas(Request $request)
{
    $perPage    = (int) $request->input('per_page', 20);
    $search     = $request->input('search');
    $bulan      = $request->input('bulan');
    $tahun      = $request->input('tahun', now()->year);
    $unitFilter = $request->input('unit');

    // === DAFTAR UNIT UNTUK DROPDOWN ===
    $unitOptions = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru'])
        ->whereNotNull('bimba_unit')
        ->where('bimba_unit', '!=', '')
        ->distinct()
        ->orderBy('bimba_unit')
        ->pluck('bimba_unit')
        ->toArray();

    // === QUERY MURID AKTIF / BARU ===
    $muridQuery = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru'])
        ->select('nim', 'nama as nama_murid', 'bimba_unit', 'kelas', 'guru');

    if ($unitFilter) {
        $muridQuery->where('bimba_unit', $unitFilter);
    }

    $muridAktif = $muridQuery->get();

    // === Filter pencarian NIM / Nama ===
    if ($search) {
        $searchLower = strtolower(trim($search));
        $muridAktif = $muridAktif->filter(function ($m) use ($searchLower) {
            return str_contains(strtolower($m->nim), $searchLower) ||
                   str_contains(strtolower($m->nama_murid), $searchLower);
        });
    }

    // === Opsi bulan ===
    $bulanOptions = $this->getBulanOptions();

    // === Jika tidak ada murid setelah filter ===
    if ($muridAktif->isEmpty()) {
        return view('penerimaan.rbas', compact(
            'perPage', 'search', 'bulan', 'tahun', 'unitFilter',
            'unitOptions', 'bulanOptions', 'muridAktif'
        ) + [
            'sudahBayarList' => collect(),
            'belumBayarRbas' => collect(),
        ]);
    }

    // === Ambil record PENERIMAAN yang BENAR-BENAR pembayaran RBAS ===
    $penerimaanQuery = Penerimaan::where('tahun', $tahun)
        ->where('RBAS', '>', 0)  // Hanya yang nominal RBAS > 0 (pasti bayar RBAS)
        ->whereIn('nim', $muridAktif->pluck('nim'))
        ->select([
            'id',
            'nim',
            'kwitansi',
            'tanggal',
            'RBAS',
            'tanggal_penyerahan_rbas',
            'bulan', // untuk safety jika perlu debug
        ])
        ->orderBy('tanggal', 'desc'); // Urutkan terbaru dulu

    if ($bulan) {
        $penerimaanQuery->whereRaw('LOWER(TRIM(bulan)) = ?', [strtolower(trim($bulan))]);
    }

    // Ambil semua, lalu group by NIM dan ambil record terbaru per murid
    $penerimaanRbas = $penerimaanQuery->get()
        ->groupBy('nim')
        ->map(function ($group) {
            // Ambil record dengan tanggal terbaru (paling relevan)
            return $group->sortByDesc('tanggal')->first();
        });

    // === Suntikkan data penerimaan RBAS ke murid yang sudah bayar ===
    $sudahBayarList = $muridAktif
        ->whereIn('nim', $penerimaanRbas->keys())
        ->map(function ($murid) use ($penerimaanRbas) {
            $murid->penerimaan_rbas = $penerimaanRbas->get($murid->nim);
            return $murid;
        });

    // === Murid yang belum bayar RBAS ===
    $belumBayarRbas = $muridAktif->whereNotIn('nim', $penerimaanRbas->keys());

    return view('penerimaan.rbas', compact(
        'sudahBayarList',
        'belumBayarRbas',
        'perPage',
        'search',
        'bulan',
        'tahun',
        'unitFilter',
        'unitOptions',
        'bulanOptions',
        'muridAktif'
    ));
}

/**
 * Helper untuk daftar bulan Indonesia (bisa dipindah ke trait jika perlu)
 */
private function getBulanOptions(): array
{
    return [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
}
// Di PenerimaanController

public function getMuridByUnit(Request $request)
{
    $unit = $request->input('bimba_unit');

    $murid = BukuInduk::whereIn(DB::raw('LOWER(status)'), ['aktif', 'baru'])
        ->when($unit, function ($q) use ($unit) {
            return $q->where('bimba_unit', $unit);
        })
        ->orderBy('nama')
        ->select('nim', 'nama as nama_murid')
        ->get();

    return response()->json($murid);
}

public function export(Request $request)
{
    $query = Penerimaan::query();

    // Filter (sama seperti index)
    $search = trim($request->input('search', ''));
    if (!empty($search)) {
        if (str_contains($search, '|')) {
            [$nim] = array_map('trim', explode('|', $search));
            $query->where('nim', $nim);
        } else {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                  ->orWhere('nama_murid', 'like', "%{$search}%");
            });
        }
    }

    if ($request->bulan) $query->where('bulan', $request->bulan);
    if ($request->tahun) $query->where('tahun', $request->tahun);
    if ($request->bimba_unit) $query->where('bimba_unit', $request->bimba_unit);

    $penerimaan = $query->orderBy('tanggal', 'desc')->get();

    return Excel::download(
        new PenerimaanExport($penerimaan),
        'Penerimaan_SPP_' . now()->format('Y-m-d_His') . '.xlsx'
    );
}

/**
 * AJAX: Ambil voucher berdasarkan NIM (untuk select2)
 */
public function getVouchersByNim(Request $request)
{
    $nim = $request->query('nim');

    if (!$nim) {
        return response()->json([]);
    }

    $vouchers = VoucherLama::where('nim', $nim)
        ->where('jumlah_voucher', '>', 0)
        ->where('status', 'penyerahan')
        ->select('no_voucher', 'jumlah_voucher')
        ->orderBy('no_voucher')
        ->get();

    return response()->json($vouchers);
}
}
