<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UnitScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $table = $model->getTable();

        if (!Schema::hasColumn($table, 'bimba_unit')) {
            return;
        }

        if (!Auth::check()) {
            Log::warning("UnitScope: User belum login, tidak ada filter");
            return;
        }

        $user = Auth::user();

        // Admin bebas
        if (in_array($user->role ?? null, ['admin', 'superadmin'])) {
            Log::info("UnitScope: Admin detected - no filter");
            return;
        }

        // ==================== FILTER UTAMA ====================
        if (!empty($user->bimba_unit)) {
            
            Log::info("UnitScope applied", [
                'table' => $table,
                'user_bimba_unit' => $user->bimba_unit,
                'user_no_cabang' => $user->no_cabang ?? null
            ]);

            $builder->where($table . '.bimba_unit', $user->bimba_unit);

            // Filter cabang jika ada
            if (Schema::hasColumn($table, 'no_cabang') && !empty($user->no_cabang)) {
                $builder->where($table . '.no_cabang', $user->no_cabang);
            }

        } else {
            // User tidak punya unit → blokir total
            Log::warning("UnitScope: User tidak punya bimba_unit, blocking all data");
            $builder->whereRaw('1 = 0');
        }
    }
}