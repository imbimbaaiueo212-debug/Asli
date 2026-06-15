<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AktifScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // HANYA berlaku untuk Model Profile
        if ($model instanceof \App\Models\Profile) {
            $builder->whereNotIn('status_karyawan', ['Resign', 'Non-Aktif']);
        }
        // Model lain (JadwalDetail, RekapAbsensi, dll) diabaikan
    }
}