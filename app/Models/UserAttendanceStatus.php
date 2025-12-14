<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAttendanceStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_attendance_status';
    protected $primaryKey = 'status_id';

    protected $fillable = [
        'status',
    ];

    /**
     * User profiles with this attendance status.
     */
    public function userProfiles()
    {
        return $this->hasMany(UserProfile::class, 'status_id', 'status_id');
    }

    /**
     * Attendance records with this status.
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'status_id', 'status_id');
    }
}
