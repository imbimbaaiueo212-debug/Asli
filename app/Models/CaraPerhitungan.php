<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaraPerhitungan extends Model
{
    use HasFactory;

    // Nama tabel HARUS sama persis dengan yang ada di database
    protected $table = 'cara_perhitungan';

    protected $fillable = [
        'kategori',
        'range_fm',
        'tarif',
    ];
}
