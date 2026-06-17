<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemesananSTPB extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_stpb';

    protected $fillable = [
        'nim',
        'nama_murid',
        'tmpt_lahir',
        'tgl_lahir',
        'tgl_masuk',
        'nama_orang_tua',
        'level',
        'tgl_level',
        'minggu',
        'keterangan',
        'unit_id',
        'tgl_lulus',
        'tgl_pemesanan',
    ];

    protected $casts = [
        'tgl_lahir'      => 'date',
        'tgl_masuk'      => 'date',
        'tgl_level'      => 'date',
        'tgl_lulus'      => 'date',
        'tgl_pemesanan'  => 'date',
    ];

    /**
     * Relasi ke Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke BukuInduk
     */
    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class, 'nim', 'nim');
    }

    /**
     * GLOBAL SCOPE UNIT
     */
    protected static function booted()
    {
        static::addGlobalScope('unit', function (Builder $builder) {
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();

            // Admin & Superadmin boleh lihat semua
            if ($user->is_admin ?? false || in_array($user->role ?? '', ['admin', 'superadmin', 'keuangan'])) {
                return;
            }

            $userUnit     = trim($user->bimba_unit ?? '');
            $userNoCabang = trim($user->no_cabang ?? '');

            $builder->where(function ($q) use ($userUnit, $userNoCabang) {
                // Filter berdasarkan relasi unit_id
                if ($userUnit) {
                    $q->whereHas('unit', function ($u) use ($userUnit) {
                        $u->where('biMBA_unit', 'LIKE', "%{$userUnit}%");
                    });
                }

                if ($userNoCabang) {
                    $q->orWhereHas('unit', function ($u) use ($userNoCabang) {
                        $u->where('no_cabang', $userNoCabang);
                    });
                }

                // Unit-unit khusus yang diizinkan
                $q->orWhereHas('unit', function ($u) {
                    $u->whereIn('no_cabang', ['00340', '05141', '01045']);
                });
            });
        });
    }

    /**
     * Accessor: Minggu dalam bulan
     */
    public function getMingguDalamBulanAttribute(): ?int
    {
        if (!$this->tgl_pemesanan) {
            return null;
        }

        $hari = Carbon::parse($this->tgl_pemesanan)->day;
        return (int) ceil($hari / 7);
    }

    /**
     * Scope tambahan
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}