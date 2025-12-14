<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VolunteerSubject extends Model
{
    use HasFactory;

    protected $table = 'volunteer_subjects';
    protected $primaryKey = 'subject_id';

    protected $fillable = [
        'subject_code',
        'subject_name',
    ];

    public function teachers()
    {
        return $this->hasMany(SubjectTeacher::class, 'subject_id', 'subject_id');
    }
}
