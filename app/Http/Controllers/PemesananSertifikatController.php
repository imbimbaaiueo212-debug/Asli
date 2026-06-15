<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemesananSertifikat;
use App\Models\BukuInduk;
use App\Models\Unit;
use Carbon\Carbon;

class PemesananSertifikatController extends Controller
{
    public function index()
    {
        $orders = PemesananSertifikat::latest()->get();
        return view('pemesanan_sertifikat.index', compact('orders'));
    }

    public function create()
    {
        // Ambil semua unit untuk dropdown (hanya admin yang butuh ini)
        $units = Unit::orderBy('no_cabang')->get();

        // Cek apakah user adalah admin/pusat
        $isAdmin = auth()->user()->role === 'admin' || 
                   auth()->user()->role === 'pusat' || 
                   !auth()->user()->unit;

        // Jika bukan admin → siswa dari unit login saja
        if (!$isAdmin) {
            $siswas = BukuInduk::where('status', 'Aktif')
                               ->orderBy('nama')
                               ->get();
        } else {
            $siswas = collect(); // kosong, akan di-load via AJAX
        }

        return view('pemesanan_sertifikat.create', compact('units', 'siswas', 'isAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'buku_induk_id'     => 'required|exists:buku_induk,id',
            'tanggal_pemesanan' => 'required|date',
            'level'             => 'required|string',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        $siswa = BukuInduk::findOrFail($request->buku_induk_id);

        $date = Carbon::parse($request->tanggal_pemesanan);
        $minggu = min(ceil($date->day / 7), 5);

        PemesananSertifikat::create([
            'nim'               => $siswa->nim,
            'nama_murid'        => $siswa->nama,
            'tmpt_lahir'        => $siswa->tmpt_lahir,
            'tgl_lahir'         => $siswa->tgl_lahir,
            'tgl_masuk'         => $siswa->tgl_masuk,
            'level'             => $request->level,
            'tanggal_pemesanan' => $request->tanggal_pemesanan,
            'minggu'            => $minggu,
            'keterangan'        => $request->keterangan,
            'bimba_unit'        => $siswa->bimba_unit,
            'no_cabang'         => $siswa->no_cabang,
        ]);

        return redirect()
            ->route('pemesanan_sertifikat.index')
            ->with('success', 'Pemesanan sertifikat berhasil disimpan!');
    }

    public function edit($id)
    {
        $order = PemesananSertifikat::findOrFail($id);

        $units = Unit::orderBy('no_cabang')->get();

        $isAdmin = auth()->user()->role === 'admin' || 
                   auth()->user()->role === 'pusat' || 
                   !auth()->user()->unit;

        return view('pemesanan_sertifikat.edit', compact('order', 'units', 'isAdmin'));
    }

    public function update(Request $request, $id)
    {
        $order = PemesananSertifikat::findOrFail($id);

        $request->validate([
            'tanggal_pemesanan' => 'required|date',
            'level'             => 'required|string',
            'keterangan'        => 'nullable|string|max:500',
        ]);

        $date = Carbon::parse($request->tanggal_pemesanan);
        $minggu = min(ceil($date->day / 7), 5);

        $order->update([
            'tanggal_pemesanan' => $request->tanggal_pemesanan,
            'minggu'            => $minggu,
            'level'             => $request->level,
            'keterangan'        => $request->keterangan,
        ]);

        return redirect()
            ->route('pemesanan_sertifikat.index')
            ->with('success', 'Pemesanan sertifikat berhasil diperbarui!');
    }

    /**
     * Hapus pemesanan sertifikat
     */
    public function destroy($id)
    {
        $order = PemesananSertifikat::findOrFail($id);

        $order->delete();

        return redirect()
            ->route('pemesanan_sertifikat.index')
            ->with('success', 'Pemesanan sertifikat berhasil dihapus!');
    }
}