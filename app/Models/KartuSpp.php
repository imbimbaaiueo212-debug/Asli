<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KartuSpp extends Model
{
    use HasFactory;

    protected $table = 'kartu_spp';

    protected $fillable = [
        'no_pembayaran',
        'nama_murid',
        'golongan',
        'pembayaran_spp',
        'bimba_unit'
    ];

    // Relasi ke tabel units
public function unit()
{
    return $this->belongsTo(Unit::class, 'bimba_unit', 'biMBA_unit'); 
    // 'bimba_unit' di KartuSpp -> 'biMBA_unit' di Unit
}
}
