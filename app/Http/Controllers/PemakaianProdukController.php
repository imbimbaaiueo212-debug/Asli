<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemakaianProduk;
use App\Models\Unit;
use App\Models\DataProduk;
use App\Models\BukuInduk;   // <-- Pastikan ini ada
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PemakaianProdukController extends Controller
{
    /**
     * Daftar semua pemakaian dengan filter
     */
    public function index(Request $request)
{
    $query = PemakaianProduk::with('unit');

    // Filter Unit
    if ($unitId = $request->input('unit_id')) {
        $query->where('unit_id', $unitId);
    }

    // Filter Range Tanggal (dari - sampai)
    if ($request->filled('tanggal_dari')) {
        $query->whereDate('tanggal', '>=', $request->tanggal_dari);
    }
    if ($request->filled('tanggal_sampai')) {
        $query->whereDate('tanggal', '<=', $request->tanggal_sampai);
    }

    // Filter Nama Murid
    if ($namaMurid = $request->input('nama_murid')) {
        $query->where('nama_murid', 'like', "%{$namaMurid}%");
    }

    // Filter Label Produk
    if ($label = $request->input('label')) {
        $query->where('label', 'like', "%{$label}%");
    }

    // Filter Guru (jika masih ingin dipertahankan)
    if ($guru = $request->input('guru')) {
        $query->where('guru', 'like', "%{$guru}%");
    }

    // Search umum (NIM, nama produk, dll) – opsional dipertahankan
    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('nim', 'like', "%{$search}%")
              ->orWhere('nama_produk', 'like', "%{$search}%")
              ->orWhere('nama_murid', 'like', "%{$search}%")
              ->orWhere('label', 'like', "%{$search}%");
        });
    }

    $items = $query->orderByDesc('tanggal')->paginate(25)->withQueryString();

    $units = Unit::orderBy('no_cabang')->get();

    return view('pemakaian_produk.index', compact('items', 'units'));
}

    /**
     * Form tambah pemakaian
     */
    public function create()
    {
        $units = Unit::orderBy('no_cabang')->get();
        return view('pemakaian_produk.create', compact('units'));
    }

    /**
     * Simpan pemakaian baru
     */
    public function store(Request $request)
{
    $request->validate([
        'unit_id'       => 'required|exists:units,id',
        'murid_id'      => 'required|exists:buku_induk,id',
        'tanggal'       => 'required|date',
        'label'         => 'required|string',
        'jumlah'        => 'required|integer|min:1',
        'kategori'      => 'nullable|string',
        'jenis'         => 'required|string',
        'nama_produk'   => 'required|string',
        'satuan'        => 'required|string',
        'harga'         => 'required|numeric|min:0',
    ]);

    // Ambil data murid
    $murid = BukuInduk::findOrFail($request->murid_id);

    // Hitung minggu otomatis dari tanggal
    $date = Carbon::parse($request->tanggal);
    $minggu = min(ceil($date->day / 7), 5);

    // Simpan data — TAMBAHKAN gol dan guru DI SINI
    PemakaianProduk::create([
        'unit_id'       => $request->unit_id,
        'murid_id'      => $request->murid_id,
        'nim'           => $murid->nim ?? null,
        'nama_murid'    => $murid->nama,
        'gol'           => $murid->gol ?? null,        // ← TAMBAH INI
        'guru'          => $murid->guru ?? null,       // ← TAMBAH INI
        'tanggal'       => $request->tanggal,
        'minggu'        => $minggu,
        'label'         => $request->label,
        'jumlah'        => $request->jumlah,
        'kategori'      => $request->kategori ?? '',
        'jenis'         => $request->jenis,
        'nama_produk'   => $request->nama_produk,
        'satuan'        => $request->satuan,
        'harga'         => $request->harga,
        'total'         => $request->jumlah * $request->harga,
    ]);

    return redirect()->route('pemakaian_produk.index')
                     ->with('success', 'Pemakaian produk berhasil disimpan!');
}

    /**
     * Form edit pemakaian
     */
    public function edit($id)
    {
        $item = PemakaianProduk::findOrFail($id);
        $units = Unit::orderBy('no_cabang')->get();

        return view('pemakaian_produk.edit', compact('item', 'units'));
    }

    /**
     * Update pemakaian
     */
    public function update(Request $request, $id)
{
    $item = PemakaianProduk::findOrFail($id);

    $request->validate([
        'unit_id'       => 'required|exists:units,id',
        'murid_id'      => 'required|exists:buku_induk,id',
        'tanggal'       => 'required|date',
        'label'         => 'required|string|max:50',
        'jumlah'        => 'required|integer|min:1',
        'kategori'      => 'nullable|string',
        'jenis'         => 'required|string',
        'nama_produk'   => 'required|string',
        'satuan'        => 'required|string',
        'harga'         => 'required|numeric|min:0',
        // HAPUS BARIS INI:
        // 'minggu'        => 'required|integer|min:1|max:5',
    ]);

    $oldJumlah = $item->jumlah;

    // Ambil data murid baru
    $murid = BukuInduk::findOrFail($request->murid_id);

    // Hitung minggu otomatis dari tanggal baru
    $date = Carbon::parse($request->tanggal);
    $minggu = min(ceil($date->day / 7), 5);

    $data = $request->only([
        'unit_id', 'tanggal', 'label', 'jumlah',
        'kategori', 'jenis', 'nama_produk', 'satuan', 'harga'
    ]);

    $data['murid_id']    = $request->murid_id;
    $data['nim']         = $murid->nim ?? null;
    $data['nama_murid']  = $murid->nama;
    $data['gol']         = $murid->gol ?? null;
    $data['guru']        = $murid->guru ?? null;
    $data['minggu']      = $minggu;                    // ← OTOMATIS
    $data['total']       = $data['jumlah'] * $data['harga'];

    $item->update($data);

    // Update stok 'pakai'
    $this->updatePakaiDiRekapStok($item, $oldJumlah);

    return redirect()->route('pemakaian_produk.index')
                     ->with('success', 'Data pemakaian produk berhasil diperbarui!');
}

    /**
     * Hapus pemakaian
     */
    public function destroy($id)
    {
        $item = PemakaianProduk::findOrFail($id);

        // Kurangi 'pakai' di rekap stok sebelum hapus
        $this->updatePakaiDiRekapStok($item, $item->jumlah, true);

        $item->delete();

        return redirect()->route('pemakaian_produk.index')
                         ->with('success', 'Data pemakaian produk berhasil dihapus!');
    }

   /**
 * API: Load murid aktif berdasarkan unit_id dari tabel units
 * Cocokkan dengan bimba_unit atau no_cabang di buku_induk
 */
