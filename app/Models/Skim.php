<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skim extends Model
{
    use HasFactory;

    protected $table = 'skim';

    protected $fillable = [
        'jabatan',
        'masa_kerja',
        'status',
        'tunj_pokok',
        'harian',
        'fungsional',
        'kesehatan',
        'thp',
        'tunj_khusus',
        'jumlah',
    ];
}
