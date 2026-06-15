<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemesananPerlengkapanUnit;
use App\Models\Produk;
use App\Models\Unit;
use Carbon\Carbon;

class PemesananPerlengkapanUnitController extends Controller
{
    /**
     * Daftar semua pemesanan perlengkapan
     */
    public function index()
    {
        $orders = PemesananPerlengkapanUnit::with(['produk', 'unit'])
                                           ->latest('tanggal_pemesanan')
                                           ->get();

        return view('pemesanan_perlengkapan_unit.index', compact('orders'));
    }

    /**
     * Form tambah pemesanan
     */
    public function create()
    {
        // Hanya produk perlengkapan, urutkan berdasarkan nama_produk agar mudah dipilih
        $produks = Produk::where('kategori', 'Perlengkapan')
                        ->orWhere('jenis', 'Perlengkapan')
                        ->orderBy('nama_produk', 'asc')
                        ->get();

        $units = Unit::orderBy('no_cabang')->get();

        return view('pemesanan_perlengkapan_unit.create', compact('produks', 'units'));
    }

    /**
     * Simpan pemesanan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id'           => 'required|exists:units,id',
            'tanggal_pemesanan' => 'required|date',
            'kode'              => 'required|string', // dari dropdown produk
            'nama_barang'       => 'required|string',
            'jumlah'            => 'required|integer|min:1',
            'harga'             => 'required|numeric|min:0',
            'keterangan'        => 'nullable|string|max:500',
            'kategori'          => 'required|string',
        ]);

        // Hitung minggu otomatis dari tanggal pemesanan
        $date = Carbon::parse($request->tanggal_pemesanan);
        $minggu = min(ceil($date->day / 7), 5); // 1-5

        $data = $request->only([
            'unit_id',
            'tanggal_pemesanan',
            'kode',
            'nama_barang',
            'jumlah',
            'harga',
            'keterangan',
            'kategori',
        ]);

        $data['minggu'] = $minggu;
        $data['total'] = $request->jumlah * $request->harga;

        PemesananPerlengkapanUnit::create($data);

        return redirect()
            ->route('pemesanan_perlengkapan_unit.index')
            ->with('success', 'Pemesanan perlengkapan berhasil disimpan!');
    }

    /**
     * Form edit pemesanan
     */
public function edit($id)
{
    $order = PemesananPerlengkapanUnit::findOrFail($id);

    $produks = Produk::where('kategori', 'Perlengkapan')
                    ->orWhere('jenis', 'Perlengkapan')
                    ->orderBy('nama_produk', 'asc')
                    ->get();

    $units = Unit::orderBy('no_cabang')->get();

    return view('pemesanan_perlengkapan_unit.edit', compact('order', 'produks', 'units'));
}

    /**
     * Update pemesanan
     */
    public function update(Request $request, $id)
    {
        $order = PemesananPerlengkapanUnit::findOrFail($id);

        $request->validate([
            'unit_id'           => 'required|exists:units,id',
            'tanggal_pemesanan' => 'required|date',
            'kode'              => 'required|string',
            'nama_barang'       => 'required|string',
            'jumlah'            => 'required|integer|min:1',
            'harga'             => 'required|numeric|min:0',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        // Hitung ulang minggu jika tanggal berubah
        $date = Carbon::parse($request->tanggal_pemesanan);
        $minggu = min(ceil($date->day / 7), 5);

        $data = $request->only([
            'unit_id',
            'tanggal_pemesanan',
            'kode',
            'nama_barang',
            'jumlah',
            'harga',
            'keterangan',
        ]);

        $data['minggu'] = $minggu;
        $data['total'] = $request->jumlah * $request->harga;

        $order->update($data);

        return redirect()
            ->route('pemesanan_perlengkapan_unit.index')
            ->with('success', 'Pemesanan perlengkapan berhasil diperbarui!');
    }

    /**
     * Hapus pemesanan
     */
    public function destroy($id)
    {
        $order = PemesananPerlengkapanUnit::findOrFail($id);
        $order->delete();

        return redirect()
            ->route('pemesanan_perlengkapan_unit.index')
            ->with('success', 'Data pemesanan berhasil dihapus!');
    }
}