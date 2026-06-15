<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderModul extends Model
{
    use HasFactory;

    protected $table = 'order_moduls'; // Jika tabel memang plural, biarkan. Jika singular, hapus baris ini.

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
        'jml1' => 'integer',
        'jml2' => 'integer',
        'jml3' => 'integer',
        'jml4' => 'integer',
        'jml5' => 'integer',
        'hrg1' => 'integer',
        'hrg2' => 'integer',
        'hrg3' => 'integer',
        'hrg4' => 'integer',
        'hrg5' => 'integer',
        'sts1' => 'boolean',
        'sts2' => 'boolean',
        'sts3' => 'boolean',
        'sts4' => 'boolean',
        'sts5' => 'boolean',
    ];

    /**
     * Relasi ke Unit biMBA
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Accessor: Ambil semua item order yang terisi (kode + jumlah > 0)
     */
    public function getItemsAttribute()
    {
        $items = [];

        for ($i = 1; $i <= 5; $i++) {
            $kode = $this->{'kode' . $i};
            $jumlah = $this->{'jml' . $i} ?? 0;

            if ($kode && $jumlah > 0) {
                $items[] = [
                    'minggu'        => $i,
                    'kode'          => trim($kode),
                    'jumlah'        => $jumlah,
                    'harga_satuan'  => $this->{'hrg' . $i} / $jumlah, // jika hrg adalah total
                    'harga_total'   => $this->{'hrg' . $i},
                    'status_stok'   => $this->{'sts' . $i},
                ];
            }
        }

        return collect($items); // return Collection agar bisa chain method
    }

    /**
     * Accessor: Total harga semua minggu
     */
    public function getTotalHargaAttribute()
    {
        return $this->hrg1 + $this->hrg2 + $this->hrg3 + $this->hrg4 + $this->hrg5;
    }

    /**
     * Accessor: Tanggal order dalam format Indonesia
     */
    public function getTanggalFormattedAttribute()
    {
        return $this->tanggal_order?->translatedFormat('d F Y') ?? '-';
    }

    /**
     * Scope: Filter berdasarkan tahun
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('tanggal_order', $year);
    }

    /**
     * Scope: Filter berdasarkan bulan & tahun
     */
    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal_order', $year)
                     ->whereMonth('tanggal_order', $month);
    }

    /**
     * Scope: Filter berdasarkan unit
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope: Ambil order aktif bulan ini untuk unit tertentu
     */
    public function scopeCurrentMonthForUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId)
                     ->whereMonth('tanggal_order', now()->month)
                     ->whereYear('tanggal_order', now()->year);
    }
}