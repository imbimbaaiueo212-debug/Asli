<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyesuaianRbGuru extends Model
{
    use HasFactory;

    protected $table = 'penyesuaian_rb_guru';

    protected $fillable = [
        'jumlah_murid',
        'slot_rombim',
        'jam_kegiatan',
        'penyesuaian_rb'
    ];
}
