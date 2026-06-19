<?php

namespace App\Models;
use App\Models\Scopes\UnitScope;   // ← Tambahkan ini

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

    protected static function booted()
    {
        // Auto hitung total jam (yang sudah ada)
        static::saving(function ($lembur) {
            if ($lembur->jam_awal && $lembur->jam_selesai) {
                $awal = \Carbon\Carbon::parse($lembur->jam_awal);
                $selesai = \Carbon\Carbon::parse($lembur->jam_selesai);
                
                $diff = $awal->diffInMinutes($selesai) / 60;
                $lembur->total_jam = round($diff, 2);
            }
        });

        // ← TAMBAHKAN SCOPE DI SINI
        static::addGlobalScope(new UnitScope());
    }
}