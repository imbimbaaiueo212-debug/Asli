<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranProgresif extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_progresif';

    protected $fillable = [
        'nama',
        'jabatan',
        'status',
        'departemen',
        'masa_kerja',
        'no_rekening',
        'bank',
        'atas_nama',
        'thp',
        'kurang',
        'lebih',
        'bulan',
        'transfer',
        'bimba_unit',
        'no_cabang'
    ];

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
