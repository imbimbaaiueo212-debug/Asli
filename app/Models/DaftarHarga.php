<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarHarga extends Model
{
    use HasFactory;

    protected $table = 'daftar_harga'; // <-- tambahkan ini

    protected $fillable = [
        'kategori',
        'sub_kategori',
        'unit',
        'deskripsi',
        'harga_a',
        'harga_b',
        'harga_c',
        'harga_d',
        'harga_e',
        'harga_f',
    ];
}
