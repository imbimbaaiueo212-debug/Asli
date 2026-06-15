<?php

namespace App\Http\Controllers;

use App\Models\CaraPerhitungan;

class CaraPerhitunganController extends Controller
{
    public function index()
    {
        // Jika tabel masih kosong, isi data awal
        if (CaraPerhitungan::count() === 0) {
            $data = [
                // GURU
                ['kategori' => 'Guru', 'range_fm' => '6-10', 'tarif' => 100000],
                ['kategori' => 'Guru', 'range_fm' => '11-15', 'tarif' => 200000],
                ['kategori' => 'Guru', 'range_fm' => '16-20', 'tarif' => 300000],
                ['kategori' => 'Guru', 'range_fm' => '21-25', 'tarif' => 450000],
                ['kategori' => 'Guru', 'range_fm' => '26-30', 'tarif' => 600000],
                ['kategori' => 'Guru', 'range_fm' => '31-35', 'tarif' => 800000],
                ['kategori' => 'Guru', 'range_fm' => '36-40', 'tarif' => 1000000],
                ['kategori' => 'Guru', 'range_fm' => '41-45', 'tarif' => 1400000],
                ['kategori' => 'Guru', 'range_fm' => '46-50', 'tarif' => 1500000],
                ['kategori' => 'Guru', 'range_fm' => '51-55', 'tarif' => 1600000],
                ['kategori' => 'Guru', 'range_fm' => '56-60', 'tarif' => 1800000],
                ['kategori' => 'Guru', 'range_fm' => '61-65', 'tarif' => 2000000],
                ['kategori' => 'Guru', 'range_fm' => '66-70', 'tarif' => 2100000],
                ['kategori' => 'Guru', 'range_fm' => '71-75', 'tarif' => 2400000],
                ['kategori' => 'Guru', 'range_fm' => '76-80', 'tarif' => 2600000],

                // KEPALA UNIT
                ['kategori' => 'Kepala Unit', 'range_fm' => '10-29', 'tarif' => 210000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '30-49', 'tarif' => 420000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '50-69', 'tarif' => 630000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '70-89', 'tarif' => 840000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '90-109', 'tarif' => 1050000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '110-129', 'tarif' => 1260000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '130-149', 'tarif' => 1470000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '150-169', 'tarif' => 1680000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '170-189', 'tarif' => 1890000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '190-209', 'tarif' => 2100000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '210-229', 'tarif' => 2310000],
                ['kategori' => 'Kepala Unit', 'range_fm' => '230-249', 'tarif' => 2520000],

                // KEPALA UA
                ['kategori' => 'Kepala UA', 'range_fm' => '250-269', 'tarif' => 2730000],
                ['kategori' => 'Kepala UA', 'range_fm' => '270-289', 'tarif' => 2940000],
                ['kategori' => 'Kepala UA', 'range_fm' => '290-309', 'tarif' => 3150000],
                ['kategori' => 'Kepala UA', 'range_fm' => '310-329', 'tarif' => 3360000],
                ['kategori' => 'Kepala UA', 'range_fm' => '330-349', 'tarif' => 3570000],
                ['kategori' => 'Kepala UA', 'range_fm' => '350-369', 'tarif' => 3780000],
                ['kategori' => 'Kepala UA', 'range_fm' => '370-389', 'tarif' => 3990000],
                ['kategori' => 'Kepala UA', 'range_fm' => '390-409', 'tarif' => 4200000],
                ['kategori' => 'Kepala UA', 'range_fm' => '410-429', 'tarif' => 4410000],
                ['kategori' => 'Kepala UA', 'range_fm' => '430-449', 'tarif' => 4620000],
                ['kategori' => 'Kepala UA', 'range_fm' => '450-469', 'tarif' => 4830000],
                ['kategori' => 'Kepala UA', 'range_fm' => '470-489', 'tarif' => 5040000],
            ];

            CaraPerhitungan::insert($data);
        }

        $caraPerhitungan = CaraPerhitungan::orderBy('kategori')->get();

        return view('cara_perhitungan.index', compact('caraPerhitungan'));
    }
}
