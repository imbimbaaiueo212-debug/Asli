<?php

namespace App\Http\Controllers;

use App\Models\Pengajuan;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Pengajuan::query();

        // Filter sesuai unit login (kalau user punya info unit)
        if ($user) {
            $userUnit = null;

            if (!empty($user->bimba_unit)) {
                $userUnit = $user->bimba_unit;
            } elseif (!empty($user->departemen)) {
                $userUnit = $user->departemen;
            }

            if ($userUnit) {
                // kalau di komisi/petty cash kamu selalu pakai nama unit exactly, pakai where biasa
                $query->where('bimba_unit', $userUnit);
            }
        }

        $pengajuan = $query->orderBy('tanggal', 'desc')->get();

        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create()
    {
        return view('pengajuan.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'tanggal' => 'required|date',
        'keterangan_pengajuan' => 'required',
        'harga' => 'required|integer|min:0',
        'jumlah' => 'required|integer|min:1',
        'bimba_unit' => 'required',
        'no_cabang' => 'required',
    ]);

    $total = $request->harga * $request->jumlah;

    Pengajuan::create([
        'tanggal' => $request->tanggal,
        'keterangan_pengajuan' => $request->keterangan_pengajuan,
        'harga' => $request->harga,
        'jumlah' => $request->jumlah,
        'total' => $total,
        'bimba_unit' => $request->bimba_unit,
        'no_cabang' => $request->no_cabang,
    ]);

    return redirect()->route('pengajuan.index')
        ->with('success', 'Pengajuan berhasil ditambahkan');
}

    public function edit(Pengajuan $pengajuan)
    {
        return view('pengajuan.edit', compact('pengajuan'));
    }

    public function update(Request $request, Pengajuan $pengajuan)
    {
        $request->validate([
            'tanggal'              => 'required|date',
            'keterangan_pengajuan' => 'required',
            'harga'                => 'required|integer|min:0',
            'jumlah'               => 'required|integer|min:1',
        ]);

        $total = $request->harga * $request->jumlah;

        // Biasanya unit pengajuan tidak berubah, jadi kita biarkan bimba_unit & no_cabang yang lama.
        // Tapi kalau mau otomatis isi kalau masih null, aktifkan block di bawah ini.

        if (empty($pengajuan->bimba_unit) || empty($pengajuan->no_cabang)) {
            $user       = Auth::user();
            $bimba_unit = $pengajuan->bimba_unit;
            $no_cabang  = $pengajuan->no_cabang;

            if ($user && empty($bimba_unit)) {
                if (!empty($user->bimba_unit)) {
                    $bimba_unit = $user->bimba_unit;
                } elseif (!empty($user->departemen)) {
                    $bimba_unit = $user->departemen;
                }

                if ($bimba_unit) {
                    $unitModel = Unit::whereRaw('LOWER(TRIM(biMBA_unit)) = ?', [strtolower(trim($bimba_unit))])->first();
                    if ($unitModel) {
                        $bimba_unit = $unitModel->biMBA_unit;
                        $no_cabang  = $unitModel->no_cabang;
                    }
                }
            }

            $pengajuan->bimba_unit = $bimba_unit;
            $pengajuan->no_cabang  = $no_cabang;
        }

        $pengajuan->update([
            'tanggal'              => $request->tanggal,
            'keterangan_pengajuan' => $request->keterangan_pengajuan,
            'harga'                => $request->harga,
            'jumlah'               => $request->jumlah,
            'total'                => $total,
            // bimba_unit & no_cabang sudah di-set di atas (kalau perlu)
        ]);

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil diperbarui');
    }

    public function destroy(Pengajuan $pengajuan)
    {
        $pengajuan->delete();
        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan berhasil dihapus');
    }
}
