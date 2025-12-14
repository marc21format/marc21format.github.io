<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Note on permissions:
 * - Only Admins and Executives can modify any user's profile or dropdown options.
 * - Students and Instructors can only edit their own user profiles.
 */
class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_profiles';
    protected $primaryKey = 'userprofile_id';

    protected $fillable = [
        'user_id',
        'f_name',
        'm_name',
        's_name',
        'lived_name',
        'generational_suffix',
        'phone_number',
        'birthday',
        'sex',
        'address_id',
    ];

    protected $casts = [
        'student_group' => 'integer',
        'batch_id' => 'integer',
    ];

    /**
     * The user that owns this profile (inverse one-to-one).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * The address for this profile.
     */
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id', 'address_id');
    }

    /**
     * The attendance status for this profile.
     */
    public function attendanceStatus()
    {
        return $this->belongsTo(UserAttendanceStatus::class, 'status_id', 'status_id');
    }

    /**
     * The room (student group) this profile belongs to.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'student_group', 'room_id');
    }

    /**
     * The batch this profile belongs to (fceer_batch table).
     */
    public function batch()
    {
        return $this->belongsTo(FceerBatch::class, 'batch_id', 'batch_id');
    }
}
