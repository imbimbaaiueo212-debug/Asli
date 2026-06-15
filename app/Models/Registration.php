<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Registration extends Model
{
    use HasFactory;

    public const STATUSES = ['pending','verified','accepted','rejected'];

    protected $fillable = [
        'student_id', 
        'gelombang', 
        'program', 
        'status', 
        'tanggal_daftar',
        'tahun_ajaran', 
        'attachment_path',

        // Data Akademik
        'tahap', 
        'tgl_tahapan', 
        'level',           // ← BARU
        'tgl_level',       // ← BARU
        'kelas', 
        'gol', 
        'kd', 
        'spp',
        'guru', 
        'kode_jadwal', 
        'hari_jam',
        'jenis_kbm',

        // Data Tambahan Murid
        'no_telp_hp',      // ← BARU
        'alamat_murid',    // ← BARU
        'asal_modul',      // ← BARU

        // Unit & Cabang
        'bimba_unit', 
        'no_cabang',

        // ==================== FIELD BARU DITAMBAHKAN ====================

        // Dhuafa & BNF
        'periode',
        'tgl_mulai',
        'tgl_akhir',
        'alert',                    // contoh: "aktif" / null

        // Paket 72
        'tgl_bayar',
        'tgl_selesai',
        'alert2',                   // contoh: "aktif" / null

        // Surat Garansi BCA
        'tgl_surat_garansi',        // ← BARU
        'tgl_pengajuan_garansi',
        'tgl_selesai_garansi',
        'masa_aktif_garansi',
        'perpanjang_garansi',       // boolean atau "ya"/"tidak"
        'alasan_garansi',

        // Data Penerimaan
        'kwitansi', 
        'via', 
        'bulan', 
        'tahun', 
        'tanggal_penerimaan',
        'daftar', 
        'voucher', 
        'spp_rp', 
        'spp_keterangan',
        'kaos', 
        'kpk', 
        'sertifikat', 
        'stpb', 
        'tas', 
        'event', 
        'lain_lain',
        'total',
    ];

    protected $casts = [
        'tanggal_daftar'     => 'date',
        'tanggal_penerimaan' => 'date',
        'tgl_tahapan'        => 'date',
        'tgl_level'          => 'date',        // ← BARU
        'tahun'              => 'integer',
        'spp'                => 'integer',
        'spp_rp'             => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public static function currentAcademicYear(): string
    {
        $now   = now();
        $year  = (int) $now->format('Y');
        $start = $now->month >= 7 ? $year : $year - 1;
        return "{$start}/" . ($start + 1);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'bg-warning text-dark',
            'verified'  => 'bg-primary text-white',
            'accepted'  => 'bg-success text-white',
            'rejected'  => 'bg-danger text-white',
            default     => 'bg-secondary',
        };
    }

    protected static function booted()
    {
        static::addGlobalScope('unit', function (Builder $builder) {
            if (!Auth::check()) return;

            $user = Auth::user();

            if ($user->is_admin ?? false || in_array($user->role ?? '', ['admin', 'superadmin'])) {
                return;
            }

            $userUnit     = trim($user->bimba_unit ?? '');
            $userNoCabang = trim($user->no_cabang ?? '');

            $builder->where(function ($q) use ($userUnit, $userNoCabang) {
                if ($userUnit) {
                    $q->where('bimba_unit', 'LIKE', "%{$userUnit}%");
                }
                if ($userNoCabang) {
                    $q->orWhere('no_cabang', $userNoCabang);
                }

                $q->orWhere('bimba_unit', 'LIKE', '%VILLA BEKASI INDAH 2%')
                  ->orWhere('no_cabang', '00340')
                  ->orWhere('bimba_unit', 'LIKE', '%GRIYA PESONA MADANI%')
                  ->orWhere('no_cabang', '05141')
                  ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA IV%')
                  ->orWhere('bimba_unit', 'LIKE', '%SAPTA TARUNA 4%')
                  ->orWhere('no_cabang', '01045');
            });
        });
    }

    public function muridTrial()
    {
        return $this->belongsTo(\App\Models\MuridTrial::class, 'murid_trial_id');
    }
}