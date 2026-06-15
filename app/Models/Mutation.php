<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    protected $fillable = [
        'student_id','tipe','nama','asal_unit','asal_kode',
        'tanggal_mutasi','alasan','keterangan','created_by','updated_by'
    ];

    protected $casts = [
        'tanggal_mutasi' => 'date',
    ];

    public function student(){ return $this->belongsTo(Student::class); }
    public function creator(){ return $this->belongsTo(User::class,'created_by'); }
    public function updater(){ return $this->belongsTo(User::class,'updated_by'); }
}
