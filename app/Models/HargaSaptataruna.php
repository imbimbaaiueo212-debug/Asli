<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaSaptataruna extends Model
{
    use HasFactory;

    protected $table = 'harga_saptataruna';

    protected $fillable = [
        'kategori', 'sub_kategori', 'kode', 'nama', 'duafa', 'promo_2019', 'daftar_ulang',
        'spesial', 'umum1', 'umum2', 'harga', 'a', 'b', 'c', 'd', 'e', 'f', 'promo_umum',
    ];
}

