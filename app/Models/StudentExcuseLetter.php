<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentExcuseLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'student_excuse_letters';
    protected $primaryKey = 'letter_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'attendance_id_am',
        'attendance_id_pm',
        'reason',           // your note/reason column
        'date_attendance',  // actual DB column name
        'status',
        'letter_link',      // actual DB column name for file link
    ];

    protected $casts = [
        'date_attendance' => 'date',
        'letter_link' => 'string',
        'status' => 'string',
        'attendance_id_am' => 'integer',
        'attendance_id_pm' => 'integer',
    ];

    protected static function booted()
    {
        static::created(function ($letter) {
            if ($letter->attendance_id_am) {
                Attendance::where('attendance_id', $letter->attendance_id_am)->update(['letter_id' => $letter->letter_id]);
            }
            if ($letter->attendance_id_pm) {
                Attendance::where('attendance_id', $letter->attendance_id_pm)->update(['letter_id' => $letter->letter_id]);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function attendance()
    {
        return $this->belongsTo(\App\Models\Attendance::class, 'attendance_id', 'attendance_id');
    }

    // Compatibility accessor: allow ->date in existing code
    public function getDateAttribute()
    {
        return $this->date_attendance;
    }

    // Compatibility accessor: allow ->linked_file_path in existing code
    public function getLinkedFilePathAttribute()
    {
        return $this->letter_link;
    }
}
