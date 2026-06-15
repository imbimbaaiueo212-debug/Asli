<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;   // TAMBAHKAN INI
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PindahGolongan extends Model
{
    use HasFactory;

    protected $table = 'pindah_golongan';

    protected $fillable = [
        'nim',
        'nama',
        'bimba_unit',     // ⬅️ WAJIB
        'no_cabang',      // ⬅️ WAJIB
        'gol',
        'gol_baru',
        'kd',
        'kd_baru',
        'spp',
        'spp_baru',
        'guru',
        'tanggal_pindah_golongan',
        'keterangan',
        'alasan_pindah',
        'source',           // ← TAMBAHKAN INI
    ];

    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class, 'nim', 'nim');
    }

    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }
}
