<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanProduk;
use App\Models\Produk;
use App\Models\PemesananPerlengkapanUnit;
use App\Models\Unit;           // ← Tambah ini
use App\Models\OrderModul;
use App\Models\PemesananKaos;
use App\Models\PemesananSertifikat;
use App\Models\PemesananSTPB;
use Carbon\Carbon;
use Illuminate\Validation\Rule; // Pastikan ini ada di atas controller
use App\Models\DataProduk;           // ← sudah ada atau tambahkan jika belum
use Illuminate\Support\Facades\DB;   // ← TAMBAHKAN BARIS INI !!!



class PenerimaanProdukController extends Controller
{
    private $allowedJenis = [
        'Modul Baca', 'Modul Tulis', 'Modul Matematika', 'Modul Dikte',
        'Modul Huruf Sambung', 'Modul SD', 'Modul Evaluasi', 'Modul Mewarnai',
        'Hore Aku Bisa Baca', 'Modul Eksklusif', 'Kaos Anak', 'Administrasi',
        'ATK biMBA', 'Attribut Promosi', 'Furniture', 'Souvenir',
        'Buku Cerita Anak Bilingual', 'Pic Card', 'Fun Worksheet', 'ATK English', 'Sertifikat',
    ];

    /**
     * Daftar semua penerimaan produk
     */
  public function index()
{
    // Query dasar dengan eager loading unit
    $query = PenerimaanProduk::with('unit');

    // === FILTER UNIT ===
    if (request('unit_id')) {
        $query->where('unit_id', request('unit_id'));
    }

    // === FILTER FAKTUR ===
    if (request('faktur')) {
        $query->where('faktur', 'like', '%' . request('faktur') . '%');
    }

    // === FILTER RANGE TANGGAL ===
    if (request('tanggal_mulai')) {
        $query->whereDate('tanggal', '>=', request('tanggal_mulai'));
    }
    if (request('tanggal_selesai')) {
        $query->whereDate('tanggal', '<=', request('tanggal_selesai'));
    }

    // Eksekusi query: sorting + pagination
    $items = $query->orderBy('tanggal', 'desc')
                   ->orderBy('id', 'desc')
                   ->paginate(20)
                   ->withQueryString();

   // === HITUNG JUMLAH ORDER DARI ORDER_MODUL UNTUK SETIAP ITEM ===
foreach ($items as $item) {
    $jumlahOrder = \App\Models\OrderModul::where('unit_id', $item->unit_id)
        ->whereDate('tanggal_order', $item->tanggal) // hanya tanggal sama
        ->sum(DB::raw('COALESCE(jml1, 0) + COALESCE(jml2, 0) + COALESCE(jml3, 0) + COALESCE(jml4, 0) + COALESCE(jml5, 0)'));

    $item->jumlah_order = $jumlahOrder;
}
    // === DATA UNTUK FILTER ===
    $units = \App\Models\Unit::orderBy('biMBA_unit')->get();

    // Label unik untuk dropdown filter
    $fakturs = PenerimaanProduk::distinct()
                              ->orderBy('faktur')
                              ->pluck('faktur');

    return view('penerimaan_produk.index', compact('items', 'units', 'fakturs'));
}

    /**
     * Form tambah penerimaan baru
     */
    public function create()
    {
        $produks = Produk::whereIn('jenis', $this->allowedJenis)
                         ->orderBy('kode')
                         ->get(['kode', 'label', 'jenis', 'kategori', 'satuan', 'harga', 'status', 'isi']);

        // Ambil semua unit, urutkan berdasarkan nama unit
        $units = Unit::orderBy('biMBA_unit')->get();

        return view('penerimaan_produk.create', compact('produks', 'units'));
    }

