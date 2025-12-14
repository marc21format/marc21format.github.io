<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FceerProfile;
use App\Models\Room;
use App\Models\Attendance;
use App\Models\ReviewSeason;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Students extends Component
{
    use WithPagination;
    public $date;
    public $session; // 'am'|'pm'|null
    public $committeeFilter = null;
    // Calendar view state for month navigation
    public $calendarYear;
    public $calendarMonth;
    // Monthly matrix view
    public $monthlyView = false;
    public $matrixYear;
    public $matrixMonth;
    
    // Weekly analytics navigation (for daily view)
    public $weeklyAnalyticsYear;
    public $weeklyAnalyticsMonth;

    // Editable attendance tracking
    public $editingRow = []; // [user_id => true] - tracks which rows are in edit mode
    public $editingAttendance = []; // [attendance_id => ['status' => 'on time'|'late'|'excused'|'absent']]
    public $pendingChanges = []; // Tracks what needs to be saved
    public $showConfirmModal = false;
    public $confirmModalData = []; // Data for confirmation modal
    public $showSelfEditError = false; // Error modal for self-edit attempts

    // Review Season settings (exec only)
    public $showReviewSeasonModal = false;
    public $reviewSeasonStartMonth;
    public $reviewSeasonStartYear;
    public $reviewSeasonEndMonth;
    public $reviewSeasonEndYear;
    public $showOutsideSeasonError = false; // Error modal for edits outside review season
    public $outsideSeasonErrorData = []; // Data for outside season error

    protected $listeners = ['attendanceUpdated' => '$refresh'];

    public function updatingDate()
    {
        // Keep for backward compat but avoid firing browser events here.
    }

    public function updatingSession()
    {
        // No direct browser event to avoid duplicate refreshes.
    }

    public function updatedSession($value)
    {
        // Normalize session immediately after user changes it and reset pagination.
        $this->session = $value ? strtolower($value) : null;
        $this->resetPage();
        Log::info('[Students] updatedSession', ['received' => $value, 'normalized' => $this->session]);
    }

    public function updatedDate($value)
    {
        $this->date = $value ?: null;
        $this->resetPage();
        Log::info('[Students] updatedDate', ['received' => $value, 'date' => $this->date]);
    }

    public function setSession($s)
    {
        $this->session = $s ? strtolower($s) : null;
        $this->resetPage();
        Log::info('[Students] setSession', ['requested' => $s, 'normalized' => $this->session]);
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    public function setDate($date = null)
    {
        $this->date = $date ?: ($this->date ?: null);
        if($date){
            try{
                $dt = \Carbon\Carbon::parse($date);
                $this->calendarYear = $dt->year;
                $this->calendarMonth = $dt->month;
            } catch (\Throwable $_) { }
        }
        $this->resetPage();
        Log::info('[Students] setDate', ['date' => $this->date, 'param' => $date]);
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    public function prevCalendarMonth()
    {
        try{
            $dt = \Carbon\Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
            $this->calendarYear = $dt->year;
            $this->calendarMonth = $dt->month;
        } catch (\Throwable $_) { }
    }

    public function nextCalendarMonth()
    {
        try{
            $dt = \Carbon\Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
            $this->calendarYear = $dt->year;
            $this->calendarMonth = $dt->month;
        } catch (\Throwable $_) { }
    }

    public function toggleMonthlyView()
    {
        $this->monthlyView = ! $this->monthlyView;
        if ($this->monthlyView) {
            try {
                $dt = \Carbon\Carbon::parse($this->date ?: now());
                $this->matrixYear = $dt->year;
                $this->matrixMonth = $dt->month;
            } catch (\Throwable $_) { }
        }
        $this->resetPage();
    }

    public function prevMatrixMonth()
    {
        try {
            $dt = \Carbon\Carbon::create($this->matrixYear, $this->matrixMonth, 1)->subMonth();
            $this->matrixYear = $dt->year;
            $this->matrixMonth = $dt->month;
        } catch (\Throwable $_) { }
        $this->resetPage();
    }

    public function nextMatrixMonth()
    {
        try {
            $dt = \Carbon\Carbon::create($this->matrixYear, $this->matrixMonth, 1)->addMonth();
            $this->matrixYear = $dt->year;
            $this->matrixMonth = $dt->month;
        } catch (\Throwable $_) { }
        $this->resetPage();
    }
    
    public function prevWeeklyAnalyticsMonth()
    {
        try {
            $dt = \Carbon\Carbon::create($this->weeklyAnalyticsYear, $this->weeklyAnalyticsMonth, 1)->subMonth();
            $this->weeklyAnalyticsYear = $dt->year;
            $this->weeklyAnalyticsMonth = $dt->month;
        } catch (\Throwable $_) { }
    }
    
    public function nextWeeklyAnalyticsMonth()
    {
        try {
            $dt = \Carbon\Carbon::create($this->weeklyAnalyticsYear, $this->weeklyAnalyticsMonth, 1)->addMonth();
            $this->weeklyAnalyticsYear = $dt->year;
            $this->weeklyAnalyticsMonth = $dt->month;
        } catch (\Throwable $_) { }
    }

    public function filterChanged()
    {
        $this->resetPage();
        try {
            $this->emit('attendanceUpdated');
        } catch (\Throwable $_) {
            // ignore — emit should be available
        }
    }

    /**
     * Apply deferred filters (called by the Apply button)
     * Consolidates deferred dropdown selections and triggers a single refresh.
     */
    public function applyFilters()
    {
        $this->date = $this->date ?: null;
        $this->session = $this->session ? strtolower($this->session) : null;
        $this->committeeFilter = $this->committeeFilter ?: null;

        $this->resetPage();
        Log::info('[Students] applyFilters', ['date' => $this->date, 'session' => $this->session, 'committee' => $this->committeeFilter]);
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    /**
     * Start editing a row - show editable fields
     * Allows starting edit for self (error shown when trying to save)
     */
    public function startEditing($userId)
    {
        $this->editingRow[$userId] = true;
    }

    /**
     * Cancel editing for a row
     */
    public function cancelEditing($userId)
    {
        unset($this->editingRow[$userId]);
        
        // Clear any pending changes for this user
        foreach ($this->editingAttendance as $key => $change) {
            if (($change['user_id'] ?? null) == $userId) {
                unset($this->editingAttendance[$key]);
                unset($this->pendingChanges[$key]);
            }
        }
    }

    /**
     * Close self-edit error modal
     */
    public function closeSelfEditError()
    {
        $this->showSelfEditError = false;
    }

    /**
     * Update status for a specific attendance record (tracks pending change)
     * Allows tracking changes for self (error shown when trying to save)
     * Can handle creating new records when attendanceId is 0
     */
    public function updateAttendanceStatus($attendanceId, $userId, $newStatus, $fullName, $date)
    {
        // Use a composite key for new records (when attendanceId is 0)
        $key = $attendanceId ?: "new_{$userId}_{$date}";
        
        $this->editingAttendance[$key] = [
            'attendance_id' => $attendanceId,
            'status' => $newStatus,
            'user_id' => $userId,
            'full_name' => $fullName,
            'date' => $date,
            'session' => $this->session ?? 'am',
        ];
        $this->pendingChanges[$key] = true;
    }

    /**
     * Update time for a specific attendance record (tracks pending change)
     * Can handle creating new records when attendanceId is 0
     */
    public function updateAttendanceTime($attendanceId, $userId, $newTime, $fullName, $date)
    {
        // Use a composite key for new records (when attendanceId is 0)
        $key = $attendanceId ?: "new_{$userId}_{$date}";
        
        if (!isset($this->editingAttendance[$key])) {
            $this->editingAttendance[$key] = [
                'attendance_id' => $attendanceId,
                'user_id' => $userId,
                'full_name' => $fullName,
                'date' => $date,
                'session' => $this->session ?? 'am',
            ];
        }
        
        $this->editingAttendance[$key]['time'] = $newTime;
        $this->pendingChanges[$key] = true;
    }

    /**
     * Prepare confirmation modal for saving changes
     * If self-edit, show error modal instead
     */
    public function prepareConfirmSave($userId, $fullName)
    {
        // Check for self-edit - show error if trying to save own attendance
        if (Auth::id() == $userId) {
            $this->showSelfEditError = true;
            return;
        }
        
        // Collect all pending changes for this user
        $userChanges = collect($this->editingAttendance)->filter(function($change) use ($userId) {
            return ($change['user_id'] ?? null) == $userId;
        });
        
        if ($userChanges->isEmpty()) {
            return;
        }

        // Check if any date is outside the review season
        $reviewSeason = ReviewSeason::getActive();
        if ($reviewSeason) {
            foreach ($userChanges as $change) {
                $date = $change['date'] ?? null;
                if ($date && !$reviewSeason->isDateWithinSeason($date)) {
                    $this->outsideSeasonErrorData = [
                        'date' => \Carbon\Carbon::parse($date)->format('F j, Y'),
                        'range' => $reviewSeason->range_label,
                    ];
                    $this->showOutsideSeasonError = true;
                    return;
                }
            }
        }

        $dates = $userChanges->pluck('date')->unique()->map(function($d) {
            return \Carbon\Carbon::parse($d)->format('F j, Y');
        })->values()->all();

        $this->confirmModalData = [
            'user_id' => $userId,
            'full_name' => $fullName,
            'dates' => $dates,
            'changes' => $userChanges->toArray(),
        ];
        $this->showConfirmModal = true;
    }

    /**
     * Confirm and save attendance changes
     */
    public function confirmSaveAttendance()
    {
        $userId = $this->confirmModalData['user_id'] ?? null;
        if (!$userId) {
            $this->showConfirmModal = false;
            return;
        }

        foreach ($this->editingAttendance as $key => $change) {
            if (($change['user_id'] ?? null) != $userId) continue;
            
            $attendanceId = $change['attendance_id'] ?? null;
            $newStatus = $change['status'] ?? null;
            $newTime = $change['time'] ?? null;
            $date = $change['date'] ?? null;
            $session = $change['session'] ?? 'am';
            
            if ($attendanceId) {
                // Update existing record
                $attendance = Attendance::find($attendanceId);
                if ($attendance) {
                    // Handle time change
                    if ($newTime !== null) {
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
                    }
                    
                    // Handle status change (only in daily view)
                    // Map lowercase values to database enum values
                    $statusMap = ['on time' => 'On Time', 'late' => 'Late', 'excused' => 'Excused', 'N/A' => 'N/A'];
                    if ($newStatus !== null) {
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
                    }
                    
                    $attendance->updated_by = Auth::id();
                    $attendance->save();
                }
            } else {
                // Create new attendance record
                if ($newTime && $newTime !== '') {
                    // Create with time
                    $timeIn = \Carbon\Carbon::parse($newTime);
                    $sessionLower = strtolower($session);
                    $isLate = false;
                    if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                        $isLate = true;
                    } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                        $isLate = true;
                    }
                    
                    Attendance::create([
                        'user_id' => $userId,
                        'date' => $date,
                        'attendance_time' => $newTime . ':00',
                        'session' => $session,
                        'student_status' => $isLate ? 'Late' : 'On Time',
                        'recorded_by' => Auth::id(),
                    ]);
                } elseif ($newStatus && $newStatus !== 'absent') {
                    // Create with status - excused/N/A can have no time, others need time
                    $statusMap = ['on time' => 'On Time', 'late' => 'Late', 'excused' => 'Excused', 'N/A' => 'N/A'];
                    Attendance::create([
                        'user_id' => $userId,
                        'date' => $date,
                        'attendance_time' => ($newStatus === 'excused' || $newStatus === 'N/A') ? null : now()->format('H:i:s'),
                        'session' => $session,
                        'student_status' => $statusMap[$newStatus] ?? $newStatus,
                        'recorded_by' => Auth::id(),
                    ]);
                }
            }
            
            // Clear from pending
            unset($this->editingAttendance[$key]);
            unset($this->pendingChanges[$key]);
        }

        // Clear editing row state
        unset($this->editingRow[$userId]);

        $this->showConfirmModal = false;
        $this->confirmModalData = [];
        
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
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
     * Open the review season setting modal (exec only)
     */
    public function openReviewSeasonModal()
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role_id != 1) {
            return; // Only exec can set review season
        }

        // Load current review season or default to current month
        $current = ReviewSeason::getActive();
        if ($current) {
            $this->reviewSeasonStartMonth = $current->start_month;
            $this->reviewSeasonStartYear = $current->start_year;
            $this->reviewSeasonEndMonth = $current->end_month;
            $this->reviewSeasonEndYear = $current->end_year;
        } else {
            $now = now();
            $this->reviewSeasonStartMonth = $now->month;
            $this->reviewSeasonStartYear = $now->year;
            $this->reviewSeasonEndMonth = $now->month;
            $this->reviewSeasonEndYear = $now->year;
        }

        $this->showReviewSeasonModal = true;
    }

    /**
     * Close the review season modal
     */
    public function closeReviewSeasonModal()
    {
        $this->showReviewSeasonModal = false;
    }

    /**
     * Confirm and save the review season range
     */
    public function confirmSetReviewSeason()
    {
        $loggedInUser = Auth::user();
        if (!$loggedInUser || $loggedInUser->role_id != 1) {
            return; // Only exec can set review season
        }

        // Deactivate any existing active review seasons
        ReviewSeason::where('is_active', true)->update(['is_active' => false]);

        // Create new review season
        ReviewSeason::create([
            'start_month' => $this->reviewSeasonStartMonth,
            'start_year' => $this->reviewSeasonStartYear,
            'end_month' => $this->reviewSeasonEndMonth,
            'end_year' => $this->reviewSeasonEndYear,
            'is_active' => true,
            'set_by_user_id' => $loggedInUser->id,
        ]);

        $this->showReviewSeasonModal = false;
        
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
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
     * Reset pending changes for a user
     */
    public function resetUserChanges($userId)
    {
        $this->editingAttendance = collect($this->editingAttendance)->filter(function($change) use ($userId) {
            return ($change['user_id'] ?? null) != $userId;
        })->toArray();
        
        $this->pendingChanges = collect($this->pendingChanges)->filter(function($val, $key) use ($userId) {
            return !isset($this->editingAttendance[$key]) || ($this->editingAttendance[$key]['user_id'] ?? null) != $userId;
        })->toArray();
    }

    public function mount($date = null, $session = null)
    {
        $this->date = $date ?? now()->toDateString();
        $this->session = $session ? strtolower($session) : null;
        $dt = \Carbon\Carbon::parse($this->date ?: now()->toDateString());
        $this->calendarYear = $dt->year;
        $this->calendarMonth = $dt->month;
        $this->matrixYear = $dt->year;
        $this->matrixMonth = $dt->month;
        $this->weeklyAnalyticsYear = $dt->year;
        $this->weeklyAnalyticsMonth = $dt->month;
    }

    public function render()
    {
        $date = $this->date;
        $session = $this->session;
        $sessionNormalized = $session ? strtolower($session) : null;

        // If monthly view is active, compute weekend dates for the matrix (Sat/Sun only)
        $weekendDates = [];
        if ($this->monthlyView) {
            try {
                $dt = \Carbon\Carbon::create($this->matrixYear, $this->matrixMonth, 1);
            } catch (\Throwable $_) { $dt = now(); }
            $daysInMonth = $dt->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = \Carbon\Carbon::create($dt->year, $dt->month, $d);
                // Only include weekends (Sat/Sun) - review sessions are on weekends
                if ($day->isSaturday() || $day->isSunday()) {
                    $weekendDates[] = $day->toDateString();
                }
            }
        }

        Log::info('[Students] render', ['date' => $date, 'session' => $session, 'sessionNormalized' => $sessionNormalized]);

        // Load FCEER profiles for users with Student role.
        $query = FceerProfile::whereHas('user.role', function ($qr) {
            $qr->where('role_title', 'Student');
        });

        if ($this->monthlyView && count($weekendDates)) {
            $profiles = $query->with(['user.attendanceRecords' => function ($q) use ($weekendDates) {
                $q->whereIn('date', $weekendDates)->with('excuseLetter');
            }])->get()->groupBy('student_group');
        } else {
            $profiles = $query->with(['user.attendanceRecords' => function ($q) use ($date, $sessionNormalized) {
                if ($date) $q->whereDate('date', $date);
                if ($sessionNormalized) $q->whereRaw('LOWER(session) = ?', [$sessionNormalized]);
                $q->with('excuseLetter');
            }])->get()->groupBy('student_group');
        }

        $roomIds = $profiles->keys()->filter()->all();
        $rooms = Room::whereIn('room_id', $roomIds)->get()->keyBy('room_id');

        // Options for the filter dropdown — use rooms as "groups" for students
        $allCommittees = Room::orderBy('group')->get();

        // Get active review season
        $reviewSeason = ReviewSeason::getActive();

        // ========== ANALYTICS CALCULATIONS ==========
        // Get all student user IDs (filtered by committee if applicable)
        $allProfiles = collect();
        foreach ($profiles as $rid => $plist) {
            foreach ($plist as $p) {
                $p->room_ref = $rooms[$rid] ?? null;
                $allProfiles->push($p);
            }
        }
        
        // Apply committee filter for analytics
        if (!empty($this->committeeFilter)) {
            $allProfiles = $allProfiles->filter(function ($p) {
                return isset($p->room_ref) && ($p->room_ref->room_id == $this->committeeFilter);
            });
        }
        
        $studentUserIds = $allProfiles->pluck('user_id')->filter()->unique()->values()->toArray();
        $totalStudents = count($studentUserIds);
        
        // Calculate analytics based on review season
        $analytics = [
            'week' => ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'present' => 0, 'total_days' => 0],
            'month' => ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'present' => 0, 'total_days' => 0],
            'overall' => ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'present' => 0, 'total_days' => 0],
        ];
        
        if ($totalStudents > 0 && $reviewSeason) {
            $now = \Carbon\Carbon::now();
            $sessionFilter = $sessionNormalized ?? 'am';
            
            // Get weekend dates for current week (within review season)
            $weekStart = $now->copy()->startOfWeek();
            $weekEnd = $now->copy()->endOfWeek();
            $weekWeekends = [];
            $current = $weekStart->copy();
            while ($current->lte($weekEnd)) {
                if (($current->isSaturday() || $current->isSunday()) && $reviewSeason->isDateWithinSeason($current)) {
                    $weekWeekends[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
            
            // Get weekend dates for current month (within review season)
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd = $now->copy()->endOfMonth();
            $monthWeekends = [];
            $current = $monthStart->copy();
            while ($current->lte($monthEnd)) {
                if (($current->isSaturday() || $current->isSunday()) && $reviewSeason->isDateWithinSeason($current)) {
                    $monthWeekends[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
            
            // Get all weekend dates in review season (overall)
            $seasonStart = $reviewSeason->start_date;
            $seasonEnd = $reviewSeason->end_date;
            $overallWeekends = [];
            $current = $seasonStart->copy();
            while ($current->lte($seasonEnd) && $current->lte($now)) {
                if ($current->isSaturday() || $current->isSunday()) {
                    $overallWeekends[] = $current->format('Y-m-d');
                }
                $current->addDay();
            }
            
            // Query attendance records for analytics
            $attendanceQuery = Attendance::whereIn('user_id', $studentUserIds)
                ->whereRaw('LOWER(session) = ?', [$sessionFilter]);
            
            // Week analytics
            if (count($weekWeekends) > 0) {
                $weekRecords = (clone $attendanceQuery)->whereIn('date', $weekWeekends)->get();
                $analytics['week'] = $this->calculateStudentAnalytics($weekRecords, $weekWeekends, $studentUserIds);
            }
            
            // Month analytics
            if (count($monthWeekends) > 0) {
                $monthRecords = (clone $attendanceQuery)->whereIn('date', $monthWeekends)->get();
                $analytics['month'] = $this->calculateStudentAnalytics($monthRecords, $monthWeekends, $studentUserIds);
            }
            
            // Overall analytics
            if (count($overallWeekends) > 0) {
                $overallRecords = (clone $attendanceQuery)->whereIn('date', $overallWeekends)->get();
                $analytics['overall'] = $this->calculateStudentAnalytics($overallRecords, $overallWeekends, $studentUserIds);
            }
        }
        
        // Daily analytics for daily view
        $dailyAnalytics = ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'total' => $totalStudents];
        if (!$this->monthlyView && $date && $totalStudents > 0) {
            $dailyRecords = Attendance::whereIn('user_id', $studentUserIds)
                ->whereDate('date', $date)
                ->when($sessionNormalized, function($q) use ($sessionNormalized) {
                    $q->whereRaw('LOWER(session) = ?', [$sessionNormalized]);
                })
                ->get();
            
            foreach ($dailyRecords as $rec) {
                $status = strtolower($rec->student_status ?? '');
                if ($status === 'excused' || $rec->letter_id) {
                    $dailyAnalytics['excused']++;
                } elseif ($rec->attendance_time) {
                    if ($status === 'late') {
                        $dailyAnalytics['late']++;
                    } elseif ($status === 'on time') {
                        $dailyAnalytics['on_time']++;
                    } else {
                        // Calculate based on time
                        $timeIn = \Carbon\Carbon::parse($rec->attendance_time);
                        $sessionLower = strtolower($rec->session ?? 'am');
                        $isLate = ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') ||
                                  ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15');
                        if ($isLate) {
                            $dailyAnalytics['late']++;
                        } else {
                            $dailyAnalytics['on_time']++;
                        }
                    }
                }
            }
            // Calculate absent as students without any record or with absent status
            $studentsWithRecords = $dailyRecords->pluck('user_id')->unique()->toArray();
            $dailyAnalytics['absent'] = $totalStudents - count($studentsWithRecords);
        }
        
        // ========== PER-MONTH ANALYTICS FOR MONTHLY VIEW ==========
        $monthlyAnalytics = [];
        if ($this->monthlyView && $reviewSeason && $totalStudents > 0) {
            $sessionFilter = $sessionNormalized ?? 'am';
            $current = $reviewSeason->start_date->copy()->startOfMonth();
            $seasonEnd = $reviewSeason->end_date;
            
            while ($current->lte($seasonEnd)) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();
                $monthWeekends = [];
                
                $temp = $monthStart->copy();
                while ($temp->lte($monthEnd)) {
                    // Include ALL weekends in the month (no date filtering)
                    if ($temp->isSaturday() || $temp->isSunday()) {
                        $monthWeekends[] = $temp->format('Y-m-d');
                    }
                    $temp->addDay();
                }
                
                if (count($monthWeekends) > 0) {
                    $monthRecords = Attendance::whereIn('user_id', $studentUserIds)
                        ->whereRaw('LOWER(session) = ?', [$sessionFilter])
                        ->whereIn('date', $monthWeekends)
                        ->get();
                    
                    $monthStats = $this->calculateStudentAnalytics($monthRecords, $monthWeekends, $studentUserIds);
                    $monthStats['label'] = $current->format('F Y');
                    $monthlyAnalytics[] = $monthStats;
                }
                
                $current->addMonth();
            }
        }
        
        // ========== WEEKLY ANALYTICS FOR DAILY VIEW ==========
        // Each "week" is a Saturday-Sunday pair within the selected month
        $weeklyAnalytics = ['weeks' => [], 'currentWeek' => null, 'month_label' => '', 'year' => null, 'month' => null];
        if (!$this->monthlyView && $totalStudents > 0 && $reviewSeason) {
            // Use weeklyAnalyticsYear/Month for navigation, fallback to current date
            if (!$this->weeklyAnalyticsYear || !$this->weeklyAnalyticsMonth) {
                $dt = $date ? \Carbon\Carbon::parse($date) : now();
                $this->weeklyAnalyticsYear = $dt->year;
                $this->weeklyAnalyticsMonth = $dt->month;
            }
            
            $selectedMonth = \Carbon\Carbon::create($this->weeklyAnalyticsYear, $this->weeklyAnalyticsMonth, 1);
            $sessionFilter = $sessionNormalized ?? 'am';
            $monthLabel = $selectedMonth->format('F Y');
            $weeklyAnalytics['month_label'] = $monthLabel;
            $weeklyAnalytics['year'] = $this->weeklyAnalyticsYear;
            $weeklyAnalytics['month'] = $this->weeklyAnalyticsMonth;
            
            // Get all weekend dates in the selected month
            $monthStart = $selectedMonth->copy()->startOfMonth();
            $monthEnd = $selectedMonth->copy()->endOfMonth();
            
            $allWeekends = [];
            $temp = $monthStart->copy();
            while ($temp->lte($monthEnd)) {
                if ($temp->isSaturday() || $temp->isSunday()) {
                    $allWeekends[] = $temp->copy();
                }
                $temp->addDay();
            }
            
            // Group weekends into Saturday-Sunday pairs
            $weekNumber = 1;
            for ($i = 0; $i < count($allWeekends); $i++) {
                $saturday = null;
                $sunday = null;
                
                // Check if current date is Saturday
                if ($allWeekends[$i]->isSaturday()) {
                    $saturday = $allWeekends[$i];
                    // Check if next date is Sunday AND it's the very next day (tomorrow)
                    if ($i + 1 < count($allWeekends) && 
                        $allWeekends[$i + 1]->isSunday() && 
                        $allWeekends[$i + 1]->format('Y-m-d') === $saturday->copy()->addDay()->format('Y-m-d')) {
                        $sunday = $allWeekends[$i + 1];
                        $i++; // Skip the Sunday since we've paired it
                    }
                }
                // Check if current date is Sunday without a Saturday (orphaned Sunday)
                elseif ($allWeekends[$i]->isSunday()) {
                    $sunday = $allWeekends[$i];
                }
                
                // Create week entry if we have at least one day
                if ($saturday || $sunday) {
                    $weekDates = [];
                    if ($saturday) $weekDates[] = $saturday->format('Y-m-d');
                    if ($sunday) $weekDates[] = $sunday->format('Y-m-d');
                    
                    $weekRecords = Attendance::whereIn('user_id', $studentUserIds)
                        ->whereRaw('LOWER(session) = ?', [$sessionFilter])
                        ->whereIn('date', $weekDates)
                        ->get();
                    
                    $weekData = $this->calculateStudentAnalytics($weekRecords, $weekDates, $studentUserIds);
                    
                    // Create label
                    if ($saturday && $sunday) {
                        $weekData['label'] = $saturday->format('M j') . '-' . $sunday->format('j');
                    } elseif ($saturday) {
                        $weekData['label'] = $saturday->format('M j') . ' (Sat only)';
                    } else {
                        $weekData['label'] = $sunday->format('M j') . ' (Sun only)';
                    }
                    
                    $weekData['week_number'] = $weekNumber;
                    $weekData['dates'] = $weekDates;
                    
                    $weeklyAnalytics['weeks'][] = $weekData;
                    
                    // Check if selected date is in this week (only if date matches the analytics month)
                    if ($date) {
                        $selectedDate = \Carbon\Carbon::parse($date);
                        if ($selectedDate->year == $this->weeklyAnalyticsYear && 
                            $selectedDate->month == $this->weeklyAnalyticsMonth) {
                            if (($saturday && $selectedDate->isSameDay($saturday)) || 
                                ($sunday && $selectedDate->isSameDay($sunday))) {
                                $weeklyAnalytics['currentWeek'] = $weekNumber;
                                $weeklyAnalytics['current_week_label'] = $weekData['label'];
                            }
                        }
                    }
                    
                    $weekNumber++;
                }
            }
        }

        return view('livewire.attendance.students', [
            'profilesByRoom' => $profiles,
            'rooms' => $rooms,
            'actor' => Auth::user(),
            'date' => $date,
            'session' => $session,
            'monthlyView' => $this->monthlyView,
            'weekendDates' => $weekendDates,
            'allCommittees' => $allCommittees,
            'reviewSeason' => $reviewSeason,
            'analytics' => $analytics,
            'dailyAnalytics' => $dailyAnalytics,
            'totalStudents' => $totalStudents,
            'monthlyAnalytics' => $monthlyAnalytics,
            'weeklyAnalytics' => $weeklyAnalytics,
        ]);
    }
    
    /**
     * Calculate student analytics from attendance records
     */
    protected function calculateStudentAnalytics($records, $dates, $studentUserIds)
    {
        $result = ['on_time' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0, 'present' => 0, 'total_days' => count($dates)];
        $totalStudents = count($studentUserIds);
        
        // Group records by date
        $recordsByDate = $records->groupBy(function($r) {
            return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
        });
        
        foreach ($dates as $date) {
            $dayRecords = $recordsByDate[$date] ?? collect();
            $studentsWithValidStatus = []; // Only count students with valid attendance status
            
            foreach ($dayRecords as $rec) {
                $status = strtolower($rec->student_status ?? '');
                
                // Check excused first (highest priority)
                if ($status === 'excused' || $rec->letter_id) {
                    $result['excused']++;
                    $studentsWithValidStatus[$rec->user_id] = true;
                } elseif ($rec->attendance_time) {
                    // Has time - check if late or on time
                    if ($status === 'late') {
                        $result['late']++;
                    } elseif ($status === 'on time') {
                        $result['on_time']++;
                    } else {
                        // Calculate based on time
                        $timeIn = \Carbon\Carbon::parse($rec->attendance_time);
                        $sessionLower = strtolower($rec->session ?? 'am');
                        $isLate = ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') ||
                                  ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15');
                        if ($isLate) {
                            $result['late']++;
                        } else {
                            $result['on_time']++;
                        }
                    }
                    $studentsWithValidStatus[$rec->user_id] = true;
                } else {
                    // Has record but no time and not excused = absent
                    $result['absent']++;
                    $studentsWithValidStatus[$rec->user_id] = true;
                }
            }
            
            // Count absent (students with NO records at all for this date)
            $result['absent'] += $totalStudents - count($studentsWithValidStatus);
        }
        
        return $result;
    }
}
