<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class MuridTrial extends Model
{
    use HasFactory;

    protected $table = 'murid_trials';

    public const STATUS_DAFTAR_BARU = 'daftar_baru';
    public const STATUS_BARU        = 'baru';
    public const STATUS_AKTIF       = 'aktif';
    public const STATUS_LANJUT_DAFTAR = 'lanjut_daftar';
    public const STATUS_BATAL       = 'batal';

    protected $fillable = [
        'tgl_mulai', 'kelas', 'nama', 'tgl_lahir', 'usia', 'guru_trial', 'info',
        'orangtua', 'no_telp', 'alamat', 'rt', 'rw', 'waktu_submit', 'nim',
        'status_trial', 'promoted_at', 'bimba_unit', 'no_cabang',
        'tanggal_aktif', 'tanggal_trial_baru',
    ];

    protected $casts = [
        'tanggal_aktif'     => 'date',
        'tanggal_trial_baru'=> 'date',
        'tgl_mulai'         => 'date',
        'tgl_lahir'         => 'date',
        'waktu_submit'      => 'datetime',
        'promoted_at'       => 'datetime',
        'usia'              => 'integer',
    ];

    // ====================== BOOTED & CREATING ======================
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->waktu_submit) {
                $model->waktu_submit = now();
            }

            // DEFAULT STATUS YANG BENAR
            if (empty($model->status_trial)) {
                $model->status_trial = self::STATUS_DAFTAR_BARU;
            }

            if (empty($model->tanggal_trial_baru)) {
                $model->tanggal_trial_baru = now()->format('Y-m-d');
            }
        });

        static::addGlobalScope('unit', function (Builder $builder) {
            if (!Auth::check()) return;

            $user = Auth::user();
            if ($user->is_admin ?? false || in_array($user->role ?? '', ['admin', 'superadmin'])) {
                return;
            }

            $userUnit = trim($user->bimba_unit ?? '');
            $userNoCabang = trim($user->no_cabang ?? '');

            $builder->where(function ($q) use ($userUnit, $userNoCabang) {
                if ($userUnit) $q->where('bimba_unit', 'LIKE', "%{$userUnit}%");
                if ($userNoCabang) $q->orWhere('no_cabang', $userNoCabang);

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

    // ====================== RELASI ======================
    public function student()
    {
        return $this->hasOne(Student::class, 'murid_trial_id', 'id');
    }

    public function commitment()
    {
        return $this->hasOne(\App\Models\ParentCommitment::class, 'murid_trial_id', 'id');
    }

    // ====================== HELPER DELAY 24 JAM ======================
    public function isReadyToActivate(): bool
    {
        if (!in_array($this->status_trial, [self::STATUS_DAFTAR_BARU, self::STATUS_BARU])) {
            return false;
        }
        if (!$this->waktu_submit) return true;

        return $this->waktu_submit->lte(now()->subDay());
    }

    public function scopeBelum24Jam($query)
    {
        return $query->whereIn('status_trial', [self::STATUS_DAFTAR_BARU, self::STATUS_BARU])
                     ->where('waktu_submit', '>', now()->subDay());
    }

    public function scopeSudah24Jam($query)
    {
        return $query->whereIn('status_trial', [self::STATUS_DAFTAR_BARU, self::STATUS_BARU])
                     ->where('waktu_submit', '<=', now()->subDay());
    }

    // ====================== ACCESSOR ======================
    public function getStatusBadgeAttribute()
    {
        return match ($this->status_trial) {
            self::STATUS_AKTIF         => '<span class="badge bg-primary">Trial Aktif</span>',
            self::STATUS_LANJUT_DAFTAR => '<span class="badge bg-success">Lanjut Daftar</span>',
            self::STATUS_BATAL         => '<span class="badge bg-danger">Batal</span>',
            self::STATUS_DAFTAR_BARU,
            self::STATUS_BARU          => '<span class="badge bg-warning">Trial Baru</span>',
            default                    => '<span class="badge bg-secondary">Belum Diproses</span>',
        };
    }

    public function getFullAddressAttribute()
    {
        $alamatParts = [];

        $alamatUtama = $this->alamat ?? ($this->student?->alamat ?? null);
        if ($alamatUtama) $alamatParts[] = $alamatUtama;

        $rt = $this->rt ?? ($this->student?->rt ?? null);
        $rw = $this->rw ?? ($this->student?->rw ?? null);
        if ($rt || $rw) $alamatParts[] = "RT {$rt} / RW {$rw}";

        $kelurahan = $this->kelurahan ?? ($this->student?->kelurahan ?? null);
        $kecamatan = $this->kecamatan ?? ($this->student?->kecamatan ?? null);
        $kodya     = $this->kodya_kab ?? ($this->student?->kodya_kab ?? null);
        $provinsi  = $this->provinsi  ?? ($this->student?->provinsi ?? null);
        $kodepos   = $this->kode_pos  ?? ($this->student?->kode_pos ?? null);

        if ($kelurahan) $alamatParts[] = $kelurahan;
        if ($kecamatan) $alamatParts[] = $kecamatan;
        if ($kodya)     $alamatParts[] = $kodya;
        if ($provinsi)  $alamatParts[] = $provinsi;
        if ($kodepos)   $alamatParts[] = "Kode Pos {$kodepos}";

        return !empty($alamatParts) ? implode(', ', $alamatParts) : '-';
    }
}