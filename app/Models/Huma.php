<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\UnitScope;   // TAMBAHKAN INI

class Huma extends Model
{
    use HasFactory;

    protected $table = 'humas';

    protected $fillable = [
        'tgl_reg', 'nih', 'nama', 'status', 'no_telp',
        'pekerjaan', 'alamat', 'bimba_unit', 'no_cabang',
    ];

    protected $casts = [
        'tgl_reg' => 'date',
    ];

    // TAMBAHKAN INI — SUPAYA OTOMATIS FILTER PER UNIT
    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }
}