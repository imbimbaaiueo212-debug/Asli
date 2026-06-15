<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PenerimaanProduk extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_produk';

    protected $fillable = [
        'faktur',
        'unit_id',          // relasi ke tabel units
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
     * Relasi ke Unit biMBA (cabang)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Accessor: Nama unit lengkap dengan format dari model Unit
     * Contoh: GRIYA PESONA MADANI (05141)
     */
    public function getUnitLabelAttribute()
    {
        return $this->unit?->label ?? '-';
    }

    /**
     * Accessor: Hanya nama unit (tanpa nomor cabang)
     */
    public function getUnitNameAttribute()
    {
        return $this->unit?->nama_unit ?? '-';
    }

    /**
     * Accessor: Nomor cabang unit (5 digit)
     */
    public function getNoCabangAttribute()
    {
        return $this->unit?->no_cabang ?? '-';
    }

    /**
     * Accessor: Tanggal dalam format Indonesia
     */
    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal ? Carbon::parse($this->tanggal)->translatedFormat('d F Y') : '-';
    }

    /**
     * Accessor: Total harga dengan format rupiah (opsional untuk view)
     */
    public function getTotalFormattedAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Scope: Filter berdasarkan unit (berguna kalau ada multi-tenant)
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Filter berdasarkan periode bulan-tahun
     */
    public function scopeByPeriode($query, $periode) // format Y-m, contoh: 2026-01
    {
        return $query->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$periode]);
    }
}