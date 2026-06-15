<?php

namespace App\Models;

use App\Models\Scopes\UnitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherLama extends Model
{
    use HasFactory;

    protected $table = 'voucher_lama';

    protected $fillable = [
        'voucher',
        'no_voucher',
        'jumlah_voucher',
        'nominal',
        'tanggal',
        'tanggal_penyerahan',
        'status',
        'nim',
        'nama_murid',
        'orangtua',
        'telp_hp',
        'nim_murid_baru',
        'nama_murid_baru',
        'orangtua_murid_baru',
        'telp_hp_murid_baru',
        'source',
        'bukti_penyerahan_path',
        'bimba_unit',
        'no_cabang',
        'tipe_voucher',
        'is_independent',
    ];

    protected $casts = [
        'tanggal'            => 'date:Y-m-d',
        'tanggal_pemakaian'  => 'date:Y-m-d',
        'tanggal_penyerahan' => 'date:Y-m-d',
    ];

    /* =========================
     | RELATION
     ========================= */
    public function histori()
    {
        return $this->hasMany(\App\Models\VoucherHistori::class, 'voucher_lama_id');
    }

    /* =========================
     | ACCESSOR
     ========================= */
    public function getLastTanggalPemakaianAttribute()
    {
        if ($this->relationLoaded('histori') && $this->histori->isNotEmpty()) {
            return $this->histori->first()->tanggal_pemakaian;
        }

        if (!empty($this->tanggal_pemakaian)) {
            return $this->tanggal_pemakaian;
        }

        $h = $this->histori()->latest('tanggal_pemakaian')->first();
        return $h?->tanggal_pemakaian;
    }

    /* =========================
     | SYSTEM SYNC (BYPASS SCOPE)
     ========================= */
    public static function syncForStudent(\App\Models\Student $student): array
    {
        try {
            $nimKey     = (string) $student->nim;
            $nimNumeric = ltrim($nimKey, '0');

            // ambil telp
            $telpRaw = $student->hp_ayah
                ?? $student->hp_ibu
                ?? $student->no_telp
                ?? $student->telp_hp;

            if (!$telpRaw) {
                $bi = \App\Models\BukuInduk::where('nim', $student->nim)->first();
                $telpRaw = $bi?->no_telp_hp ?? $bi?->no_telp;
            }

            $telpVal = $telpRaw ? preg_replace('/\D+/', '', (string) $telpRaw) : null;
            if ($telpVal === '') $telpVal = null;

            $updateForNewStudent = [];
            $updateForHumas      = [];

            if (!empty($student->nama)) {
                $updateForNewStudent['nama_murid_baru'] = $student->nama;
                $updateForHumas['nama_murid']           = $student->nama;
            }

            if ($student->orangtua !== null) {
                $updateForNewStudent['orangtua_murid_baru'] = $student->orangtua;
                $updateForHumas['orangtua']                 = $student->orangtua;
            }

            if ($telpVal !== null) {
                $updateForNewStudent['telp_hp_murid_baru'] = $telpVal;
                $updateForHumas['telp_hp']                 = $telpVal;
            }

            $counts = ['murid_baru' => 0, 'humas' => 0, 'fallback' => 0];

            // 1️⃣ berdasarkan nim_murid_baru
            if ($updateForNewStudent) {
                $counts['murid_baru'] =
                    self::withoutGlobalScope(UnitScope::class)
                        ->where(function ($q) use ($nimKey, $nimNumeric) {
                            $q->where('nim_murid_baru', $nimKey)
                              ->orWhereRaw(
                                  "TRIM(LEADING '0' FROM COALESCE(nim_murid_baru,'')) = ?",
                                  [$nimNumeric]
                              );
                        })
                        ->update($updateForNewStudent);
            }

            // 2️⃣ berdasarkan nim (humas)
            if ($updateForHumas) {
                $counts['humas'] =
                    self::withoutGlobalScope(UnitScope::class)
                        ->where(function ($q) use ($nimKey, $nimNumeric) {
                            $q->where('nim', $nimKey)
                              ->orWhereRaw(
                                  "TRIM(LEADING '0' FROM COALESCE(nim,'')) = ?",
                                  [$nimNumeric]
                              );
                        })
                        ->update($updateForHumas);
            }

            // 3️⃣ fallback tanpa nim (nama humas)
            if ($updateForHumas && !empty($student->nama)) {
                $name = mb_strtolower(trim($student->nama));

                $counts['fallback'] =
                    self::withoutGlobalScope(UnitScope::class)
                        ->where(function ($q) {
                            $q->whereNull('nim')->orWhere('nim', '');
                        })
                        ->whereRaw(
                            "LOWER(TRIM(COALESCE(nama_murid,''))) = ?",
                            [$name]
                        )
                        ->update($updateForHumas);
            }

            \Log::info('VoucherLama::syncForStudent OK', [
                'nim'    => $nimKey,
                'counts' => $counts,
            ]);

            return $counts;

        } catch (\Throwable $e) {
            \Log::error('VoucherLama::syncForStudent ERROR', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);

            return ['murid_baru' => 0, 'humas' => 0, 'fallback' => 0];
        }
    }

    /* =========================
     | GLOBAL SCOPE (USER ONLY)
     ========================= */
    protected static function booted()
    {
        static::addGlobalScope(new UnitScope);
    }
}