    /**
     * Simpan penerimaan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'faktur'       => 'required|string|max:255',
            'unit_id'      => 'required|exists:units,id',
            'tanggal'      => 'required|date',
            'label'        => 'required|string',
            'jumlah'       => 'required|integer|min:1',
            'harga'        => 'required|numeric|min:0',
            'kategori'     => 'nullable|string',
            'jenis'        => 'required|string',
            'nama_produk'  => 'required|string',
            'satuan'       => 'required|string',
            'status'       => 'nullable|string',
            'isi'          => 'nullable|string',
        ]);

        $data = $request->only([
            'faktur', 'unit_id', 'tanggal', 'label', 'jumlah',
            'kategori', 'jenis', 'nama_produk', 'satuan', 'harga', 'status', 'isi'
        ]);

        $data['minggu'] = $this->hitungMingguDariTanggal($data['tanggal']);
        $data['total']  = $data['jumlah'] * $data['harga'];

        PenerimaanProduk::create($data);

        // Sinkronkan TERIMA ke data_produk setelah simpan
        $this->syncTerimaFromPenerimaan(
            Carbon::parse($data['tanggal'])->format('Y-m'),
            $data['unit_id']
        );

        return redirect()->route('penerimaan_produk.index')
                         ->with('success', 'Data penerimaan produk berhasil disimpan!');
    }

    /**
     * Form edit penerimaan
     */
    public function edit($id)
    {
        $item = PenerimaanProduk::findOrFail($id);

        $produks = Produk::whereIn('jenis', $this->allowedJenis)
                         ->orderBy('kode')
                         ->get(['kode', 'label', 'jenis', 'kategori', 'satuan', 'harga', 'status', 'isi']);

        $units = Unit::orderBy('biMBA_unit')->get();

        return view('penerimaan_produk.edit', compact('item', 'produks', 'units'));
    }

    /**
     * Update penerimaan
     */
    public function update(Request $request, $id)
{
    $item = PenerimaanProduk::findOrFail($id);

    $request->validate([
        'faktur'       => 'required|string|max:255',
        'unit_id'      => 'required|exists:units,id',
        'tanggal'      => 'required|date',
        'label'        => 'required|string',
        'jumlah'       => 'required|integer|min:1',
        'harga'        => 'required|numeric|min:0',
        'kategori'     => 'nullable|string',
        'jenis'        => 'required|string',
        'nama_produk'  => 'required|string',
        'satuan'       => 'required|string',
        'status'       => 'nullable|string',
        'isi'          => 'nullable|string',
    ]);

    $data = $request->only([
        'faktur', 'unit_id', 'tanggal', 'label', 'jumlah',
        'kategori', 'jenis', 'nama_produk', 'satuan', 'harga', 'status', 'isi'
    ]);

    // Hitung minggu otomatis dari tanggal
    $date = \Carbon\Carbon::parse($data['tanggal']);
    $hari = $date->day;
    $data['minggu'] = min(ceil($hari / 7), 5);

    $data['total'] = $data['jumlah'] * $data['harga'];

    $item->update($data);

    return redirect()->route('penerimaan_produk.index')
                     ->with('success', 'Data penerimaan produk berhasil diperbarui!');
}

