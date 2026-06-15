<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembayaranProgresif;
use App\Models\RekapProgresif;
use App\Models\Profile;

class PembayaranProgresifController extends Controller
{

    public function index(Request $request)
    {

        $bulanFilter     = $request->get('bulan');
        $namaFilter      = $request->get('nama');
        $bimbaUnitFilter = $request->get('bimba_unit');
        $cabangFilter    = $request->get('no_cabang');


        /*
        ============================================================
        SINKRONISASI DATA DARI REKAP PROGRESIF
        ============================================================
        */

        $rekapList = RekapProgresif::when($bulanFilter,
            fn($q)=>$q->where('bulan',$bulanFilter)
        )->get();


        foreach ($rekapList as $rekap) {

            $jabatan = strtoupper(trim($rekap->jabatan ?? ''));

            if (!in_array($jabatan, ['GURU','KEPALA UNIT'])) {
                continue;
            }


            /*
            ============================================================
            AMBIL PROFILE BERDASARKAN NAMA (CASE INSENSITIVE)
            ============================================================
            */

            $profile = Profile::whereRaw('LOWER(nama)=?',
                [strtolower($rekap->nama)]
            )->first();



            $bimbaUnit = $rekap->bimba_unit
                ?? $profile?->biMBA_unit
                ?? $profile?->bimba_unit
                ?? $profile?->unit;


            $noCabang = $rekap->no_cabang
                ?? $profile?->no_cabang;


            /*
            ============================================================
            AMBIL PROGRESIF DARI REKAP
            ============================================================
            */

            $progresif = $rekap->progresif ?? 0;


            /*
            ============================================================
            THP = PROGRESIF
            ============================================================
            */

            $thp = $progresif;



            PembayaranProgresif::updateOrCreate(

                [
                    'nama'       => $rekap->nama,
                    'bulan'      => $rekap->bulan,
                    'bimba_unit' => $bimbaUnit,
                ],

                [

                    'nama'        => $rekap->nama,
                    'jabatan'     => $rekap->jabatan,
                    'status'      => $rekap->status,
                    'departemen'  => $rekap->departemen,
                    'masa_kerja'  => $rekap->masa_kerja,
                    'bulan'       => $rekap->bulan,

                    'no_rekening' => $profile?->no_rekening,
                    'bank'        => $profile?->bank,
                    'atas_nama'   => $profile?->nama_rekening ?? $profile?->atas_nama,

                    'total_fm'    => $rekap->total_fm,
                    'progresif'   => $progresif,

                    'thp'         => $thp,

                    'kurang'      => 0,
                    'lebih'       => 0,
                    'transfer'    => 0,

                    'bimba_unit'  => $bimbaUnit,
                    'no_cabang'   => $noCabang,
                ]
            );

        }



        /*
        ============================================================
        QUERY DATA PEMBAYARAN
        ============================================================
        */

        $pembayaran = PembayaranProgresif::query()

            ->when($bulanFilter,
                fn($q)=>$q->where('bulan',$bulanFilter))

            ->when($namaFilter,
                fn($q)=>$q->where('nama','like',"%{$namaFilter}%"))

            ->when($bimbaUnitFilter,
                fn($q)=>$q->where('bimba_unit',$bimbaUnitFilter))

            ->when($cabangFilter,
                fn($q)=>$q->where('no_cabang',$cabangFilter))

            ->orderBy('bimba_unit')
            ->orderBy('no_cabang')
            ->orderBy('nama')

            ->get();



        $bimbaUnitList = PembayaranProgresif::select('bimba_unit')
            ->distinct()
            ->orderBy('bimba_unit')
            ->pluck('bimba_unit');


        $cabangList = PembayaranProgresif::select('no_cabang')
            ->distinct()
            ->orderBy('no_cabang')
            ->pluck('no_cabang');


        $karyawanList = PembayaranProgresif::select('nama')
            ->distinct()
            ->orderBy('nama')
            ->pluck('nama');


        $karyawanPerUnit = PembayaranProgresif::select('bimba_unit','nama')
            ->distinct()
            ->get()
            ->groupBy('bimba_unit')
            ->map(fn($g)=>$g->pluck('nama')->values())
            ->toJson();


        return view('pembayaran-progresif.index',compact(

            'pembayaran',
            'bimbaUnitList',
            'cabangList',
            'karyawanList',
            'karyawanPerUnit'

        ));

    }

}