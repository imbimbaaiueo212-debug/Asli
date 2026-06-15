<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PerkembanganUnit extends Model
{
    use HasFactory;

    protected $table = 'perkembangan_units';
    public $timestamps = false;

    protected $fillable = [
        'bimba_unit', 'no_cabang', 'tgl', 'bl',
        '01','02','03','04','05','06','07','08','09','10',
        '11','12','13','14','15','16','17','18','19','20',
        '21','22','23','24','25','26','27','28','29','30','31',
        'T',
    ];

    protected $casts = [
        'tgl' => 'date:Y-m-d',
        'bl'  => 'integer',
        'T'   => 'integer',
        // Cast semua kolom hari jadi integer agar aman
        '01' => 'integer', '02' => 'integer', '03' => 'integer', '04' => 'integer', '05' => 'integer',
        '06' => 'integer', '07' => 'integer', '08' => 'integer', '09' => 'integer', '10' => 'integer',
        '11' => 'integer', '12' => 'integer', '13' => 'integer', '14' => 'integer', '15' => 'integer',
        '16' => 'integer', '17' => 'integer', '18' => 'integer', '19' => 'integer', '20' => 'integer',
        '21' => 'integer', '22' => 'integer', '23' => 'integer', '24' => 'integer', '25' => 'integer',
        '26' => 'integer', '27' => 'integer', '28' => 'integer', '29' => 'integer', '30' => 'integer',
        '31' => 'integer',
    ];

    // Default value semua kolom hari & total = 0
    protected $attributes = [
        '01'=>0, '02'=>0, '03'=>0, '04'=>0, '05'=>0, '06'=>0, '07'=>0, '08'=>0, '09'=>0, '10'=>0,
        '11'=>0, '12'=>0, '13'=>0, '14'=>0, '15'=>0, '16'=>0, '17'=>0, '18'=>0, '19'=>0, '20'=>0,
        '21'=>0, '22'=>0, '23'=>0, '24'=>0, '25'=>0, '26'=>0, '27'=>0, '28'=>0, '29'=>0, '30'=>0, '31'=>0,
        'T'=>0,
    ];

    /**
     * Daftar kolom hari (01 sampai 31)
     */
    public static function dayColumns(): array
    {
        return array_map(fn($i) => str_pad($i, 2, '0', STR_PAD_LEFT), range(1, 31));
    }

    /**
     * Hitung total harian (T)
     */
    public function calculateTotal(): int
    {
        return array_sum(array_map(fn($col) => (int) $this->getAttribute($col), self::dayColumns()));
    }

    /**
     * Booted method: global scope + auto hitung total sebelum save
     */
    protected static function booted()
    {
        // Auto hitung total sebelum simpan atau update
        static::saving(function ($model) {
            $model->T = $model->calculateTotal();
        });

        // Pasang global scope UnitScope (batasi berdasarkan unit user)
        static::addGlobalScope(new UnitScope);
    }

    // ===================== SCOPE QUERY =====================

    public function scopeUnit(Builder $query, string $bimbaUnit): Builder
    {
        return $query->where('bimba_unit', $bimbaUnit);
    }

    public function scopeCabang(Builder $query, string $noCabang): Builder
    {
        return $query->where('no_cabang', $noCabang);
    }

    public function scopeUnitCabang(Builder $query, string $bimbaUnit, string $noCabang): Builder
    {
        return $query->where('bimba_unit', $bimbaUnit)
                     ->where('no_cabang', $noCabang);
    }

    public function scopeBulanTahun(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->where('bl', $bulan)
                     ->whereYear('tgl', $tahun);
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->whereYear('tgl', $tahun);
    }

    /**
     * Scope untuk admin: abaikan global scope unit
     */
    public function scopeAllUnits(Builder $query): Builder
    {
        return $query->withoutGlobalScope(UnitScope::class);
    }
}