<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, Notifiable, CanResetPasswordTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'bimba_unit',
        'role',
        'no_cabang',        // ← Tambahkan ini
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            // Jika no_cabang berupa integer (biasanya kode cabang), cast ke integer
            // 'no_cabang' => 'integer',
        ];
    }

    // Method untuk cek apakah user termasuk admin/pusat
    public function isAdminUser(): bool
    {
        return in_array($this->role, ['admin', 'pusat', 'developer']);
    }

    // Accessor (opsional) kalau kamu mau pakai $user->is_admin
    public function getIsAdminAttribute(): bool
    {
        return in_array(strtolower($this->role ?? ''), [
            'admin',
            'pusat',
            'developer',
            'superadmin',
            'owner',
            'direktur',
        ]);
    }
}