    /**
     * Hapus penerimaan
     */
    public function destroy($id)
    {
        $item = PenerimaanProduk::findOrFail($id);
        $item->delete();

        return redirect()->route('penerimaan_produk.index')
                         ->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Helper: Ambil minggu order untuk produk tertentu (label)
     */
    public function getMingguOrder($label)
    {
        $minggu = [];

        for ($i = 1; $i <= 5; $i++) {
            $hasOrder = OrderModul::where("kode{$i}", $label)
                                  ->whereNotNull("jml{$i}")
                                  ->where("jml{$i}", '>', 0)
                                  ->exists();

            if ($hasOrder) {
                $minggu[] = $i;
            }
        }

        return $minggu; // return array [1,3,5]
    }
    private function hitungMingguDariTanggal($tanggal)
{
    // Konversi ke Carbon
    $date = Carbon::parse($tanggal);

    // Hari dalam bulan (1-31)
    $hari = $date->day;

    // Hitung minggu: setiap 7 hari = 1 minggu
    $minggu = ceil($hari / 7);

    // Maksimal minggu 5
    return min($minggu, 5);
}
public function dariKaos()
{
    $pemesanan = PemesananKaos::whereDoesntHave('penerimaan') // asumsi ada relasi atau flag
                              ->with('unit')
                              ->get();

    $units = Unit::orderBy('biMBA_unit')->get();

    return view('penerimaan_produk.dari_kaos', compact('pemesanan', 'units'));
}

public function terimaDariKaos(Request $request, $pemesananId)
{
    $pesanan = PemesananKaos::findOrFail($pemesananId);

    $request->validate([
        'faktur'   => 'required',
        'tanggal'  => 'required|date',
    ]);

    PenerimaanProduk::create([
        'faktur'       => $request->faktur,
        'unit_id'      => $pesanan->unit_id,
        'tanggal'      => $request->tanggal,
        'minggu'       => $this->hitungMingguDariTanggal($request->tanggal),
        'label'        => 'Kaos Anak - ' . $pesanan->size,
        'jumlah'       => $pesanan->kaos + $pesanan->kaos_panjang,
        'harga'        => 0, // atau ambil dari master harga
        'total'        => 0,
        'kategori'     => 'Kaos',
        'jenis'        => 'Kaos Anak',
        'nama_produk'  => 'Kaos Anak ' . $pesanan->size,
        'satuan'       => 'pcs',
        'status'       => 'Diterima dari pemesanan',
        'isi'          => "Dari pemesanan: {$pesanan->no_bukti} - {$pesanan->nama_murid}",
    ]);

    // Optional: tandai pemesanan sudah diterima
    // $pesanan->update(['status_terima' => 'sudah']);

    return redirect()->route('penerimaan_produk.index')->with('success', 'Penerimaan dari kaos berhasil!');
}

public function createMulti(Request $request)
{
    $units = Unit::orderBy('biMBA_unit')->get();

    $produks = collect();
    $sertifikatPending = collect();
    $stpbPending = collect();

    $produkSTA  = null;
    $produkSTPB = null;

    $infoMessage = 'Pilih Unit dan Tanggal Penerimaan untuk melihat produk & sertifikat yang belum diterima.';

    $selectedUnitId  = $request->query('unit_id');
    $selectedTanggal = $request->query('tanggal');

    if ($selectedUnitId && $selectedTanggal) {

        $tanggalCarbon = Carbon::parse($selectedTanggal);
        $bulan = $tanggalCarbon->month;
        $tahun = $tanggalCarbon->year;

        /** ===============================
         *  KUMPULKAN ORDER PER KODE + QTY
         * =============================== */
        $pendingProduk = collect(); // key = kode, value = total qty

        /* ================= UNIT ================= */
        $unit = Unit::find($selectedUnitId);
        $noCabang = $unit?->no_cabang;

        /* ================= 1. ORDER MODUL ================= */
        $orders = OrderModul::where('unit_id', $selectedUnitId)
            ->where('status', 'accept')
            ->get();

        foreach ($orders as $order) {
            for ($i = 1; $i <= 5; $i++) {

                $kodeInput = trim($order->{'kode'.$i} ?? '');
                $qty       = (int) ($order->{'jml'.$i} ?? 0);

                if ($kodeInput && $qty > 0) {

                    $produk = Produk::where('kode', $kodeInput)
                        ->orWhere('label', $kodeInput)
                        ->first();

                    if ($produk) {
                        $pendingProduk[$produk->kode] =
                            ($pendingProduk[$produk->kode] ?? 0) + $qty;
                    }
                }
            }
        }

        /* ================= 2. KAOS MURID ================= */
        $pemesananKaosMurid = PemesananKaos::where('unit_id', $selectedUnitId)
            ->whereNotNull('tanggal')
            ->get();

        foreach ($pemesananKaosMurid as $pesanan) {

            $jumlahKaos = ($pesanan->kaos ?? 0) + ($pesanan->kaos_panjang ?? 0);

            if ($jumlahKaos > 0 && $pesanan->size) {
                $produk = Produk::where('kode', $pesanan->size)
                    ->orWhere('label', $pesanan->size)
                    ->first();

                if ($produk) {
                    $pendingProduk[$produk->kode] =
                        ($pendingProduk[$produk->kode] ?? 0) + $jumlahKaos;
                }
            }

            if ($pesanan->kode_tas && ($pesanan->jumlah_tas ?? 0) > 0) {
                $produkTas = Produk::where('kode', $pesanan->kode_tas)->first();
                if ($produkTas) {
                    $pendingProduk[$produkTas->kode] =
                        ($pendingProduk[$produkTas->kode] ?? 0) + $pesanan->jumlah_tas;
                }
            }

            $map = [
                'rbas'    => 'RBAS',
                'bcabs01' => 'BCABS.01',
                'bcabs02' => 'BCABS.02',
                'kpk'     => 'KPK',
            ];

            foreach ($map as $field => $kodePendek) {
                $qty = (int) ($pesanan->{$field} ?? 0);
                if ($qty > 0) {
                    $produk = Produk::where('kode', $kodePendek)
                        ->orWhere('label', $kodePendek)
                        ->first();

                    if ($produk) {
                        $pendingProduk[$produk->kode] =
                            ($pendingProduk[$produk->kode] ?? 0) + $qty;
                    }
                }
            }
        }

        /* ================= 3. PERLENGKAPAN UNIT ================= */
        $pemesananPerlengkapan = PemesananPerlengkapanUnit::where('unit_id', $selectedUnitId)
            ->get();

        foreach ($pemesananPerlengkapan as $pesanan) {
            if ($pesanan->jumlah > 0 && $pesanan->kode) {
                $produk = Produk::where('kode', $pesanan->kode)->first();
                if ($produk) {
                    $pendingProduk[$produk->kode] =
                        ($pendingProduk[$produk->kode] ?? 0) + $pesanan->jumlah;
                }
            }
        }

        /* ================= 4. SERTIFIKAT & STPB (tetap sama) ================= */
        if ($noCabang) {
            $sudahTerimaSTA = PenerimaanProduk::where('unit_id', $selectedUnitId)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->where('label', 'STA')
                ->exists();

            if (!$sudahTerimaSTA) {
                $sertifikatPending = PemesananSertifikat::where('no_cabang', $noCabang)
                    ->orderBy('nama_murid')
                    ->get();

                if ($sertifikatPending->count() > 0) {
                    $pendingProduk['090903002'] = ($pendingProduk['090903002'] ?? 0) + $sertifikatPending->count();
                    $produkSTA = Produk::where('kode', '090903002')->first();
                }
            }
        }

        $sudahTerimaSTPB = PenerimaanProduk::where('unit_id', $selectedUnitId)
            ->where('label', 'STPB')
            ->exists();

        if (!$sudahTerimaSTPB) {
            $stpbPending = PemesananSTPB::where('unit_id', $selectedUnitId)
                ->orderBy('nama_murid')
                ->get();

            if ($stpbPending->count() > 0) {
                $pendingProduk['070701027'] = ($pendingProduk['070701027'] ?? 0) + $stpbPending->count();
                $produkSTPB = Produk::where('kode', '070701027')->first();
            }
        }

        /* ================= SUDAH DITERIMA ================= */
        $receivedLabels = PenerimaanProduk::where('unit_id', $selectedUnitId)
            ->pluck('label')
            ->map('trim')
            ->unique()
            ->values();

        $receivedKode = $receivedLabels->map(function ($label) {
            $produk = Produk::where('label', $label)
                ->orWhere('kode', $label)
                ->first();
            return $produk ? $produk->kode : $label;
        })->unique()->values();

        /* ================= FINAL PRODUK + ORDER QTY ================= */
        $finalPending = collect($pendingProduk)
            ->reject(fn ($qty, $kode) => $receivedKode->contains($kode));

        if ($finalPending->isNotEmpty()) {

            $produkMaster = Produk::whereIn('kode', $finalPending->keys())
                ->get()
                ->keyBy('kode');

            $produks = $finalPending->map(function ($qty, $kode) use ($produkMaster) {

                $p = $produkMaster[$kode] ?? null;
                if (!$p) return null;

                return [
                    'kode'        => $p->kode,
                    'label'       => $p->label,
                    'nama_produk' => $p->nama_produk,
                    'jenis'       => $p->jenis,
                    'kategori'    => $p->kategori,
                    'satuan'      => $p->satuan,
                    'harga'       => $p->harga,
                    'order_qty'   => $qty,
                    'status'      => $p->status,
                    'isi'         => $p->isi,
                ];
            })->filter()->values();
        }

                        /* ================= TAMBAHAN BARU: DARI REKAPITULASI BULANAN (PRIORITAS TINGGI) ================= */
        $periode = $tanggalCarbon->format('Y-m');

        $rekapData = DataProduk::where('periode', $periode)
                    ->where('unit_id', $selectedUnitId)
                    ->get();

        foreach ($rekapData as $rekap) {
            if ($produks->contains('kode', $rekap->kode) || 
                $receivedKode->contains($rekap->kode)) {
                continue;
            }

            $produk = Produk::where('kode', $rekap->kode)
                           ->orWhere('label', $rekap->label)
                           ->first();

            if ($produk) {
                $produks->push([
                    'kode'        => $produk->kode,
                    'label'       => $produk->label,
                    'nama_produk' => $produk->nama_produk ?? $rekap->label,
                    'jenis'       => $produk->jenis ?? 'Administrasi',
                    'kategori'    => $produk->kategori ?? '',
                    'satuan'      => $produk->satuan ?? 'Buku',
                    'harga'       => $produk->harga ?? 12000,
                    'order_qty'   => $rekap->sl_awal ?? 0,
                    'status'      => $produk->status ?? 'Aktif',
                    'isi'         => $produk->isi ?? '',
                ]);
            }
        }

        // ================= TAMBAHAN BARU (yang diminta) =================
        // Paksa tampilkan semua rekap bulanan meskipun sudah pernah diterima
        foreach ($rekapData as $rekap) {
            // Jika sudah ada di list produks, lewati
            if ($produks->contains('kode', $rekap->kode)) {
                continue;
            }

            $produk = Produk::where('kode', $rekap->kode)
                           ->orWhere('label', $rekap->label)
                           ->first();

            if ($produk) {
                $produks->push([
                    'kode'        => $produk->kode,
                    'label'       => $produk->label,
                    'nama_produk' => $produk->nama_produk ?? $rekap->label,
                    'jenis'       => $produk->jenis ?? 'Administrasi',
                    'kategori'    => $produk->kategori ?? '',
                    'satuan'      => $produk->satuan ?? 'Buku',
                    'harga'       => $produk->harga ?? 12000,
                    'order_qty'   => $rekap->sl_awal ?? 0,
                    'status'      => $produk->status ?? 'Aktif',
                    'isi'         => $produk->isi ?? '',
                ]);
            }
        }
        // ================= END TAMBAHAN =================

        foreach ($rekapData as $rekap) {
            if ($produks->contains('kode', $rekap->kode) || 
                $receivedKode->contains($rekap->kode)) {
                continue;
            }

            $produk = Produk::where('kode', $rekap->kode)
                           ->orWhere('label', $rekap->label)
                           ->first();

            if ($produk) {
                $produks->push([
                    'kode'        => $produk->kode,
                    'label'       => $produk->label,
                    'nama_produk' => $produk->nama_produk ?? $rekap->label,
                    'jenis'       => $produk->jenis ?? 'Administrasi',
                    'kategori'    => $produk->kategori ?? '',
                    'satuan'      => $produk->satuan ?? 'Buku',
                    'harga'       => $produk->harga ?? 12000,
                    'order_qty'   => $rekap->sl_awal ?? 0,
                    'status'      => $produk->status ?? 'Aktif',
                    'isi'         => $produk->isi ?? '',
                ]);
            }
        }

        /* ================= TAMBAHAN BARU: SEMUA PRODUK DARI MASTER (Cadangan) ================= */
        $allProdukMaster = Produk::orderBy('kode')
            ->get(['kode', 'label', 'nama_produk', 'jenis', 'kategori', 'satuan', 'harga', 'status', 'isi']);

        foreach ($allProdukMaster as $p) {
            if ($produks->contains('kode', $p->kode)) continue;
            if ($receivedKode->contains($p->kode)) continue;

            $produks->push([
                'kode'        => $p->kode,
                'label'       => $p->label,
                'nama_produk' => $p->nama_produk,
                'jenis'       => $p->jenis,
                'kategori'    => $p->kategori,
                'satuan'      => $p->satuan,
                'harga'       => $p->harga,
                'order_qty'   => 0,
                'status'      => $p->status,
                'isi'         => $p->isi,
            ]);
        }

        $produks = $produks->unique('kode')->sortBy('kode')->values();

        /* ================= INFO ================= */
        $infoProduk = $produks->isEmpty()
            ? "Semua produk umum sudah diterima."
            : "Menampilkan {$produks->count()} produk (Rekap + Order + Master).";

        $infoSert = $sertifikatPending->isEmpty()
            ? "Tidak ada sertifikat murid."
            : "Ada {$sertifikatPending->count()} sertifikat murid.";

        $infoSTPB = $stpbPending->isEmpty()
            ? "Tidak ada STPB murid."
            : "Ada {$stpbPending->count()} STPB murid.";

        $infoMessage = $infoProduk.' '.$infoSert.' '.$infoSTPB;
    }

    return view('penerimaan_produk.create_multi', compact(
        'produks',
        'produkSTA',
        'produkSTPB',
        'sertifikatPending',
        'stpbPending',
        'units',
        'selectedUnitId',
        'selectedTanggal',
        'infoMessage'
    ));
}


/**
 * Simpan multi penerimaan (bulk insert)
 */
/**
 * Simpan multi penerimaan (bulk insert)
 */
public function storeMulti(Request $request)
{
    $tableName = (new PenerimaanProduk())->getTable();

    $request->validate([
        'faktur' => [
            'required','string','max:255',
            Rule::unique($tableName, 'faktur'),
        ],
        'unit_id' => 'required|exists:units,id',
        'tanggal' => 'required|date',

        'items'               => 'required|array|min:1',
        'items.*.label'       => 'required|string',
        'items.*.nama_produk' => 'required|string',
        'items.*.jenis'       => 'required|string',
        'items.*.satuan'      => 'required|string',
        'items.*.harga'       => 'required|numeric|min:0',
        'items.*.jumlah'      => 'required|integer|min:1',
        'items.*.kategori'    => 'nullable|string',
        'items.*.status'      => 'nullable|string',
        'items.*.isi'         => 'nullable|string',
    ]);

    $commonData = [
        'faktur'  => $request->faktur,
        'unit_id' => $request->unit_id,
        'tanggal' => $request->tanggal,
        'minggu'  => $this->hitungMingguDariTanggal($request->tanggal),
    ];

    $createdCount = 0;

    foreach ($request->items as $item) {

        $produk = Produk::where('kode', $item['label'])->first();

        $labelSingkat = $produk ? $produk->label : $item['label'];
        $namaLengkap  = $produk ? $produk->nama_produk : $item['nama_produk'];

        $data = array_merge($commonData, [
            'label'       => $labelSingkat,
            'nama_produk' => $namaLengkap,
            'kategori'    => $item['kategori'] ?? null,
            'jenis'       => $item['jenis'],
            'satuan'      => $item['satuan'],
            'harga'       => $item['harga'],
            'jumlah'      => $item['jumlah'],
            'status'      => $item['status'] ?? null,
            'isi'         => $item['isi'] ?? null,
            'total'       => $item['jumlah'] * $item['harga'],
        ]);

        $exists = PenerimaanProduk::where('faktur', $request->faktur)
                                  ->where('label', $data['label'])
                                  ->exists();

        if ($exists) {
            return back()->withErrors([
                'items' => "Produk dengan label {$data['label']} sudah ada di faktur ini!"
            ]);
        }

        PenerimaanProduk::create($data);
        $createdCount++;
    }

    $this->syncTerimaFromPenerimaan(
        Carbon::parse($request->tanggal)->format('Y-m'),
        $request->unit_id
    );

    return redirect()
        ->route('penerimaan_produk.index')
        ->with('success', "Berhasil menyimpan {$createdCount} item penerimaan dengan faktur {$request->faktur}!");
}


    /**
     * Sinkronisasi kolom TERIMA dari penerimaan aktual (berdasarkan label)
     */
    private function syncTerimaFromPenerimaan(string $periode, ?int $unitId = null)
{
    $query = PenerimaanProduk::select('label', DB::raw('COALESCE(SUM(jumlah), 0) as total'))
        ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode]);

    if ($unitId) {
        $query->where('unit_id', $unitId);
    }

    $penerimaans = $query->groupBy('label')->pluck('total', 'label');

    // Jika tidak ada data penerimaan di periode ini, skip
    if ($penerimaans->isEmpty()) {
        return;
    }

    $dataQuery = DataProduk::where('periode', $periode);
    if ($unitId) {
        $dataQuery->where('unit_id', $unitId);
    }

    $dataQuery->chunkById(200, function ($records) use ($penerimaans) {
        foreach ($records as $record) {
            $terimaBaru = $penerimaans->get($record->label, 0);
            if ($record->terima != $terimaBaru) {
                $record->terima = $terimaBaru;
                $record->saveQuietly();
            }
        }
    });
}
}