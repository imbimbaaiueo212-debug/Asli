<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvanceInstallment extends Model
{
    protected $fillable = [
        'cash_advance_id', 'cicilan_ke', 'jatuh_tempo', 'nominal_angsuran',
        'sudah_dibayar', 'tanggal_bayar', 'status', 'keterangan'
    ];

    protected $casts = [
        'jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
