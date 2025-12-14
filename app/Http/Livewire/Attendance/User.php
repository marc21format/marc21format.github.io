<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use App\Models\User as UserModel;
use App\Models\CommitteeMember;
use App\Models\Attendance;
use App\Models\ReviewSeason;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class User extends Component
{
    public $user;
    public $fullName;
    public $committees = [];
    public $attendanceRecords = [];

    // View mode toggle
    public $monthlyView = false;

    // Session filter (am/pm)
    public $session = 'am';

    // Calendar state for daily view
    public $calendarYear;
    public $calendarMonth;
    public $selectedDate;

    // Matrix month for monthly view
    public $matrixYear;
    public $matrixMonth;

    // Weekend dates for monthly matrix
    public $weekendDates = [];

    // Editable attendance tracking
    public $editingRow = []; // [user_id => true] - tracks which rows are in edit mode
    public $editingAttendance = [];
    public $pendingChanges = [];
    public $showConfirmModal = false;
    public $confirmModalData = [];
    public $showSelfEditError = false; // Error modal for self-edit attempts
    public $showOutsideSeasonError = false; // Error modal for edits outside review season
    public $outsideSeasonErrorData = []; // Data for outside season error

    public function mount($userId)
    {
        $this->user = UserModel::with([
            'userProfile',
            'professionalCredentials.prefix',
            'professionalCredentials.suffix',
            'fceerProfile.group'
        ])->findOrFail($userId);

        // Build full name with titles
        $this->fullName = $this->buildFullName();

        // Get committees if volunteer (role_id 1, 2, or 3)
        if (in_array($this->user->role_id, [1, 2, 3])) {
            $this->committees = CommitteeMember::with(['committee', 'position'])
                ->where('user_id', $this->user->id)
                ->get();
        }

        // Initialize calendar state
        $now = Carbon::now();
        $this->calendarYear = $now->year;
        $this->calendarMonth = $now->month;
        $this->selectedDate = $now->format('Y-m-d');
        $this->selectedDate = $now->format('Y-m-d');
        $this->matrixYear = $now->year;
        $this->matrixMonth = $now->month;

        $this->loadAttendanceRecords();
        $this->calculateWeekendDates();
    }

    protected function loadAttendanceRecords()
    {
        // Get attendance records
        $this->attendanceRecords = Attendance::where('user_id', $this->user->id)
            ->with('excuseLetter')
            ->orderBy('date', 'desc')
            ->get();
    }

    protected function calculateWeekendDates()
    {
        $this->weekendDates = [];
        $daysInMonth = Carbon::create($this->matrixYear, $this->matrixMonth, 1)->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = Carbon::create($this->matrixYear, $this->matrixMonth, $d);
            // Saturday (6) and Sunday (0)
            if (in_array($dt->dayOfWeek, [0, 6])) {
                $this->weekendDates[] = $dt->format('Y-m-d');
            }
        }
    }

    public function toggleMonthlyView()
    {
        $this->monthlyView = !$this->monthlyView;
    }

    public function setSession($session)
    {
        $this->session = $session;
    }

    public function setDate($date)
    {
        $this->selectedDate = $date;
        $parsed = Carbon::parse($date);
        $this->calendarYear = $parsed->year;
        $this->calendarMonth = $parsed->month;
    }

    public function prevCalendarMonth()
    {
        $dt = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarYear = $dt->year;
        $this->calendarMonth = $dt->month;
        $this->selectedDate = $dt->format('Y-m-d');
    }

    public function nextCalendarMonth()
    {
        $dt = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarYear = $dt->year;
        $this->calendarMonth = $dt->month;
        $this->selectedDate = $dt->format('Y-m-d');
    }

    public function prevMatrixMonth()
    {
        $dt = Carbon::create($this->matrixYear, $this->matrixMonth, 1)->subMonth();
        $this->matrixYear = $dt->year;
        $this->matrixMonth = $dt->month;
        $this->calculateWeekendDates();
    }

    public function nextMatrixMonth()
    {
        $dt = Carbon::create($this->matrixYear, $this->matrixMonth, 1)->addMonth();
        $this->matrixYear = $dt->year;
        $this->matrixMonth = $dt->month;
        $this->calculateWeekendDates();
    }

    /**
     * Close outside season error modal
     */
    public function closeOutsideSeasonError()
    {
        $this->showOutsideSeasonError = false;
        $this->outsideSeasonErrorData = [];
    }

    /**
     * Check if a date is within the review season
     */
    protected function isDateWithinReviewSeason($date): bool
    {
        $reviewSeason = ReviewSeason::getActive();
        if (!$reviewSeason) {
            return true; // No review season set, allow all dates
        }
        return $reviewSeason->isDateWithinSeason($date);
    }

    /**
     * Start editing - show editable fields
     * Allows starting edit for self (error shown when trying to save)
     */
    public function startEditing()
    {
        $this->editingRow[$this->user->id] = true;
    }

    /**
     * Cancel editing
     */
    public function cancelEditing()
    {
        unset($this->editingRow[$this->user->id]);
        $this->editingAttendance = [];
        $this->pendingChanges = [];
    }

    /**
     * Close self-edit error modal
     */
    public function closeSelfEditError()
    {
        $this->showSelfEditError = false;
    }

    /**
     * Update status for a specific attendance record (for students daily view)
     * Allows tracking changes for self (error shown when trying to save)
     * Can create new records when attendanceId is 0
     */
    public function updateAttendanceStatus($attendanceId, $newStatus, $date, $session = 'am')
    {
        // Use a composite key for new records (when attendanceId is 0)
        $key = $attendanceId ?: "new_{$this->user->id}_{$date}_{$session}";
        
        $this->editingAttendance[$key] = [
            'attendance_id' => $attendanceId,
            'status' => $newStatus,
            'user_id' => $this->user->id,
            'full_name' => $this->fullName,
            'date' => $date,
            'session' => $session,
        ];
        $this->pendingChanges[$key] = true;
    }

    /**
     * Update time for a specific attendance record (for students)
     * Used in daily and monthly views
     */
    public function updateStudentTime($attendanceId, $newTime, $date, $session = 'am')
    {
        // Use a composite key for new records (when attendanceId is 0)
        $key = $attendanceId ?: "new_{$this->user->id}_{$date}_{$session}";
        
        if (!isset($this->editingAttendance[$key])) {
            $this->editingAttendance[$key] = [
                'attendance_id' => $attendanceId,
                'user_id' => $this->user->id,
                'full_name' => $this->fullName,
                'date' => $date,
                'session' => $session,
            ];
        }
        
        $this->editingAttendance[$key]['student_time'] = $newTime;
        $this->pendingChanges[$key] = true;
    }

    /**
     * Update status for a student attendance record (monthly view)
     */
    public function updateStudentStatus($attendanceId, $newStatus, $date, $session = 'am')
    {
        $key = $attendanceId ?: "new_{$this->user->id}_{$date}_{$session}";
        
        if (!isset($this->editingAttendance[$key])) {
            $this->editingAttendance[$key] = [
                'attendance_id' => $attendanceId,
                'user_id' => $this->user->id,
                'full_name' => $this->fullName,
                'date' => $date,
                'session' => $session,
            ];
        }
        
        $this->editingAttendance[$key]['status'] = $newStatus;
        $this->pendingChanges[$key] = true;
    }

    /**
     * Update time for a specific attendance record (for volunteers)
     * Allows tracking changes for self (error shown when trying to save)
     */
    public function updateAttendanceTime($attendanceId, $field, $newTime, $date)
    {
        $key = $attendanceId ?: "new_{$this->user->id}_{$date}_{$field}";
        
        if (!isset($this->editingAttendance[$key])) {
            $this->editingAttendance[$key] = [
                'attendance_id' => $attendanceId,
                'user_id' => $this->user->id,
                'full_name' => $this->fullName,
                'date' => $date,
                'time_in' => null,
                'time_out' => null,
            ];
        }
        
        $this->editingAttendance[$key][$field] = $newTime;
        $this->pendingChanges[$key] = true;
    }

    /**
     * Prepare confirmation modal for saving changes
     * If self-edit, show error modal instead
     */
    public function prepareConfirmSave()
    {
        // Check for self-edit - show error if trying to save own attendance
        if (Auth::id() == $this->user->id) {
            $this->showSelfEditError = true;
            return;
        }
        
        if (empty($this->editingAttendance)) {
            return;
        }

        // Check if any date is outside the review season
        $reviewSeason = ReviewSeason::getActive();
        if ($reviewSeason) {
            foreach ($this->editingAttendance as $change) {
                $date = $change['date'] ?? null;
                if ($date && !$reviewSeason->isDateWithinSeason($date)) {
                    $this->outsideSeasonErrorData = [
                        'date' => Carbon::parse($date)->format('F j, Y'),
                        'range' => $reviewSeason->range_label,
                    ];
                    $this->showOutsideSeasonError = true;
                    return;
                }
            }
        }

        $dates = collect($this->editingAttendance)->pluck('date')->unique()->map(function($d) {
            return Carbon::parse($d)->format('F j, Y');
        })->values()->all();

        $this->confirmModalData = [
            'user_id' => $this->user->id,
            'full_name' => $this->fullName,
            'dates' => $dates,
            'changes' => $this->editingAttendance,
        ];
        $this->showConfirmModal = true;
    }

    /**
     * Confirm and save attendance changes
     */
    public function confirmSaveAttendance()
    {
        foreach ($this->editingAttendance as $key => $change) {
            // Handle student time changes
            if (isset($change['student_time'])) {
                $attendanceId = $change['attendance_id'] ?? null;
                $newTime = $change['student_time'];
                $date = $change['date'] ?? null;
                $session = $change['session'] ?? 'am';
                
                if ($attendanceId) {
                    // Update existing record
                    $attendance = Attendance::find($attendanceId);
                    if ($attendance) {
                        if ($newTime === '' || $newTime === null) {
                            // Clear time = absent
                            $attendance->attendance_time = null;
                            $attendance->student_status = null;
                        } else {
                            $attendance->attendance_time = $newTime . ':00';
                            // Auto-calculate status based on time
                            $timeIn = \Carbon\Carbon::parse($newTime);
                            $sessionLower = strtolower($attendance->session ?? 'am');
                            $isLate = false;
                            if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                                $isLate = true;
                            } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                                $isLate = true;
                            }
                            $attendance->student_status = $isLate ? 'Late' : 'On Time';
                        }
                        $attendance->updated_by = Auth::id();
                        $attendance->save();
                    }
                } else {
                    // Create new attendance record
                    if ($newTime && $newTime !== '') {
                        $timeIn = \Carbon\Carbon::parse($newTime);
                        $sessionLower = strtolower($session);
                        $isLate = false;
                        if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                            $isLate = true;
                        } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                            $isLate = true;
                        }
                        
                        Attendance::create([
                            'user_id' => $this->user->id,
                            'date' => $date,
                            'attendance_time' => $newTime . ':00',
                            'session' => $session,
                            'student_status' => $isLate ? 'Late' : 'On Time',
                            'recorded_by' => Auth::id(),
                        ]);
                    }
                }
            }
            
            // Handle status changes (students daily view)
            elseif (isset($change['status'])) {
                $attendanceId = $change['attendance_id'] ?? null;
                $newStatus = $change['status'];
                $date = $change['date'] ?? null;
                $session = $change['session'] ?? 'am';
                
                if ($attendanceId) {
                    // Update existing record
                    $attendance = Attendance::find($attendanceId);
                    if ($attendance) {
                        $statusMap = ['on time' => 'On Time', 'late' => 'Late', 'excused' => 'Excused', 'N/A' => 'N/A'];
                        if ($newStatus === 'absent' || $newStatus === 'N/A') {
                            $attendance->attendance_time = null;
                            $attendance->student_status = $newStatus === 'N/A' ? 'N/A' : null;
                        } elseif ($newStatus === 'excused') {
                            // Excused can be set without time in
                            $attendance->student_status = 'Excused';
                        } else {
                            // For 'on time' or 'late', require time in
                            if (!$attendance->attendance_time) {
                                $attendance->attendance_time = now()->format('H:i:s');
                            }
                            $attendance->student_status = $statusMap[$newStatus] ?? $newStatus;
                        }
                        $attendance->updated_by = Auth::id();
                        $attendance->save();
                    }
                } else {
                    // Create new attendance record - excused/N/A can have no time, others need time
                    if ($newStatus && $newStatus !== 'absent') {
                        $statusMap = ['on time' => 'On Time', 'late' => 'Late', 'excused' => 'Excused', 'N/A' => 'N/A'];
                        Attendance::create([
                            'user_id' => $this->user->id,
                            'date' => $date,
                            'attendance_time' => ($newStatus === 'excused' || $newStatus === 'N/A') ? null : now()->format('H:i:s'),
                            'session' => $session,
                            'student_status' => $statusMap[$newStatus] ?? $newStatus,
                            'recorded_by' => Auth::id(),
                        ]);
                    }
                }
            }
            
            // Handle time changes (volunteers)
            elseif (isset($change['time_in']) || isset($change['time_out'])) {
                $userId = $this->user->id;
                $date = $change['date'] ?? null;
                
                if (!empty($change['time_in'])) {
                    $timeInRecord = Attendance::where('user_id', $userId)
                        ->whereDate('date', $date)
                        ->orderBy('attendance_time')
                        ->first();
                    
                    if ($timeInRecord) {
                        $timeInRecord->attendance_time = $change['time_in'] . ':00';
                        $timeInRecord->updated_by = Auth::id();
                        $timeInRecord->save();
                    } else {
                        Attendance::create([
                            'user_id' => $userId,
                            'date' => $date,
                            'attendance_time' => $change['time_in'] . ':00',
                            'session' => 'am',
                            'recorded_by' => Auth::id(),
                        ]);
                    }
                }
                
                if (!empty($change['time_out'])) {
                    $records = Attendance::where('user_id', $userId)
                        ->whereDate('date', $date)
                        ->orderBy('attendance_time')
                        ->get();
                    
                    if ($records->count() > 1) {
                        $timeOutRecord = $records->get(1);
                        $timeOutRecord->attendance_time = $change['time_out'] . ':00';
                        $timeOutRecord->updated_by = Auth::id();
                        $timeOutRecord->save();
                    } else {
                        Attendance::create([
                            'user_id' => $userId,
                            'date' => $date,
                            'attendance_time' => $change['time_out'] . ':00',
                            'session' => 'pm',
                            'recorded_by' => Auth::id(),
                        ]);
                    }
                }
            }
        }
        $this->editingAttendance = [];
        $this->pendingChanges = [];
        $this->editingRow = []; // Clear editing row state
        $this->showConfirmModal = false;
        $this->confirmModalData = [];
        $this->loadAttendanceRecords();
    }

    /**
     * Cancel confirmation modal
     */
    public function cancelConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmModalData = [];
    }

    /**
     * Reset pending changes
     */
    public function resetChanges()
    {
        $this->editingAttendance = [];
        $this->pendingChanges = [];
    }

    protected function buildFullName()
    {
        $parts = [];

        // Get the first professional credential for titles
        $credential = $this->user->professionalCredentials->first();

        // Add prefix title
        if ($credential && $credential->prefix) {
            $parts[] = $credential->prefix->title;
        }

        // Add first name
        if ($this->user->userProfile && $this->user->userProfile->f_name) {
            $parts[] = $this->user->userProfile->f_name;
        }

        // Add middle name
        if ($this->user->userProfile && $this->user->userProfile->m_name) {
            $parts[] = $this->user->userProfile->m_name;
        }

        // Add surname
        if ($this->user->userProfile && $this->user->userProfile->s_name) {
            $parts[] = $this->user->userProfile->s_name;
        }

        // Add suffix title
        if ($credential && $credential->suffix) {
            $parts[] = $credential->suffix->title;
        }

        return implode(' ', $parts);
    }

    public function redirectToCreateExcuse($attendanceId, $session)
    {
        if ($attendanceId) {
            return redirect()->route('student.excuse-letters.create', ['user_id' => $this->user->id, 'attendance_id' => $attendanceId]);
        } else {
            return redirect()->route('student.excuse-letters.create', ['user_id' => $this->user->id, 'date' => $this->selectedDate, 'session' => $session]);
        }
    }

    public function render()
    {
        $reviewSeason = ReviewSeason::getActive();
        return view('livewire.attendance.user', [
            'reviewSeason' => $reviewSeason,
        ]);
    }
}
