<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranTunjangan extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_tunjangans';

    protected $fillable = [
        'nama',
        'jabatan',
        'status',
        'departemen',

        // ✅ tambah
        'bimba_unit',
        'no_cabang',
        // ⬆️

        'masa_kerja',
        'no_rekening',
        'bank',
        'atas_nama',
        'pendapatan',
        'potongan',
        'dibayarkan',
        'bulan',
        'nik',
    ];

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
