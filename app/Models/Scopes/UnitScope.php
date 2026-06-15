<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope as EloquentScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class UnitScope implements EloquentScope
{
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        // Pastikan kolom ada
        if (!Schema::hasColumn($table, 'bimba_unit')) {
            return;
        }

        // Jika belum login → jangan filter
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        // =========================
        // ✅ DETEKSI ADMIN (AMAN)
        // =========================
        $isAdmin = in_array($user->role ?? null, ['admin', 'superadmin']);

        if ($isAdmin) {
            return; // admin lihat semua
        }

        // =========================
        // ✅ FILTER BERDASARKAN UNIT
        // =========================
        if (!empty($user->bimba_unit)) {

            $builder->where($table . '.bimba_unit', $user->bimba_unit);

            // Optional: filter cabang juga
            if (
                Schema::hasColumn($table, 'no_cabang') &&
                !empty($user->no_cabang)
            ) {
                $builder->where($table . '.no_cabang', $user->no_cabang);
            }

        } else {
            // Jika user tidak punya unit → blokir semua
            $builder->whereRaw('1 = 0');
        }
    }
}