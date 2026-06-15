<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SertifikatBeasiswa extends Model
{
    use HasFactory;

    protected $table = 'sertifikat_beasiswa';

    protected $fillable = [
        'virtual_account',
        'nim',
        'nama',
        'bimba_unit',           // ← diubah dari nama_unit
        'tanggal_lahir',
        'alamat',
        'nama_orang_tua',
        'golongan',
        'jumlah_beasiswa',
        'tanggal_mulai',
        'tanggal_selesai',
        'periode_bea_ke',
    ];

    protected $casts = [
        'tanggal_lahir'   => 'date',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'jumlah_beasiswa' => 'decimal:2',
    ];

    // Scope yang sudah kita tambahkan sebelumnya, update ke bimba_unit
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (!$user || $user->isAdminUser()) {
            return $query; // admin lihat semua
        }

        $unitValue = $user->bimba_unit;  // ← langsung ambil dari user (string)

        if ($unitValue) {
            return $query->where('bimba_unit', $unitValue);
        }

        // Unit kosong → query kosong
        return $query->where('id', 0);
    }

    // Optional scope manual
    public function scopeOfUnit(Builder $query, string $unit): Builder
    {
        return $query->where('bimba_unit', $unit);
    }
}