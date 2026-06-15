<?php

namespace App\Http\Controllers;

use App\Models\LaporanBagiHasil;
use Illuminate\Http\Request;

class LaporanBagiHasilController extends Controller
{
    public function index()
    {
        $laporan = LaporanBagiHasil::orderBy('bulan', 'desc')->get();
        return view('laporan.index', compact('laporan'));
    }

    public function create()
    {
        return view('laporan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'no_cabang'   => 'required|string',
            'bimba_unit'  => 'required|string', // ✅ DIGANTI dari 'unit'
            'bulan'       => 'required|date',
            'nama_bank'   => 'required|string',
            'no_rekening' => 'required|string',
            'atas_nama'   => 'required|string',

            'bimba_murid_aktif_lalu'        => 'nullable|integer',
            'bimba_murid_baru'              => 'nullable|integer',
            'bimba_murid_kembali'           => 'nullable|integer',
            'bimba_murid_keluar'            => 'nullable|integer',
            'bimba_murid_aktif_ini'         => 'nullable|integer',
            'bimba_total_penerimaan_spp'    => 'nullable|integer',
            'bimba_persentase_bagi_hasil'   => 'nullable|numeric',

            'eng_murid_aktif_lalu'          => 'nullable|integer',
            'eng_murid_baru'                => 'nullable|integer',
            'eng_murid_kembali'             => 'nullable|integer',
            'eng_murid_keluar'              => 'nullable|integer',
            'eng_murid_aktif_ini'           => 'nullable|integer',
            'eng_total_penerimaan_spp'      => 'nullable|integer',
            'eng_persentase_bagi_hasil'     => 'nullable|numeric',
        ]);

        LaporanBagiHasil::create($data);

        return redirect()->route('laporan.index')->with('success', 'Data berhasil disimpan');
    }

    public function edit(LaporanBagiHasil $laporan)
    {
        return view('laporan.edit', compact('laporan'));
    }

    public function update(Request $request, LaporanBagiHasil $laporan)
    {
        $data = $request->validate([
            'no_cabang'   => 'required|string',
            'bimba_unit'  => 'required|string', // ✅ DIGANTI dari 'unit'
            'bulan'       => 'required|date',
            'nama_bank'   => 'required|string',
            'no_rekening' => 'required|string',
            'atas_nama'   => 'required|string',

            'bimba_murid_aktif_lalu'        => 'nullable|integer',
            'bimba_murid_baru'              => 'nullable|integer',
            'bimba_murid_kembali'           => 'nullable|integer',
            'bimba_murid_keluar'            => 'nullable|integer',
            'bimba_murid_aktif_ini'         => 'nullable|integer',
            'bimba_total_penerimaan_spp'    => 'nullable|integer',
            'bimba_persentase_bagi_hasil'   => 'nullable|numeric',

            'eng_murid_aktif_lalu'          => 'nullable|integer',
            'eng_murid_baru'                => 'nullable|integer',
            'eng_murid_kembali'             => 'nullable|integer',
            'eng_murid_keluar'              => 'nullable|integer',
            'eng_murid_aktif_ini'           => 'nullable|integer',
            'eng_total_penerimaan_spp'      => 'nullable|integer',
            'eng_persentase_bagi_hasil'     => 'nullable|numeric',
        ]);

        $laporan->update($data);

        return redirect()->route('laporan.index')->with('success', 'Data berhasil diupdate');
    }

    public function destroy(LaporanBagiHasil $laporan)
    {
        $laporan->delete();
        return redirect()->route('laporan.index')->with('success', 'Data berhasil dihapus');
    }
}
