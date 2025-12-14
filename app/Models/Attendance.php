<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'attendance';
    protected $primaryKey = 'attendance_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'date',
        'attendance_time', // TIME column (no date component) — you used "time" type
        'session',         // 'am' | 'pm'
        'student_status',  // enum: 'on time', 'late', 'excused'
        'letter_id',
        'recorded_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'attendance_time' => 'string',
        'session' => 'string',
        'student_status' => 'string',
        'letter_id' => 'integer',
        'recorded_by' => 'integer',
        'updated_by' => 'integer',
    ];

    // Session constants
    public const SESSION_AM = 'am';
    public const SESSION_PM = 'pm';

    // Default lateness cutoff (HH:MM) — can be moved to config if desired
    public static $lateCutoff = '08:00';

    /**
     * Accessor for is_late convenience attribute
     */
    public function getIsLateAttribute(): bool
    {
        if (! $this->attendance_time) {
            return false;
        }
        $cutoff = Carbon::createFromFormat('H:i', static::$lateCutoff);
        // attendance_time stored as H:i:s string
        $time = Carbon::createFromFormat('H:i:s', (string) $this->attendance_time);
        return $time->greaterThan($cutoff);
    }

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function letter()
    {
        return $this->belongsTo(\App\Models\StudentExcuseLetter::class, 'letter_id', 'letter_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by', 'id');
    }

    public function excuseLetter()
    {
        return $this->belongsTo(\App\Models\StudentExcuseLetter::class, 'letter_id', 'letter_id');
    }

    /**
     * Idempotent: attach matching StudentExcuseLetter (if any) for this user/date.
     * Uses actual column name date_attendance in student_excuse_letters table.
     */
    public function attachLetterIfExists(): void
    {
        if ($this->letter_id) {
            return;
        }

        $letter = \App\Models\StudentExcuseLetter::where('user_id', $this->user_id)
            ->whereDate('date_attendance', $this->date)
            ->first();

        if ($letter) {
            $this->letter_id = $letter->letter_id;
            $this->save();
        }
    }
}
