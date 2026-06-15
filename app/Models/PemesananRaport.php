<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemesananRaport extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_raport';

    protected $fillable = [
        'nim',
        'nama_murid',
        'gol',
        'tgl_masuk',
        'lama_bljr',
        'guru',
        'keterangan'
    ];
}
