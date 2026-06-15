<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParentCommitment extends Model
{
    use HasFactory;

    protected $fillable = [
        'murid_trial_id','student_id',
        'parent_name','child_name','phone','address',
        'agreed','signed_at',
    ];

    protected $casts = [
        'agreed'   => 'boolean',
        'signed_at'=> 'datetime',
    ];

    public function muridTrial() { return $this->belongsTo(MuridTrial::class); }
    public function student()    { return $this->belongsTo(Student::class); }
}
