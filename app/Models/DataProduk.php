<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataProduk extends Model
{
    use HasFactory;

    protected $table = 'data_produk';

    /**
     * Kolom yang boleh diisi secara mass assignment
     */
    protected $fillable = [
    'unit_id',
    'periode',
    'kode',
    'jenis',
    'label',
    'satuan',
    'harga',
    'min_stok',
    'sld_awal',
    'sld_akhir',
    'terima',
    'pakai',
    'opname',
    'selisih',
    'nilai',
    'saldo_sistem',

    // =========================
    // ADJUSTMENT
    // =========================
    'jenis_adjustment',
    'qty_adjustment',
    'keterangan_adjustment',
    'adjusted_at',
    'adjusted_by',
];

    /**
     * Cast attribute agar tipe data sesuai
     */
   protected $casts = [
    'periode'   => 'date:Y-m',
    'harga'     => 'integer',
    'min_stok'  => 'integer',
    'sld_awal'  => 'integer',
    'terima'    => 'integer',
    'pakai'     => 'integer',
    'opname'    => 'integer',
    'sld_akhir' => 'integer',
    'selisih'   => 'integer',
    'saldo_sistem' => 'integer',
    'nilai'     => 'decimal:2',

    // adjustment
    'qty_adjustment' => 'integer',
    'adjusted_at'    => 'datetime',
];

    // ========================================
    // RELASI
    // ========================================

    /**
     * Relasi ke Unit biMBA
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke Master Produk (berdasarkan kode)
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'kode', 'kode');
    }

    // ========================================
    // METHOD CALCULATED (bukan accessor)
    // ========================================

    /**
     * Hitung saldo akhir secara manual
     * Digunakan di view/controller jika diperlukan
     * (tidak override attribute sld_akhir di database)
     */
    public function calculateSaldoSistem(): int
{
    return
        (int)$this->sld_awal +
        (int)$this->terima -
        (int)$this->pakai;
}

    // ========================================
    // ACCESSOR (hanya yang tidak bentrok dengan kolom DB)
    // ========================================

   /**
 * Status stok berdasarkan perhitungan (sama seperti di form edit)
 */
public function getStatusAttribute(): string
{
    $akhir = (int) $this->sld_akhir;

    $min = (int) ($this->min_stok ?? 0);

    if ($akhir <= 0) {
        return 'HABIS_TOTAL';
    }

    return $akhir >= $min
        ? 'STOK AMAN'
        : 'STOK KURANG';
}

/**
 * Selisih stok (sld_awal sistem - opname)
 */
public function getSelisihAttribute(): int
{
    return (int) ($this->attributes['selisih'] ?? 0);
}

/**
 * Nilai selisih (selisih × harga)
 */
public function getNilaiSelisihAttribute(): int
{
    return $this->selisih * ($this->harga ?? 0);
}

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope: Filter berdasarkan periode
     */
    public function scopePeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * Scope: Filter berdasarkan unit
     */
    public function scopeUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Hanya produk yang diizinkan (modul biMBA dll)
     */
    public function scopeAllowedJenis($query, array $allowedJenis)
    {
        return $query->where(function ($q) use ($allowedJenis) {
            $q->whereHas('produk', fn($q) => $q->whereIn('jenis', $allowedJenis))
              ->orWhereIn('jenis', $allowedJenis);
        });
    }
    public function setSelisihAttribute($value)
    {
        $this->attributes['selisih'] = ($value === '' || $value === null) 
            ? null 
            : (int) $value;
    }

    /**
 * Mutator: Setiap kali opname di-set, hitung nilai otomatis
 */

}