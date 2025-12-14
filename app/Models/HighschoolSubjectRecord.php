<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HighschoolSubjectRecord extends Model
{
    use HasFactory;

    protected $table = 'highschool_subject_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'user_id',
        'highschoolsubject_id',
        'grade',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function subject()
    {
        return $this->belongsTo(HighschoolSubject::class, 'highschoolsubject_id', 'highschoolsubject_id');
    }
}
