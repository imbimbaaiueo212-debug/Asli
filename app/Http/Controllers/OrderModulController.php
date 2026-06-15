<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderModul;
use App\Models\DataProduk;
use App\Models\Produk;
use App\Models\Unit;
use App\Imports\OrderModulImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class OrderModulController extends Controller
{
    private $allowedJenis = [
        'Modul Baca',
        'Modul Tulis',
        'Modul Matematika',
        'Modul Dikte',
        'Modul Huruf Sambung',
        'Modul SD',
        'Modul Evaluasi',
        'Modul Mewarnai',
        'Hore Aku Bisa Baca',
        'Modul Eksklusif',
        'Kaos Anak',
        'Administrasi',
        'ATK biMBA',
        'Attribut Promosi',
        'Furniture',
        'Souvenir',
        'Buku Cerita Anak Bilingual',
        'Pic Card',
        'Fun Worksheet',
        'ATK English',
    ];

    private function getStatusStokMap($periodeRekap, $unitId = null)
    {
        $query = DataProduk::where('periode', $periodeRekap);

        if ($unitId) {
            $query->where('unit_id', $unitId);
        }

        return $query->get(['label', 'sld_awal', 'terima', 'pakai', 'min_stok'])
            ->mapWithKeys(function ($item) {
                if (empty($item->label)) return [];

                $sld_akhir = $item->sld_awal + $item->terima - $item->pakai;
                $status = $sld_akhir >= $item->min_stok ? 1 : 0;

                return [$item->label => $status];
            })
            ->filter()
            ->toArray();
    }

    public function getStatusStok(Request $request)
    {
        try {
            $periodeRekap = $request->query('periode_rekap');
            $unitId = $request->query('unit_id');

            if (!$periodeRekap || !preg_match('/^\d{4}-\d{2}$/', $periodeRekap)) {
                return response()->json(['error' => 'Periode rekap tidak valid'], 400);
            }

            if ($unitId && !is_numeric($unitId)) {
                return response()->json(['error' => 'Unit ID tidak valid'], 400);
            }

            $unitId = $unitId ? (int)$unitId : null;

            $statusStokMap = $this->getStatusStokMap($periodeRekap, $unitId);

            $carbon = Carbon::createFromFormat('Y-m', $periodeRekap);
            $formatted = $carbon ? $carbon->translatedFormat('F Y') : 'Periode tidak valid';

            return response()->json([
                'status_stok' => $statusStokMap,
                'periode_formatted' => $formatted
            ]);

        } catch (\Exception $e) {
            Log::error('Error getStatusStok: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    private function getHargaMap()
    {
        return Produk::whereIn('jenis', $this->allowedJenis)
                     ->pluck('harga', 'label')
                     ->toArray();
    }

    public function index(Request $request)
{
    $query = OrderModul::with('unit');

    // ✅ FILTER RANGE TANGGAL (AMAN & FLEXIBLE)
    if ($request->start_date && $request->end_date) {
        $query->whereBetween('tanggal_order', [
            $request->start_date,
            $request->end_date
        ]);
    }

    $orders = $query->orderByDesc('tanggal_order')->get();

    return view('order_modul.index', compact('orders'));
}

    public function create()
{
    $units = Unit::orderBy('no_cabang')->get();

    $statusStokMap = [];
    $periodeRekap = now()->format('Y-m');
    $hargaMap = $this->getHargaMap();

    $produks = collect();

    // Hitung minggu default berdasarkan hari ini
    $defaultTanggal = now();
    $mingguDefault = $defaultTanggal->weekOfMonth; // 1 sampai 5 atau 6

    return view('order_modul.create', compact(
        'produks',
        'units',
        'statusStokMap',
        'periodeRekap',
        'hargaMap',
        'mingguDefault' // ← kirim ke view
    ));
}

   public function store(Request $request)
{
    $request->validate([
        'tanggal_order' => 'required|date',
        'unit_id'       => 'required|exists:units,id',
    ]);

    $produks  = $request->input('produk', []);
    $jumlahs  = $request->input('jumlah', []);
    $statuses = $request->input('status', []);

    if (empty($produks)) {
        return back()->with('error', 'Produk belum dipilih!');
    }

    // 🔒 USER TIDAK BOLEH SET STATUS
    if (!Auth::user()->is_admin) {
        $statuses = array_fill(0, count($produks), 'pending');
    }

    foreach ($produks as $i => $produk) {

        if (!$produk) continue;

        $jumlah = (int) ($jumlahs[$i] ?? 0);
        $status = $statuses[$i] ?? 'pending';

        // ambil harga dari DB
        $dataProduk = Produk::where('label', $produk)->first();
        $hargaSatuan = $dataProduk->harga ?? 0;
        $total = $jumlah * $hargaSatuan;

        // ✅ SIMPAN ORDER
        $order = OrderModul::create([
            'tanggal_order' => $request->tanggal_order,
            'unit_id'       => $request->unit_id,

            'kode1' => $produk,
            'jml1'  => $jumlah,
            'hrg1'  => $total,
            'status'=> $status,
            'approval' => 'pending',
        ]);
    }

    return redirect()->route('order_modul.index')
                     ->with('success', 'Order berhasil disimpan');
}

    public function edit($id)
{
    $order = OrderModul::with('unit')->findOrFail($id);
    $units = Unit::orderBy('no_cabang')->get();

    // ambil produk (master)
    $produks = \App\Models\Produk::orderBy('label')->get();

    return view('order_modul.edit', compact(
        'order',
        'units',
        'produks'
    ));
}

    public function update(Request $request, $id)
{
    $order = OrderModul::findOrFail($id);

    $request->validate([
        'tanggal_order' => 'required|date',
        'unit_id'       => 'required|exists:units,id',
        'status'        => 'required|in:pending,accept,reject,Kurang',  // tambahkan Kurang jika diperlukan
    ]);

    $hargaMap = \App\Models\Produk::pluck('harga', 'label')->toArray();

    $data = [
        'tanggal_order' => $request->tanggal_order,
        'unit_id'       => $request->unit_id,
        'status'        => $request->status,           // ← Ini yang penting
    ];

    // Reset semua slot produk
    for ($i = 1; $i <= 5; $i++) {
        $data['kode'.$i] = null;
        $data['jml'.$i]  = 0;
        $data['hrg'.$i]  = 0;
    }

    // Isi produk
    $produk = $request->produk ?? [];
    $jumlah = $request->jumlah ?? [];

    foreach ($produk as $i => $kode) {
        if ($i >= 5) break;

        $jml   = (int) ($jumlah[$i] ?? 0);
        $harga = $hargaMap[$kode] ?? 0;

        $index = $i + 1;

        $data['kode'.$index] = $kode;
        $data['jml'.$index]  = $jml;
        $data['hrg'.$index]  = $jml * $harga;
    }

    $order->update($data);

    return redirect()->route('order_modul.index')
        ->with('success', 'Order berhasil diupdate');
}

    public function destroy($id)
    {
        $order = OrderModul::findOrFail($id);
        $order->delete();

        return redirect()->route('order_modul.index')
                         ->with('success', 'Data order modul berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new OrderModulImport, $request->file('file'));

            // Set tanggal default jika kosong
            OrderModul::whereNull('tanggal_order')
                      ->update(['tanggal_order' => now()->format('Y-m-d')]);

            return redirect()->back()->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
    public function getProduksByUnit(Request $request)
{
    $unitId = $request->query('unit_id');

    if (!$unitId || !is_numeric($unitId)) {
        return response()->json(['produks' => []]);
    }

    $produks = Produk::where('unit_id', $unitId)  // atau where('bimba_unit', $unit->biMBA_unit)
                     ->whereIn('jenis', $this->allowedJenis)
                     ->orderBy('kode')
                     ->get(['kode', 'label', 'jenis', 'harga']);

    return response()->json([
        'produks' => $produks->map(function ($p) {
            return [
                'label' => $p->label,
                'jenis' => $p->jenis,
                'harga' => $p->harga,
            ];
        })
    ]);
}
public function updateStatus(Request $request, $id)
{
    if (!Auth::user()->is_admin) {
        abort(403);
    }

    $order = OrderModul::findOrFail($id);

    $request->validate([
        'status' => 'required|in:pending,accept,reject'
    ]);

    $order->status = $request->status;
    $order->save();

    // ✅ CEK: jangan double kirim ke penerimaan
    if ($request->status === 'accept') {

        $already = \App\Models\PenerimaanProduk::where('keterangan', 'like', '%Order #'.$order->id.'%')->exists();

        if (!$already) {
            \App\Models\PenerimaanProduk::create([
                'unit_id' => $order->unit_id,
                'tanggal' => now(),
                'kode_produk' => $order->kode1,
                'jumlah' => $order->jml1,
                'harga' => $order->hrg1,
                'keterangan' => 'Approval Order #' . $order->id,
            ]);
        }
    }

    return back()->with('success', 'Status berhasil diupdate');
}
}