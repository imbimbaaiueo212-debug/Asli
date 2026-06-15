<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelHistory extends Model
{
    protected $fillable = [
        'buku_induk_id',
        'level',
        'tgl_level',
        'keterangan',
    ];

    protected $casts = [
        'tgl_level' => 'date',
    ];

    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class);
    }
}