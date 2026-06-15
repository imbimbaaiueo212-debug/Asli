<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuIndukHistory extends Model
{
    protected $fillable = [
    'buku_induk_id',
    'action',
    'user',
    'old_data',
    'new_data',
];

protected $casts = [
    'old_data' => 'array',
    'new_data' => 'array',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
];


    public function bukuInduk()
    {
        return $this->belongsTo(BukuInduk::class);
    }
}
