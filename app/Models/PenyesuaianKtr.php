<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyesuaianKtr extends Model
{
    use HasFactory;

    protected $table = 'penyesuaian_ktr';

    protected $fillable = ['jumlah_murid', 'penyesuaian_ktr'];
}
