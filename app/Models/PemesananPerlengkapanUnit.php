<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananPerlengkapanUnit extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_perlengkapan_unit';

    protected $fillable = [
        'unit_id',              // ← TAMBAH
        'tanggal_pemesanan',    // ← TAMBAH
        'kode',
        'kategori',
        'nama_barang',
        'jumlah',
        'harga',
        'minggu',               // ← SUDAH ADA, AKAN DIISI OTOMATIS
        'keterangan'
    ];
    // Atau di Laravel 8+ ke atas:
protected $casts = [
    'tanggal_pemesanan' => 'date:Y-m-d',
];

    // Relasi
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode', 'kode'); // asumsi kode unik
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}