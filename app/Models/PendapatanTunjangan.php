<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendapatanTunjangan extends Model
{
    use HasFactory;

    protected $table = 'pendapatan_tunjangan';

    protected $fillable = [
        'nik',
        'nama',
        'jabatan',
        'status',
        'departemen',
        'bimba_unit',
        'no_cabang',
        'masa_kerja',
        'thp',
        'kerajinan',
        'english',
        'mentor',
        'kekurangan',
        'bulan_kekurangan',
        'bulan',
        'tj_keluarga',
        'lain_lain',
        // 'total' → HAPUS dari fillable & casts, karena kita hitung via accessor
    ];

    protected $appends = [
        'masa_kerja_format',
        'total',           // tetap di-append agar selalu ada di toArray()
    ];

    protected $casts = [
        'masa_kerja'       => 'integer',
        'thp'              => 'decimal:2',
        'kerajinan'        => 'decimal:2',
        'english'          => 'decimal:2',
        'mentor'           => 'decimal:2',
        'kekurangan'       => 'decimal:2',
        'tj_keluarga'      => 'decimal:2',
        'lain_lain'        => 'decimal:2',
        // 'total' → tidak perlu di-cast karena accessor mengembalikan float
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'nama', 'nama');
    }

    // Mutator masa_kerja
    public function setMasaKerjaAttribute($value): void
    {
        $this->attributes['masa_kerja'] = $value === null || $value === '' || $value === '-'
            ? 0
            : (int) $value;
    }

    // Accessor masa_kerja_format
    public function getMasaKerjaFormatAttribute(): string
    {
        $mk = $this->masa_kerja ?? 0;   // pakai accessor property, bukan attributes langsung

        $years  = intdiv($mk, 12);
        $months = $mk % 12;

        return "{$years} tahun {$months} bulan";
    }

    // Accessor total (selalu hitung ulang → paling akurat)
    public function getTotalAttribute(): float
    {
        return round(
            ($this->thp ?? 0) +
            ($this->kerajinan ?? 0) +
            ($this->english ?? 0) +
            ($this->mentor ?? 0) +
            ($this->kekurangan ?? 0) +
            ($this->tj_keluarga ?? 0) +
            ($this->lain_lain ?? 0),
            2
        );
    }

    // Mutator thp (aman)
    public function setThpAttribute($value): void
    {
        $this->attributes['thp'] = $value !== null ? (float) $value : 0.0;
    }

    // Opsional: Helper method untuk cek apakah ini data manual (ada penyesuaian)
    public function isManualAdjustment(): bool
    {
        return ($this->kerajinan > 0) ||
               ($this->english > 0) ||
               ($this->mentor > 0) ||
               ($this->kekurangan > 0) ||
               ($this->tj_keluarga > 0) ||
               ($this->lain_lain > 0);
    }

    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }
}