public function getMuridByUnit($unitId)
{
    try {
        // Ambil data unit dari tabel units
        $unit = Unit::find($unitId);

        if (!$unit) {
            return response()->json(['error' => 'Unit tidak ditemukan'], 404);
        }

        // Query murid dari buku_induk berdasarkan bimba_unit atau no_cabang
        $murid = BukuInduk::whereIn('status', ['Aktif', 'Baru'])  // ← Perubahan utama
                          ->where(function ($q) use ($unit) {
                              $q->where('bimba_unit', $unit->biMBA_unit)
                                ->orWhere('no_cabang', $unit->no_cabang);
                          })
                          ->select('id', 'nama', 'nim', 'gol', 'guru', 'status') // tambahkan 'status' agar jelas
                          ->orderBy('nama', 'asc')
                          ->get();

        return response()->json($murid);
    } catch (\Exception $e) {
        Log::error('Error getMuridByUnit unit_id=' . $unitId . ': ' . $e->getMessage());

        return response()->json([
            'error' => true,
            'message' => 'Gagal memuat murid'
        ], 500);
    }
}

    /**
     * Update kolom 'pakai' di DataProduk sesuai perubahan pemakaian
     */
    private function updatePakaiDiRekapStok($pemakaian, $oldJumlah = null, $isDelete = false)
    {
        $periode = Carbon::parse($pemakaian->tanggal)->format('Y-m');
        $unitId  = $pemakaian->unit_id;
        $label   = $pemakaian->label;

        $jumlahBaru = $pemakaian->jumlah;
        $selisih    = $jumlahBaru;

        if ($isDelete) {
            $selisih = -$jumlahBaru;
        } elseif ($oldJumlah !== null) {
            $selisih = $jumlahBaru - $oldJumlah;
        }

        if ($selisih == 0) return;

        $rekap = DataProduk::where('unit_id', $unitId)
                           ->where('periode', $periode)
                           ->where('label', $label)
                           ->first();

        if ($rekap) {
            $rekap->pakai += $selisih;
            $rekap->saveQuietly();
        }
    }
    /**
 * API: Load produk dari DataProduk berdasarkan unit_id dan periode aktif
 */
/**
 * API: Load produk dari DataProduk + join Produk untuk kategori & nama_produk
 */
/**
 * API: Load produk dari DataProduk + join Produk untuk kategori & nama_produk
 */
public function getProdukByUnit($unitId)
{
    try {
        $periode = now()->format('Y-m');

        $produk = DataProduk::where('data_produk.unit_id', $unitId) // <-- TAMBAHKAN PREFIX TABEL
                            ->where('data_produk.periode', $periode)
                            ->join('produk', 'data_produk.kode', '=', 'produk.kode')
                            ->select(
                                'data_produk.id',
                                'data_produk.label as label',
                                'produk.kategori',
                                'data_produk.jenis',
                                'produk.label as nama_produk', // atau 'produk.nama' jika kolomnya nama
                                'data_produk.satuan',
                                'data_produk.harga'
                            )
                            ->orderBy('data_produk.label', 'asc')
                            ->get();

        return response()->json($produk);
    } catch (\Exception $e) {
        \Log::error('Error getProdukByUnit: ' . $e->getMessage());

        return response()->json([
            'error' => true,
            'message' => 'Gagal memuat produk: ' . $e->getMessage()
        ], 500);
    }
}
}