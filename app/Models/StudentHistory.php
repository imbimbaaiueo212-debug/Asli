<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentHistory extends Model
{
    protected $fillable = [
        'student_id', 'user_id', 'diff', 'ip', 'user_agent',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}