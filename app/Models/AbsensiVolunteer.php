<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiVolunteer extends Model
{
    protected $table = 'absensi_volunteer';

    protected $fillable = [
        'id_fingerprint', 'nik', 'nama_relawan', 'posisi', 'bimba_unit',
        'no_cabang', 'tanggal', 'jam_masuk', 'jam_keluar', 'status',
        'keterangan', 'jam_lembur', 'onduty', 'offduty', 'alasan',
    ];

    // CASTING YANG BENAR-BENAR AMAN
    protected $casts = [
        'tanggal'     => 'date:Y-m-d',
        'jam_masuk'   => 'string',
        'jam_keluar'  => 'string',
        'onduty'      => 'string',
        'offduty'     => 'string',
        'jam_lembur'  => 'integer',
    ];

    // ACCESSOR: PASTIKAN FORMAT WAKTU SELALU H:i (08:00) DAN AMAN DARI ERROR
    public function getJamMasukAttribute($value)
    {
        return $this->formatTime($value);
    }

    public function getJamKeluarAttribute($value)
    {
        return $this->formatTime($value);
    }

    public function getOndutyAttribute($value)
    {
        return $this->formatTime($value) ?? '08:00';
    }

    public function getOffdutyAttribute($value)
    {
        return $this->formatTime($value) ?? '16:00';
    }

    // Fungsi bantu untuk format waktu (ini yang bikin tidak pernah error lagi)
    private function formatTime($value)
    {
        if (!$value || trim($value) === '' || trim($value) === 'NULL') {
            return null;
        }

        // Bersihkan dulu
        $value = trim($value);
        $value = preg_replace('/\s*(WIB|AM|PM).*$/i', '', $value); // hapus WIB, AM, dll
        $value = str_replace('.', ':', $value);

        // Ambil hanya 5 karakter pertama kalau lebih panjang (16:00:00 → 16:00)
        if (strlen($value) > 5 && str_contains($value, ':')) {
            $value = substr($value, 0, 5);
        }

        // Pastikan format H:i atau Hi
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $value, $m)) {
            return sprintf('%02d:%02d', $m[1], $m[2]);
        }

        return null;
    }

    // Filter otomatis berdasarkan unit (kecuali admin)
    protected static function booted()
    {
        static::addGlobalScope('unit', function (Builder $builder) {
            $user = Auth::user();
            if ($user && !$user->is_admin && $user->bimba_unit) {
                $builder->where('absensi_volunteer.bimba_unit', $user->bimba_unit);
            }
        });
    }

    
}