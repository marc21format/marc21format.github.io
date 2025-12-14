<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\UserProfile;
use App\Models\Attendance;
use App\Models\ProfessionalCredential;
use App\Models\Room;
use App\Models\CommitteeMember;
use App\Models\HighschoolSubjectRecord;
use App\Models\StudentExcuseLetter;
use App\Models\HighschoolRecord;
use App\Models\UserRole;

/**
 * @method bool hasRole(string ...$roles)
 * @method bool isAdmin()
 * @method bool isExecutive()
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's role.
     */
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
    }

    /**
     * The user's profile (one-to-one).
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }

    /**
     * The user's FCEER profile (one-to-one).
     */
    public function fceerProfile()
    {
        return $this->hasOne(\App\Models\FceerProfile::class, 'user_id', 'id');
    }

    /**
     * All attendance records for the user (one-to-many).
     */
    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class, 'user_id', 'id');
    }

    /**
     * Professional credentials for the user (one-to-many).
     */
    public function professionalCredentials()
    {
        return $this->hasMany(ProfessionalCredential::class, 'user_id', 'id');
    }

    /**
     * Rooms where the user is adviser.
     */
    public function advisedRooms()
    {
        return $this->hasMany(Room::class, 'adviser_id', 'id');
    }

    /**
     * Rooms where the user is president.
     */
    public function presidingRooms()
    {
        return $this->hasMany(Room::class, 'president_id', 'id');
    }

    /**
     * Rooms where the user is secretary.
     */
    public function secretaryRooms()
    {
        return $this->hasMany(Room::class, 'secretary_id', 'id');
    }

    /**
     * Committee memberships for the user (one-to-many).
     */
    public function committeeMemberships()
    {
        return $this->hasMany(CommitteeMember::class, 'user_id', 'id');
    }

    /**
     * Student highschool subject pivot records.
     */
    public function HighschoolSubjectRecords()
    {
        return $this->hasMany(HighschoolSubjectRecord::class, 'user_id', 'id');
    }

    /**
     * Student excuse letters (one-to-many).
     */
    public function studentExcuseLetters()
    {
        return $this->hasMany(StudentExcuseLetter::class, 'user_id', 'id');
    }

    /**
     * Highschool records for the user (one-to-many).
     */
    public function highschoolRecords()
    {
        return $this->hasMany(HighschoolRecord::class, 'user_id', 'id');
    }

    /**
     * Educational records for the user (one-to-many).
     */
    public function educationalRecords()
    {
        return $this->hasMany(\App\Models\EducationalRecord::class, 'user_id', 'id');
    }

    /**
     * Subject teacher assignments for the user (one-to-many).
     */
    public function subjectTeachers()
    {
        return $this->hasMany(\App\Models\SubjectTeacher::class, 'user_id', 'id');
    }

    /**
     * Check if the user has one of the given role titles.
     * Accepts a list of role title strings.
     */
    public function hasRole(string ...$roles): bool
    {
        if (! $this->role) {
            return false;
        }

        return in_array($this->role->role_title, $roles, true);
    }

    /**
     * Convenience: is Admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin', 'Administrator');
    }

    /**
     * Convenience: is Executive.
     */
    public function isExecutive(): bool
    {
        // Some installs use the short title 'Exec' (see seeder). Accept both.
        return $this->hasRole('Executive', 'Exec');
    }

    /**
     * Convenience: is staff (admin or executive)
     */
    public function isStaff(): bool
    {
        return $this->isAdmin() || $this->isExecutive();
    }

    /**
     * Permissions helper: can this user edit the given target user's profile?
     * - Admins and Executives can edit any user's profile.
     * - Students and Instructors can only edit their own profile.
     */
    public function canEditUserProfile(self $target): bool
    {
        // Admins and Executives can edit any profile
        if ($this->isStaff()) {
            return true;
        }

        // Allow a user to edit their own profile regardless of role
        if ($this->id === $target->id) {
            return true;
        }

        return false;
    }
}
