<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashAdvance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_id', // pastikan ini ada jika sudah ditambahkan
        'nama',
        'jabatan',
        'no_telepon',
        'alamat',
        'bulan_pengajuan',
        'nominal_pinjam',
        'jangka_waktu',
        'keperluan',
        'status',
        'approved_by',
        'approved_at',
        'angsuran_per_bulan',
    ];

    public function user()
    {
        return $this->belongsTo(Profile::class); // atau User jika diperlukan
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessor untuk nama bulan dalam bahasa Indonesia
    public function getBulanPengajuanIndoAttribute()
    {
        $bulanInggris = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $bulanIndonesia = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        return str_replace($bulanInggris, $bulanIndonesia, $this->bulan_pengajuan);
    }
    public function installments()
{
    return $this->hasMany(CashAdvanceInstallment::class);
}
public function profile()
{
    return $this->belongsTo(Profile::class, 'nama', 'nama'); // match berdasarkan nama
}
}