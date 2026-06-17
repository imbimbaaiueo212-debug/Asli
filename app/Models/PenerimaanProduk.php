<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PenerimaanProduk extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_produk';

    protected $fillable = [
        'faktur',
        'unit_id',
        'tanggal',
        'minggu',
        'label',
        'jumlah',
        'kategori',
        'jenis',
        'nama_produk',
        'satuan',
        'harga',
        'status',
        'isi',
        'total',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'total'   => 'decimal:2',
        'harga'   => 'decimal:2',
        'jumlah'  => 'integer',
    ];

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

                // Unit-unit yang diizinkan
                $q->orWhereHas('unit', function ($u) {
                    $u->where('no_cabang', '00340')           // Villa Bekasi Indah 2
                      ->orWhere('no_cabang', '05141')        // Griya Pesona Madani
                      ->orWhere('no_cabang', '01045');       // Sapta Taruna 4
                });
            });
        });
    }

    // Accessors
    public function getUnitLabelAttribute()
    {
        return $this->unit?->label ?? '-';
    }

    public function getUnitNameAttribute()
    {
        return $this->unit?->biMBA_unit ?? '-';
    }

    public function getNoCabangAttribute()
    {
        return $this->unit?->no_cabang ?? '-';
    }

    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal ? Carbon::parse($this->tanggal)->translatedFormat('d F Y') : '-';
    }

    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    // Scope tambahan
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByPeriode($query, $periode)
    {
        return $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode]);
    }
}