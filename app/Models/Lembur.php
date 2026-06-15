<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lembur';

    protected $fillable = [
        'profile_id',
        'tgl_lembur',
        'jam_awal',
        'jam_selesai',
        'total_jam',
        'keterangan',
        'status'
    ];

    protected $casts = [
        'tgl_lembur' => 'date',
        'jam_awal' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
        'total_jam' => 'decimal:2'
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    // Auto hitung total jam
    protected static function booted()
    {
        static::saving(function ($lembur) {
            if ($lembur->jam_awal && $lembur->jam_selesai) {
                $awal = \Carbon\Carbon::parse($lembur->jam_awal);
                $selesai = \Carbon\Carbon::parse($lembur->jam_selesai);
                
                $diff = $awal->diffInMinutes($selesai) / 60;
                $lembur->total_jam = round($diff, 2);
            }
        });
    }
}