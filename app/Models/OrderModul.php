<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderModul extends Model
{
    use HasFactory;

    protected $table = 'order_moduls';

    protected $fillable = [
        'tanggal_order',
        'unit_id',
        'status',
        'kode1', 'jml1', 'hrg1', 'sts1',
        'kode2', 'jml2', 'hrg2', 'sts2',
        'kode3', 'jml3', 'hrg3', 'sts3',
        'kode4', 'jml4', 'hrg4', 'sts4',
        'kode5', 'jml5', 'hrg5', 'sts5',
        'harga_satuan',
    ];

    protected $casts = [
        'tanggal_order' => 'date',
        'jml1' => 'integer', 'jml2' => 'integer', 'jml3' => 'integer',
        'jml4' => 'integer', 'jml5' => 'integer',
        'hrg1' => 'integer', 'hrg2' => 'integer', 'hrg3' => 'integer',
        'hrg4' => 'integer', 'hrg5' => 'integer',
        'sts1' => 'boolean', 'sts2' => 'boolean', 'sts3' => 'boolean',
        'sts4' => 'boolean', 'sts5' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Accessors (tetap sama)
    public function getItemsAttribute()
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $kode = $this->{'kode' . $i};
            $jumlah = $this->{'jml' . $i} ?? 0;

            if ($kode && $jumlah > 0) {
                $items[] = [
                    'minggu'       => $i,
                    'kode'         => trim($kode),
                    'jumlah'       => $jumlah,
                    'harga_satuan' => $this->{'hrg' . $i} / $jumlah,
                    'harga_total'  => $this->{'hrg' . $i},
                    'status_stok'  => $this->{'sts' . $i},
                ];
            }
        }
        return collect($items);
    }

    public function getTotalHargaAttribute()
    {
        return $this->hrg1 + $this->hrg2 + $this->hrg3 + $this->hrg4 + $this->hrg5;
    }

    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal_order?->translatedFormat('d F Y') ?? '-';
    }

    // Scopes
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('tanggal_order', $year);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal_order', $year)
                     ->whereMonth('tanggal_order', $month);
    }

    public function scopeCurrentMonthForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId)
                     ->whereMonth('tanggal_order', now()->month)
                     ->whereYear('tanggal_order', now()->year);
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
                // Filter berdasarkan relasi unit
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

                // Unit khusus yang diizinkan
                $q->orWhereHas('unit', function ($u) {
                    $u->whereIn('no_cabang', ['00340', '05141', '01045']);
                });
            });
        });
    }
}