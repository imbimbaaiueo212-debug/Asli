<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemakaianProduk extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_produk';

    /**
     * Kolom yang boleh diisi secara mass assignment
     */
    protected $fillable = [
        'unit_id',           // <-- BARU: Unit biMBA tempat pemakaian terjadi
        'tanggal',
        'minggu',
        'label',
        'jumlah',
        'nim',
        'kategori',
        'jenis',
        'nama_produk',
        'satuan',
        'harga',
        'total',
        'nama_murid',
        'gol',
        'guru',
    ];

    /**
     * Cast attribute untuk tipe data yang benar
     */
    protected $casts = [
        'tanggal' => 'date:Y-m-d',
        'minggu'  => 'integer',
        'jumlah'  => 'integer',
        'harga'   => 'integer',
        'total'   => 'integer',
    ];

    /**
     * Relasi ke Unit biMBA
     * Pemakaian ini milik unit mana
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope: Filter berdasarkan unit
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Filter berdasarkan periode (bulan)
     */
    public function scopePeriode($query, $yearMonth)
    {
        return $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$yearMonth]);
    }

    /**
     * Scope: Filter berdasarkan tanggal
     */
    public function scopeTanggal($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    /**
     * Accessor: Format tanggal Indonesia
     */
    public function getTanggalFormattedAttribute(): string
    {
        return $this->tanggal->format('d-m-Y');
    }

    /**
     * Accessor: Total dalam format rupiah
     */
    public function getTotalFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}