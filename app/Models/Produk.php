<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderModul;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    // 🔴 FIX UTAMA
    public $timestamps = false;

    protected $fillable = [
        'unit_id',
        'kode',
        'kategori',
        'jenis',
        'label',
        'nama_produk',
        'satuan',
        'berat',
        'harga',
        'status',
        'isi',
        'pendataan',
        'bimba_unit',
        'no_cabang',
    ];



    public function getHargaFormatAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getMingguOrderAttribute()
    {
        $minggu = [];

        for ($i = 1; $i <= 5; $i++) {
            $hasOrder = OrderModul::where("kode{$i}", $this->label)
                ->whereNotNull("jml{$i}")
                ->where("jml{$i}", '>', 0)
                ->exists();

            if ($hasOrder) {
                $minggu[] = $i;
            }
        }

        return implode(',', $minggu);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Aktif');
    }
}
