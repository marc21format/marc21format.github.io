<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HighschoolSubject extends Model
{
    use HasFactory;

    protected $table = 'highschool_subjects';
    protected $primaryKey = 'highschoolsubject_id';

    protected $fillable = [
        'subject_name',
        'subject_subname',
        'subject_code',
    ];

    public function studentSubjects()
    {
        return $this->hasMany(HighschoolSubjectRecord::class, 'highschoolsubject_id', 'highschoolsubject_id');
    }
}
