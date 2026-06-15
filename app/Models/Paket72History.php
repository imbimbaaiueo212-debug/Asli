<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket72History extends Model
{
    protected $table = 'paket72_history';

    protected $fillable = [
        'nim',
        'nama',
        'tgl_bayar',
        'tgl_selesai',
        'status',
    ];

    protected $casts = [
        'tgl_bayar' => 'date',
        'tgl_selesai' => 'date',
    ];
}
