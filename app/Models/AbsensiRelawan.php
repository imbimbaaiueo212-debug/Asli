<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AbsensiRelawan extends Model
{
    use HasFactory;

    protected $table = 'absensi_relawan';

    protected $fillable = [
        'nama_relawaan',
        'nik',
        'posisi',
        'status_relawaan',
        'departemen',
        'bimba_unit',
        'no_cabang',
        'tanggal',
        'absensi',
        'keterangan',
        'status', // kode pendek (Hadir, Sakit, Izin, Alpa, DT, PC, dll)
    ];

    protected $casts = [
        'tanggal'     => 'date:Y-m-d',   // format tanggal aman
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'jam_lembur'  => 'integer',      // jika ada field ini di tabel
    ];

    // ===================================================================
    // GLOBAL SCOPE: FILTER OTOMATIS BERDASARKAN UNIT USER YANG LOGIN
    // ===================================================================
   protected static function booted()
{
    static::addGlobalScope('unit', function (Builder $builder) {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->is_admin) {
                return;
            }

            if ($user->bimba_unit) {
                // ✅ FIX: kasih prefix tabel
                $builder->where('absensi_relawan.bimba_unit', $user->bimba_unit);
            } else {
                $builder->whereRaw('1 = 0');
            }
        }
    });

    static::created(fn($absensi) => $absensi->hitungPotonganTunjangan());
    static::updated(fn($absensi) => $absensi->hitungPotonganTunjangan());
    static::deleted(fn($absensi) => $absensi->tanggal ? $absensi->hitungPotonganTunjangan() : null);
}

    // ===================================================================
    // HITUNG ULANG POTONGAN TUNJANGAN (diperbaiki & lebih aman)
    // ===================================================================
    public function hitungPotonganTunjangan()
    {
        // Jika tidak ada tanggal → skip
        if (!$this->tanggal) {
            return $this;
        }

        $tanggal = Carbon::parse($this->tanggal);
        $bulan   = $tanggal->format('Y-m');

        // Ambil semua absensi bulan ini untuk relawan yang sama
        $absensiBulan = self::where('nama_relawaan', $this->nama_relawaan)
            ->whereYear('tanggal', $tanggal->year)
            ->whereMonth('tanggal', $tanggal->month)
            ->get();

        if ($absensiBulan->isEmpty()) {
            return $this;
        }

        $nik      = $this->nik ?? '';
        $jabatan  = $this->posisi ?? 'Relawan';

        // Cari tarif harian dari Skim (jika ada)
        $skim = \App\Models\Skim::whereRaw('LOWER(jabatan) = ?', [strtolower($jabatan)])->first();
        $tarifHarian = $skim ? ($skim->harian ?? 0) / 25 : 0;

        // Cari THP harian dari PendapatanTunjangan (jika ada)
        $pendapatan = \App\Models\PendapatanTunjangan::where('nama', $this->nama_relawaan)
            ->where('jabatan', $jabatan)
            ->first();
        $thpPerHari = $pendapatan ? ($pendapatan->thp ?? 0) / 25 : 0;

        // Inisialisasi potongan
        $potongan = [
            'nik'               => $nik,
            'nama'              => $this->nama_relawaan,
            'jabatan'           => $jabatan,
            'status'            => $this->status_relawaan,
            'departemen'        => $this->departemen,
            'bimba_unit'        => $this->bimba_unit,
            'no_cabang'         => $this->no_cabang,
            'bulan'             => $bulan,
            'sakit'             => 0,
            'izin'              => 0,
            'alpa'              => 0,
            'tidak_aktif'       => 0,
            'datang_terlambat'  => 0,
            'pulang_cepat'      => 0,
            'cuti'              => 0,
            'total'             => 0,
        ];

        // Hitung potongan berdasarkan absensi
        foreach ($absensiBulan as $absen) {
            $absensiText = trim(strtolower($absen->absensi ?? ''));

            switch (true) {
                case str_contains($absensiText, 'sakit dengan keterangan dokter'):
                    $potongan['sakit'] += $tarifHarian;
                    $absen->status = 'Sakit';
                    break;

                case str_contains($absensiText, 'sakit tanpa keterangan dokter'):
                case str_contains($absensiText, 'izin dengan form di acc'):
                case str_contains($absensiText, 'izin tanpa form di acc'):
                    $potongan['izin'] += $tarifHarian;
                    $absen->status = 'Izin';
                    break;

                case str_contains($absensiText, 'tidak masuk tanpa form'):
                case str_contains($absensiText, 'tidak mengisi absensi mingguan'):
                    $potongan['alpa'] += $thpPerHari;
                    $absen->status = 'Alpa';
                    break;

                case str_contains($absensiText, 'tidak aktif'):
                    $potongan['tidak_aktif'] += $thpPerHari;
                    $absen->status = 'Tidak Aktif';
                    break;

                case str_contains($absensiText, 'cuti'):
                    $potongan['cuti'] += $tarifHarian;
                    $absen->status = 'Cuti';
                    break;

                case str_contains($absensiText, 'datang terlambat'):
                    $absen->status = 'DT';
                    $potongan['datang_terlambat'] += $tarifHarian; // atau aturan khusus
                    break;

                case str_contains($absensiText, 'pulang cepat'):
                    $absen->status = 'PC';
                    $potongan['pulang_cepat'] += $tarifHarian; // atau aturan khusus
                    break;

                default:
                    $absen->status = 'Hadir';
                    break;
            }

            // Simpan status pendek tanpa trigger event lagi (hindari infinite loop)
            $absen->saveQuietly();
        }

        // Hitung total potongan
        $potongan['total'] = $potongan['sakit'] + $potongan['izin'] + $potongan['alpa'] +
                             $potongan['tidak_aktif'] + $potongan['cuti'] +
                             $potongan['datang_terlambat'] + $potongan['pulang_cepat'];

        // Simpan atau update ke tabel PotonganTunjangan
        \App\Models\PotonganTunjangan::updateOrCreate(
            ['nama' => $this->nama_relawaan, 'bulan' => $bulan],
            $potongan
        );

        return $this;
    }

    // ===================================================================
    // ACCESSORS FORMAT RUPIAH (diperbaiki & lebih aman)
    // ===================================================================
    public function getSakitRpAttribute()
    {
        return 'Rp ' . number_format($this->sakit ?? 0, 0, ',', '.');
    }

    public function getIzinRpAttribute()
    {
        return 'Rp ' . number_format($this->izin ?? 0, 0, ',', '.');
    }

    public function getAlpaRpAttribute()
    {
        return 'Rp ' . number_format($this->alpa ?? 0, 0, ',', '.');
    }

    public function getTotalRpAttribute()
    {
        return 'Rp ' . number_format($this->total ?? 0, 0, ',', '.');
    }

    // ===================================================================
    // SCOPE QUERY TAMBAHAN (opsional, bisa dipakai di controller)
    // ===================================================================
    public function scopeForUserUnit(Builder $query): void
    {
        if (Auth::check() && !Auth::user()->is_admin && Auth::user()->bimba_unit) {
            $query->where('bimba_unit', Auth::user()->bimba_unit);
        }
    }

    // ===================================================================
    // MUTATOR OTOMATIS (opsional: simpan status pendek saat simpan absensi panjang)
    // ===================================================================
    public function setAbsensiAttribute($value)
    {
        $this->attributes['absensi'] = $value;
        $this->attributes['status']  = $this->mapAbsensiToStatus($value);
    }

    private function mapAbsensiToStatus($absensi)
    {
        $absensi = trim(strtolower($absensi));
        return match (true) {
            str_contains($absensi, 'sakit dengan keterangan dokter') => 'Sakit',
            str_contains($absensi, 'sakit tanpa')                 => 'Izin',
            str_contains($absensi, 'izin dengan')                 => 'Izin',
            str_contains($absensi, 'izin tanpa')                  => 'Izin',
            str_contains($absensi, 'tidak masuk tanpa')           => 'Alpa',
            str_contains($absensi, 'tidak mengisi')               => 'Alpa',
            str_contains($absensi, 'tidak aktif')                 => 'Tidak Aktif',
            str_contains($absensi, 'cuti')                        => 'Cuti',
            str_contains($absensi, 'datang terlambat')            => 'DT',
            str_contains($absensi, 'pulang cepat')                => 'PC',
            default                                               => 'Hadir',
        };
    }
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'nik', 'nik');
    }
    public function volunteer()
    {
        return $this->hasOne(AbsensiVolunteer::class, 'nik', 'nik')
            ->whereColumn('absensi_volunteer.tanggal', 'absensi_relawan.tanggal');
    }
}