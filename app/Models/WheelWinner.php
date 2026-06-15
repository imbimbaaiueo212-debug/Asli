<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WheelWinner extends Model
{
    protected $table = 'wheel_winners';

    protected $fillable = [
        'name',
        'voucher',
        'voucher_index',
        'voucher_amount',     // ← Penting! ini sering digunakan di controller
        'row_hash',
        'student_id',
        'bimba_unit',
        'no_cabang',
        'won_at',
    ];

    protected $casts = [
        'won_at'          => 'datetime',
        'voucher_amount'  => 'integer',
        'voucher_index'   => 'integer',
        'student_id'      => 'integer',
    ];

    // Optional: agar created_at & updated_at tetap otomatis
    public $timestamps = true;

    /**
     * Scope untuk query pemenang terbaru
     */
    public function scopeLatestWon($query)
    {
        return $query->orderBy('won_at', 'desc');
    }
}