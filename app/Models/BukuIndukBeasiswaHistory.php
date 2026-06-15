<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuIndukBeasiswaHistory extends Model
{
    protected $table = 'buku_induk_beasiswa_history';

    protected $fillable = [
        'nim',
        'nama',
        'periode',
        'tgl_mulai',
        'tgl_akhir',
        'jumlah_beasiswa',
        'status',
        'alamat_murid',
        'orangtua',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_akhir' => 'date',
    ];

    public function scopeActive($q)
{
    return $q->where('status', 'aktif');
}
}