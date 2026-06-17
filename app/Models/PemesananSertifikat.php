<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemesananSertifikat extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_sertifikat';

    protected $fillable = [
        'nim',
        'nama_murid',
        'tmpt_lahir',
        'tgl_lahir',
        'tgl_masuk',
        'tanggal_pemesanan',
        'level',
        'minggu',
        'keterangan',
        'bimba_unit',    
        'no_cabang',     
    ];

    protected $casts = [
        'tgl_lahir'          => 'date',
        'tgl_masuk'          => 'date',
        'tanggal_pemesanan'  => 'date',
    ];

    /**
     * Relasi ke PenerimaanProduk (jika ada)
     */
    public function penerimaan()
    {
        return $this->hasOne(PenerimaanProduk::class, 'pemesanan_sertifikat_id', 'id');
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
                // Filter utama sesuai user yang login
                if ($userUnit) {
                    $q->where('bimba_unit', 'LIKE', "%{$userUnit}%");
                }
                if ($userNoCabang) {
                    $q->orWhere('no_cabang', $userNoCabang);
                }

                // Unit-unit khusus yang diizinkan
                $q->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH%')
                  ->orWhere('no_cabang', '00340')

                  ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
                  ->orWhere('no_cabang', '05141')

                  ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA%')
                  ->orWhere('no_cabang', '01045');
            });
        });
    }

    /**
     * Accessor: Format tanggal Indonesia
     */
    public function getTanggalPemesananFormattedAttribute()
    {
        return $this->tanggal_pemesanan 
            ? Carbon::parse($this->tanggal_pemesanan)->translatedFormat('d F Y') 
            : '-';
    }

    /**
     * Scope tambahan
     */
    public function scopeByUnit($query, $unitIdOrName)
    {
        return $query->where('bimba_unit', 'LIKE', "%{$unitIdOrName}%")
                     ->orWhere('no_cabang', $unitIdOrName);
    }
}