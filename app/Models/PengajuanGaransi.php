<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanGaransi extends Model
{
    protected $table = 'pengajuan_garansi';

    protected $fillable = [
        'nim',
        'nama_murid',
        'bimba_unit',
        'tgl_pengajuan',
        'alasan',
        'status'
    ];
}
