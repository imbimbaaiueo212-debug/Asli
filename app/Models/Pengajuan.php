<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = [
        'tanggal',
        'keterangan_pengajuan',
        'harga',
        'jumlah',
        'total',

        // ✅ tambahan
        'bimba_unit',
        'no_cabang',
    ];
}
