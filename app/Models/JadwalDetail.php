<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class JadwalDetail extends Model
{
    use HasFactory;

    protected $table = 'jadwal_detail';

    protected $fillable = [
        'murid_id',
        'hari',
        'shift',
        'jam_ke',
        'guru',
        'kelas',
        'kode_jadwal',
        'jenis_kbm',
    ];

    // ===================================================================
    // GLOBAL SCOPE: FILTER OTOMATIS BERDASARKAN UNIT MURID (VIA buku_induk)
    // ===================================================================
    protected static function booted()
    {
        static::addGlobalScope('unit', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Admin / Pusat / Developer → boleh lihat semua jadwal semua unit
                if ($user->is_admin) {
                    return;
                }

                // User biasa → hanya boleh lihat jadwal murid dari unitnya sendiri
                if ($user->bimba_unit) {
                    $builder->whereHas('murid', function ($q) use ($user) {
                        $q->where('bimba_unit', $user->bimba_unit);
                    });
                } else {
                    // Tidak punya unit → blokir total
                    $builder->whereRaw('1 = 0');
                }
            }
        });
    }

    /**
     * Relasi ke murid (buku_induk)
     * Ini yang dipakai untuk filter unit di atas
     */
    public function murid()
    {
        return $this->belongsTo(\App\Models\BukuInduk::class, 'murid_id', 'id');
        // atau kalau pakai nim: 'murid_id', 'nim'
    }

    /**
     * Relasi ke guru (opsional, kalau mau)
     */
    public function guruProfile()
    {
        return $this->belongsTo(\App\Models\Profile::class, 'guru', 'nama');
    }

    /**
     * Scope: Ambil jadwal berdasarkan hari & shift (contoh penggunaan)
     */
    public function scopeHariShift($query, $hari, $shift)
    {
        return $query->where('hari', $hari)->where('shift', $shift);
    }

    /**
     * Accessor: Format jam (misal: "Jam ke 1" → "Jam 1")
     */
    public function getJamKeLabelAttribute()
    {
        return 'Jam ' . $this->jam_ke;
    }
}