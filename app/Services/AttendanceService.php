<?php

namespace App\Services;

use App\Models\Attendance;

class AttendanceService
{
    /**
     * Minimal stub implementation used by controllers. Real business logic
     * can replace this later. Return an Attendance instance (not persisted)
     * to satisfy static analysis and basic runtime calls in development.
     *
     * @param int $userId
     * @param string|null $date
     * @param string|null $attendanceTime
     * @param string $session
     * @param int|null $actorId
     * @return Attendance
     */
    public function createOrUpdateAttendance(int $userId, ?string $date = null, ?string $attendanceTime = null, string $session = 'auto', ?int $actorId = null): Attendance
    {
        // Lightweight stub: return a new Attendance instance (not saved).
        $a = new Attendance();
        $a->user_id = $userId;
        $a->date = $date;
        $a->attendance_time = $attendanceTime;
        $a->session = $session === 'auto' ? null : $session;
        // Mark as recently created to match calling code expectations
        $a->wasRecentlyCreated = true;
        return $a;
    }
}
