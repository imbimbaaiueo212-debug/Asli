<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PemesananSertifikat extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_sertifikat';

    protected $fillable = [
        'nim',
        'nama_murid',
        'tmpt_lahir',
        'tgl_lahir',
        'tgl_masuk',
        'tanggal_pemesanan',
        'level',
        'minggu',
        'keterangan',
        'bimba_unit',    // ← TAMBAH
        'no_cabang',     // ← TAMBAH (sesuai buku induk)
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_masuk' => 'date',
        'tanggal_pemesanan' => 'date',  // ← TAMBAH INI AGAR FORMAT BENAR   
    ];
   public function penerimaan()
{
    return $this->hasOne(PenerimaanProduk::class, 'pemesanan_sertifikat_id', 'id');
}
}