<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;

class PettyCash extends Model
{
    protected $table = 'petty_cash';

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'kategori',
        'keterangan',

        // ✅ TAMBAHAN UNIT
        'bimba_unit',
        'no_cabang',

        'debit',
        'kredit',
        'saldo',
        'bukti',
    ];

    protected static function booted()
{
    static::addGlobalScope(new UnitScope);
}
}
