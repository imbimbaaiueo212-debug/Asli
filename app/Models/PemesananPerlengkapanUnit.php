<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PemesananPerlengkapanUnit extends Model
{
    use HasFactory;

    protected $table = 'pemesanan_perlengkapan_unit';

    protected $fillable = [
        'unit_id',
        'tanggal_pemesanan',
        'kode',
        'kategori',
        'nama_barang',
        'jumlah',
        'harga',
        'minggu',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_pemesanan' => 'date',
        'jumlah'            => 'integer',
        'harga'             => 'integer',
    ];

    /**
     * Relasi ke Produk
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode', 'kode');
    }

    /**
     * Relasi ke Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
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
                // Filter berdasarkan unit_id
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
        return $this->tanggal_pemesanan 
            ? Carbon::parse($this->tanggal_pemesanan)->translatedFormat('d F Y') 
            : '-';
    }

    /**
     * Scope tambahan
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}