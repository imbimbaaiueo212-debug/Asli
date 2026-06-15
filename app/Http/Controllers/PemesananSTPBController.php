<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PemesananSTPB;
use App\Models\BukuInduk;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemesananSTPBController extends Controller
{
    /* =========================
     * INDEX
     * ========================= */
    public function index()
    {
        $user = Auth::user();

        $query = PemesananSTPB::with('unit')
            ->orderBy('tgl_pemesanan', 'desc');

        if (!$user->isAdminUser() && $user->bimba_unit) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('biMBA_unit', $user->bimba_unit);
            });
        }

        $orders = $query->paginate(20);

        return view('pemesanan_stpb.index', compact('orders'));
    }

    /* =========================
     * CREATE
     * ========================= */
    public function create()
    {
        $user = Auth::user();
        $units = Unit::orderBy('biMBA_unit')->get();

        $defaultUnitId = null;
        if (!$user->isAdminUser() && $user->bimba_unit) {
            $unit = Unit::where('biMBA_unit', $user->bimba_unit)->first();
            $defaultUnitId = $unit?->id;
        }

        return view('pemesanan_stpb.create', compact('units', 'defaultUnitId'));
    }

    /* =========================
     * STORE
     * ========================= */
    public function store(Request $request)
    {
        $request->validate([
            'unit_id'        => 'required|exists:units,id',
            'nim'            => 'required|string',
            'nama_murid'     => 'required|string',
            'nama_orang_tua' => 'required|string',
            'level'          => 'required|string',
            'tgl_pemesanan'  => 'required|date',
            'tgl_lahir'      => 'nullable|date',
            'tgl_masuk'      => 'nullable|date',
            'tgl_lulus'      => 'nullable|date',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->only([
            'unit_id',
            'nim',
            'nama_murid',
            'tmpt_lahir',
            'tgl_lahir',
            'tgl_masuk',
            'tgl_lulus',
            'nama_orang_tua',
            'kelas',
            'level',
            'keterangan',
        ]);

        // 🔥 pastikan field date tidak string kosong
        foreach (['tgl_lahir', 'tgl_masuk', 'tgl_lulus'] as $field) {
            if (empty($data[$field])) $data[$field] = null;
        }

        // tanggal pemesanan manual
        $data['tgl_pemesanan'] = $request->tgl_pemesanan;

        // hitung minggu otomatis
        $tanggal = Carbon::parse($request->tgl_pemesanan);
        $data['minggu'] = min((int) ceil($tanggal->day / 7), 5);

        PemesananSTPB::create($data);

        return redirect()->route('pemesanan_stpb.index')
            ->with('success', 'Pemesanan STPB berhasil disimpan!');
    }

    /* =========================
     * EDIT
     * ========================= */
    public function edit($id)
    {
        $order = PemesananSTPB::with('unit')->findOrFail($id);
        $units = Unit::orderBy('biMBA_unit')->get();

        return view('pemesanan_stpb.edit', compact('order', 'units'));
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function update(Request $request, $id)
    {
        $order = PemesananSTPB::findOrFail($id);

        $request->validate([
            'unit_id'        => 'required|exists:units,id',
            'nim'            => 'required|string',
            'nama_murid'     => 'required|string',
            'nama_orang_tua' => 'required|string',
            'level'          => 'required|string',
            'tgl_pemesanan'  => 'required|date',
            'tgl_lahir'      => 'nullable|date',
            'tgl_masuk'      => 'nullable|date',
            'tgl_lulus'      => 'nullable|date',
            'keterangan'     => 'nullable|string',
        ]);

        $data = $request->only([
            'unit_id',
            'nim',
            'nama_murid',
            'tmpt_lahir',
            'tgl_lahir',
            'tgl_masuk',
            'tgl_lulus',
            'nama_orang_tua',
            'kelas',
            'level',
            'keterangan',
        ]);

        foreach (['tgl_lahir', 'tgl_masuk', 'tgl_lulus'] as $field) {
            if (empty($data[$field])) $data[$field] = null;
        }

        $data['tgl_pemesanan'] = $request->tgl_pemesanan;

        $tanggal = Carbon::parse($request->tgl_pemesanan);
        $data['minggu'] = min((int) ceil($tanggal->day / 7), 5);

        $order->update($data);

        return redirect()->route('pemesanan_stpb.index')
            ->with('success', 'Pemesanan STPB berhasil diperbarui!');
    }

    /* =========================
     * DESTROY
     * ========================= */
    public function destroy($id)
    {
        $order = PemesananSTPB::findOrFail($id);
        $order->delete();

        return redirect()->route('pemesanan_stpb.index')
            ->with('success', 'Pemesanan STPB berhasil dihapus!');
    }

    /* =========================
     * API: GET SISWA BY UNIT
     * ========================= */
    public function getSiswaByUnit(Request $request)
    {
        $request->validate(['unit_id' => 'required|exists:units,id']);

        $unitCode = Unit::where('id', $request->unit_id)->value('biMBA_unit');
        if (!$unitCode) return response()->json([]);

        $siswa = BukuInduk::withoutGlobalScopes()
            ->where('bimba_unit', $unitCode)
            ->orderBy('nama')
            ->get([
                'id','nim','nama','tmpt_lahir','tgl_lahir',
                'tgl_masuk','orangtua as nama_orang_tua',
                'kelas','tahap','tgl_keluar'
            ])
            ->map(function ($item) {

                $fmt = function ($d) {
                    if (!$d || $d === '0000-00-00') return null;
                    try { return Carbon::parse($d)->format('Y-m-d'); }
                    catch (\Exception $e) { return null; }
                };

                return [
                    'id' => $item->id,
                    'nim' => $item->nim ?? '',
                    'nama' => $item->nama ?? '',
                    'tmpt_lahir' => $item->tmpt_lahir ?? '',
                    'tgl_lahir' => $fmt($item->tgl_lahir),
                    'tgl_masuk' => $fmt($item->tgl_masuk),
                    'nama_orang_tua' => $item->nama_orang_tua ?? '',
                    'kelas' => $item->kelas ?? '',
                    'tahap' => $item->tahap ?? '',
                    'tgl_lulus' => null, // input manual
                ];
            });

        return response()->json($siswa);
    }
}
