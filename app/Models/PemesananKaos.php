<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemesananKaos extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_bukti',
        'tanggal',
        'unit_id',
        'nim',
        'nama_murid',
        'gol',
        'tgl_masuk',
        'lama_bljr',
        'guru',

        'kaos',
        'kaos_panjang',
        'size',
        'size_pendek',
        'size_panjang',
        'kpk',
        'kode_tas',
        'jumlah_tas',
        'rbas',
        'bcabs01',
        'bcabs02',
        'sertifikat',
        'stpb',
        'keterangan'
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'tgl_masuk'  => 'date',
        'jumlah_tas' => 'integer',
    ];

    /**
     * Relasi ke Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * GLOBAL SCOPE UNIT - PENTING!
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
                // Filter sesuai unit user yang login
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
     * Accessor: Format tanggal Indonesia
     */
    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal ? Carbon::parse($this->tanggal)->translatedFormat('d F Y') : '-';
    }

    /**
     * Scope tambahan (opsional)
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}