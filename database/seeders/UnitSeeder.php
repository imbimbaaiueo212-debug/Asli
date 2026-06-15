<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        Unit::create([
            'no_cabang' => '1045',
            'biMBA_unit' => 'SAPTA TARUNA IV',
            'staff_sos' => '-',
            'telp' => '081575016372',
            'email' => 'bimba_saptataruna@yahoo.com',
            'bank_nama' => 'MANDIRI',
            'bank_nomor' => '165 000 1440 503',
            'bank_atas_nama' => 'ROBIANSYAH',
            'alamat_jalan' => 'Komplek PU Sapta Taruna IV. Jl. KOPERPU XII BLOK C N0 74',
            'alamat_rt_rw' => '002/006',
            'alamat_kode_pos' => '17154',
            'alamat_kel_des' => 'SUMUR BATU',
            'alamat_kecamatan' => 'BANTAR GEBANG',
            'alamat_kota_kab' => 'Bekasi',
            'alamat_provinsi' => 'Jawa Barat'
        ]);
    }
}
