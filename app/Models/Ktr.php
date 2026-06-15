<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ktr extends Model
{
    use HasFactory;

    protected $fillable = [
        'waktu',
        'kategori',
        'jumlah',
    ];
}
