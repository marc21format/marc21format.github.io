<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectTeacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subject_teachers';
    protected $primaryKey = 'teacher_id';

    protected $fillable = [
        'user_id',
        'subject_id',
        'subject_proficiency',
    ];

    /**
     * The user that is the teacher (belongs to User).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * The subject this teacher teaches.
     */
    public function subject()
    {
        return $this->belongsTo(VolunteerSubject::class, 'subject_id', 'subject_id');
    }
}
