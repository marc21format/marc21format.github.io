<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Committee;
use App\Models\Attendance;
use App\Models\ReviewSeason;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Volunteers extends Component
{
    use WithPagination;
    public $date;
    public $session; // 'am'|'pm'|null
    public $committeeFilter = null;
    public $positionFilter = null;
    // Calendar view state (month navigation)
    public $calendarYear;
    public $calendarMonth;
    public $monthlyView = false;
    public $matrixYear;
    public $matrixMonth;
    
    // Weekly analytics navigation (for daily view)
    public $weeklyAnalyticsYear;
    public $weeklyAnalyticsMonth;

    // Editable attendance tracking
    public $editingRow = []; // [user_id => true] - tracks which rows are in edit mode
    public $editingAttendance = []; // [attendance_id => ['time_in' => '08:00', 'time_out' => '12:00']]
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
        // Keep for backward compat but don't trigger browser events here.
        // Livewire will re-render after the property update; use updatedDate() for pagination reset.
    }

    public function updatingSession()
    {
        // No direct browser event to avoid firing duplicate refreshes.
    }

    public function updatedSession($value)
    {
        // Normalize session immediately after user changes it and reset pagination.
        $this->session = $value ? strtolower($value) : null;
        $this->resetPage();
        Log::info('[Volunteers] updatedSession', ['received' => $value, 'normalized' => $this->session]);
    }

    public function updatedDate($value)
    {
        $this->date = $value ?: null;
        $this->resetPage();
        Log::info('[Volunteers] updatedDate', ['received' => $value, 'date' => $this->date]);
    }

    public function setSession($s)
    {
        $this->session = $s ? strtolower($s) : null;
        $this->resetPage();
        Log::info('[Volunteers] setSession', ['requested' => $s, 'normalized' => $this->session]);
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    public function setDate($date = null)
    {
        // If a date parameter is provided (from calendar click), use it; otherwise use bound value
        $this->date = $date ?: ($this->date ?: null);
        if($date){
            try{
                $dt = \Carbon\Carbon::parse($date);
                $this->calendarYear = $dt->year;
                $this->calendarMonth = $dt->month;
            } catch (\Throwable $_) { }
        }
        $this->resetPage();
        Log::info('[Volunteers] setDate', ['date' => $this->date, 'param' => $date]);
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
        // Explicit handler invoked from the client when filters change.
        $this->resetPage();
        // Notify other Livewire components on the page to refresh using Livewire emit (no JS fallback).
        try {
            $this->emit('attendanceUpdated');
        } catch (\Throwable $_) {
            // ignore errors — emit should be available in Livewire components
        }
    }

    /**
     * Apply deferred filters (called by the Apply button)
     * This method consolidates changes made via deferred bindings and triggers a single refresh.
     */
    public function applyFilters()
    {
        // Normalize input values
        $this->date = $this->date ?: null;
        $this->session = $this->session ? strtolower($this->session) : null;
        $this->committeeFilter = $this->committeeFilter ?: null;
        $this->positionFilter = $this->positionFilter ?: null;

        $this->resetPage();
        Log::info('[Volunteers] applyFilters', ['date' => $this->date, 'session' => $this->session, 'committee' => $this->committeeFilter, 'position' => $this->positionFilter]);

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
     * Update time for a specific attendance record (tracks pending change)
     * Allows tracking changes for self (error shown when trying to save)
     * @param int $attendanceId - If 0, it's a new record to create
     * @param int $userId
     * @param string $field - 'time_in' or 'time_out'
     * @param string $newTime - Time value in HH:MM format
     * @param string $fullName
     * @param string $date
     */
    public function updateAttendanceTime($attendanceId, $userId, $field, $newTime, $fullName, $date)
    {
        $key = $attendanceId ?: "new_{$userId}_{$date}_{$field}";
        
        if (!isset($this->editingAttendance[$key])) {
            $this->editingAttendance[$key] = [
                'attendance_id' => $attendanceId,
                'user_id' => $userId,
                'full_name' => $fullName,
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
            $date = $change['date'] ?? null;
            
            // Handle time_in update (first record)
            if (!empty($change['time_in'])) {
                // Find or create time-in record
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
            
            // Handle time_out update (second record)
            if (!empty($change['time_out'])) {
                // Find time-out record (second record on same date)
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
                    // Create second record for time out
                    Attendance::create([
                        'user_id' => $userId,
                        'date' => $date,
                        'attendance_time' => $change['time_out'] . ':00',
                        'session' => 'pm',
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
        
        $this->pendingChanges = [];
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

        $weekendDates = [];
        if ($this->monthlyView) {
            try {
                $dt = \Carbon\Carbon::create($this->matrixYear, $this->matrixMonth, 1);
            } catch (\Throwable $_) {
                $dt = now();
            }
            $daysInMonth = $dt->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $day = \Carbon\Carbon::create($dt->year, $dt->month, $d);
                // Only include weekends (Sat/Sun) - review sessions are on weekends
                if ($day->isSaturday() || $day->isSunday()) {
                    $weekendDates[] = $day->toDateString();
                }
            }
        }

        Log::info('[Volunteers] render', ['date' => $date, 'session' => $session, 'sessionNormalized' => $sessionNormalized]);

        $committees = Committee::with(['members.user' => function ($q) use ($date, $sessionNormalized, $weekendDates) {
            $q->with(['attendanceRecords' => function ($qa) use ($date, $sessionNormalized, $weekendDates) {
                if (!empty($weekendDates)) {
                    $qa->whereIn('date', $weekendDates);
                } elseif ($date) {
                    $qa->whereDate('date', $date);
                }
                if ($sessionNormalized) {
                    $qa->whereRaw('LOWER(session) = ?', [$sessionNormalized]);
                }
            }]);
        }, 'members.position'])->get();

        $allCommittees = Committee::orderBy('committee_name')->get();
        $positions = \App\Models\Position::orderBy('position_name')->get();

        $members = collect();
        $userCommittees = [];
        $userPositions = [];
        $userRoles = [];
        foreach ($committees as $committee) {
            foreach ($committee->members as $member) {
                $member->committee_ref = $committee;
                $members->push($member);

                $uid = optional($member->user)->id ?? null;
                if ($uid) {
                    $userCommittees[$uid] = $userCommittees[$uid] ?? [];
                    $name = $committee->committee_name ?? ($committee->committee_id ?? null);
                    if ($name && !in_array($name, $userCommittees[$uid])) $userCommittees[$uid][] = $name;

                    $userPositions[$uid] = $userPositions[$uid] ?? [];
                    $posTitle = optional($member->position)->position_title ?? optional($member->position)->position_name ?? null;
                    if ($posTitle && !in_array($posTitle, $userPositions[$uid])) $userPositions[$uid][] = $posTitle;
                    $roleEntry = trim(($committee->committee_name ?? $committee->committee_id) . ($posTitle ? ', ' . $posTitle : ''));
                    if ($roleEntry && !in_array($roleEntry, $userRoles[$uid] ?? [])) {
                        $userRoles[$uid] = $userRoles[$uid] ?? [];
                        $userRoles[$uid][] = $roleEntry;
                    }
                }
            }
        }

        if (!empty($this->committeeFilter)) {
            $members = $members->filter(function ($m) {
                return isset($m->committee_id) && $m->committee_id == $this->committeeFilter;
            });
        }

        if (!empty($this->positionFilter)) {
            $members = $members->filter(function ($m) {
                return isset($m->position_id) && $m->position_id == $this->positionFilter;
            });
        }

        $members = $members->unique('user_id')->filter()->sortBy(function ($m) {
            return strtolower(optional($m->user)->name ?? '');
        })->values();

        $total = $members->count();
        $presentCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        foreach ($members as $member) {
            $att = optional($member->user)->attendanceRecords->first() ?? null;
            $status = 'absent';
            if ($att) {
                if ($att->attendance_time) {
                    if ($att->letter_id) {
                        $status = 'leave';
                    } else {
                        $status = 'present';
                    }
                } else {
                    $status = 'absent';
                }
            }
            if ($status === 'present') {
                $presentCount++;
            } elseif ($status === 'leave') {
                $leaveCount++;
            } else {
                $absentCount++;
            }
        }

        $selectedDate = $date ? \Carbon\Carbon::parse($date) : now();
        $selectedDateLabel = $selectedDate->format('F j, Y');
        $sessionLabel = $session ? strtoupper($session) : 'AM';
        $sessionText = "{$sessionLabel} Session";
        $groupLabel = 'All committees';
        if (!empty($this->committeeFilter)) {
            $match = collect($allCommittees)->firstWhere('committee_id', $this->committeeFilter);
            if ($match) {
                $groupLabel = $match->committee_name ?? ($match->committee_id ?? $this->committeeFilter);
            } else {
                $groupLabel = $this->committeeFilter;
            }
        }
        $matrixMonthLabel = \Carbon\Carbon::create($this->matrixYear ?? $this->calendarYear, $this->matrixMonth ?? $this->calendarMonth, 1)->format('F Y');
        $dailyPreviewText = "{$selectedDateLabel} ({$sessionText})";
        $monthlyMatrixHeader = "{$matrixMonthLabel} ({$sessionText})";

        // Get active review season
        $reviewSeason = ReviewSeason::getActive();
        
        // ========== ANALYTICS CALCULATIONS ==========
        $volunteerUserIds = $members->pluck('user_id')->filter()->unique()->values()->toArray();
        $totalVolunteers = count($volunteerUserIds);
        
        $analytics = [
            'week' => ['present' => 0, 'total_hours' => 0, 'total_days' => 0],
            'month' => ['present' => 0, 'total_hours' => 0, 'total_days' => 0],
            'overall' => ['present' => 0, 'total_hours' => 0, 'total_days' => 0],
        ];
        
        if ($totalVolunteers > 0 && $reviewSeason) {
            $now = \Carbon\Carbon::now();
            
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
            
            // Query attendance records for analytics (no session filter for volunteers - they track time in/out)
            $attendanceQuery = Attendance::whereIn('user_id', $volunteerUserIds);
            
            // Week analytics
            if (count($weekWeekends) > 0) {
                $weekRecords = (clone $attendanceQuery)->whereIn('date', $weekWeekends)->get();
                $analytics['week'] = $this->calculateVolunteerAnalytics($weekRecords, $weekWeekends, $volunteerUserIds);
            }
            
            // Month analytics
            if (count($monthWeekends) > 0) {
                $monthRecords = (clone $attendanceQuery)->whereIn('date', $monthWeekends)->get();
                $analytics['month'] = $this->calculateVolunteerAnalytics($monthRecords, $monthWeekends, $volunteerUserIds);
            }
            
            // Overall analytics
            if (count($overallWeekends) > 0) {
                $overallRecords = (clone $attendanceQuery)->whereIn('date', $overallWeekends)->get();
                $analytics['overall'] = $this->calculateVolunteerAnalytics($overallRecords, $overallWeekends, $volunteerUserIds);
            }
        }
        
        // Daily analytics for daily view - average time today
        $dailyAvgTime = '—';
        $dailyTotalMinutes = 0;
        $dailyVolunteersWithTime = 0;
        if (!$this->monthlyView && $date && $totalVolunteers > 0) {
            $dailyRecords = Attendance::whereIn('user_id', $volunteerUserIds)
                ->whereDate('date', $date)
                ->orderBy('user_id')
                ->orderBy('attendance_time')
                ->get()
                ->groupBy('user_id');
            
            foreach ($dailyRecords as $userId => $userRecords) {
                $sorted = $userRecords->sortBy('attendance_time')->values();
                if ($sorted->count() >= 2) {
                    $timeIn = \Carbon\Carbon::parse($sorted->first()->attendance_time);
                    $timeOut = \Carbon\Carbon::parse($sorted->get(1)->attendance_time);
                    $minutes = $timeIn->diffInMinutes($timeOut);
                    $dailyTotalMinutes += $minutes;
                    $dailyVolunteersWithTime++;
                }
            }
            
            if ($dailyVolunteersWithTime > 0) {
                $avgMinutes = round($dailyTotalMinutes / $dailyVolunteersWithTime);
                $hours = floor($avgMinutes / 60);
                $mins = $avgMinutes % 60;
                $dailyAvgTime = $hours . 'h ' . $mins . 'm';
            }
        }

        // ========== MONTHLY BREAKDOWN ANALYTICS ==========
        // Calculate per-month analytics for all months in review season
        $monthlyAnalytics = [];
        if ($this->monthlyView && $reviewSeason && $totalVolunteers > 0) {
            $seasonStart = $reviewSeason->start_date;
            $seasonEnd = $reviewSeason->end_date;
            
            // Get all months in the review season
            $current = $seasonStart->copy()->startOfMonth();
            $endMonth = $seasonEnd->copy()->endOfMonth();
            
            while ($current->lte($endMonth)) {
                $monthStart = $current->copy()->startOfMonth();
                $monthEnd = $current->copy()->endOfMonth();
                $monthLabel = $current->format('F Y');
                
                // Get weekend dates for this month within review season
                $monthWeekends = [];
                $date = $monthStart->copy();
                while ($date->lte($monthEnd)) {
                    if (($date->isSaturday() || $date->isSunday()) && $reviewSeason->isDateWithinSeason($date)) {
                        $monthWeekends[] = $date->format('Y-m-d');
                    }
                    $date->addDay();
                }
                
                // Calculate analytics for this month
                if (count($monthWeekends) > 0) {
                    $monthRecords = Attendance::whereIn('user_id', $volunteerUserIds)
                        ->whereIn('date', $monthWeekends)
                        ->get();
                    
                    $monthStats = $this->calculateVolunteerAnalytics($monthRecords, $monthWeekends, $volunteerUserIds);
                    $monthStats['label'] = $monthLabel;
                    $monthlyAnalytics[] = $monthStats;
                }
                
                $current->addMonth();
            }
        }
        
        // ========== WEEKLY BREAKDOWN ANALYTICS (for Daily View) ==========
        // Each "week" is a Saturday-Sunday pair within the selected month
        $weeklyAnalytics = ['weeks' => [], 'currentWeek' => null, 'month_label' => '', 'year' => null, 'month' => null];
        if (!$this->monthlyView && $reviewSeason && $totalVolunteers > 0) {
            // Use weeklyAnalyticsYear/Month for navigation, fallback to current date
            if (!$this->weeklyAnalyticsYear || !$this->weeklyAnalyticsMonth) {
                $dt = $date ? \Carbon\Carbon::parse($date) : now();
                $this->weeklyAnalyticsYear = $dt->year;
                $this->weeklyAnalyticsMonth = $dt->month;
            }
            
            $selectedMonth = \Carbon\Carbon::create($this->weeklyAnalyticsYear, $this->weeklyAnalyticsMonth, 1);
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
                    
                    $weekRecords = Attendance::whereIn('user_id', $volunteerUserIds)
                        ->whereIn('date', $weekDates)
                        ->get();
                    
                    $weekStats = $this->calculateVolunteerAnalytics($weekRecords, $weekDates, $volunteerUserIds);
                    
                    // Create label
                    if ($saturday && $sunday) {
                        $weekStats['label'] = $saturday->format('M j') . '-' . $sunday->format('j');
                    } elseif ($saturday) {
                        $weekStats['label'] = $saturday->format('M j') . ' (Sat only)';
                    } else {
                        $weekStats['label'] = $sunday->format('M j') . ' (Sun only)';
                    }
                    
                    $weekStats['week_number'] = $weekNumber;
                    $weekStats['dates'] = $weekDates;
                    
                    $weeklyAnalytics['weeks'][] = $weekStats;
                    
                    // Check if selected date is in this week (only if date matches the analytics month)
                    if ($date) {
                        $selectedDate = \Carbon\Carbon::parse($date);
                        if ($selectedDate->year == $this->weeklyAnalyticsYear && 
                            $selectedDate->month == $this->weeklyAnalyticsMonth) {
                            if (($saturday && $selectedDate->isSameDay($saturday)) || 
                                ($sunday && $selectedDate->isSameDay($sunday))) {
                                $weeklyAnalytics['currentWeek'] = $weekNumber;
                                $weeklyAnalytics['current_week_label'] = $weekStats['label'];
                            }
                        }
                    }
                    
                    $weekNumber++;
                }
            }
        }

        return view('livewire.attendance.volunteers', [
            'committees' => $committees,
            'actor' => Auth::user(),
            'date' => $date,
            'session' => $session,
            'monthlyView' => $this->monthlyView,
            'weekendDates' => $weekendDates,
            'allCommittees' => $allCommittees,
            'positions' => $positions,
            'members' => $members,
            'userCommittees' => $userCommittees,
            'userPositions' => $userPositions,
            'userRoles' => $userRoles,
            'total' => $total,
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'dailyPreviewText' => $dailyPreviewText,
            'monthlyMatrixHeader' => $monthlyMatrixHeader,
            'matrixMonthLabel' => $matrixMonthLabel,
            'reviewSeason' => $reviewSeason,
            'analytics' => $analytics,
            'monthlyAnalytics' => $monthlyAnalytics,
            'weeklyAnalytics' => $weeklyAnalytics,
            'dailyAvgTime' => $dailyAvgTime,
            'totalVolunteers' => $totalVolunteers,
        ]);
    }
    
    /**
     * Calculate volunteer analytics from attendance records (roster-based)
     */
    protected function calculateVolunteerAnalytics($records, $dates, $volunteerUserIds)
    {
        $result = ['present' => 0, 'absent' => 0, 'total_hours' => 0, 'total_days' => count($dates)];
        $totalVolunteers = count($volunteerUserIds);
        $totalMinutes = 0;
        
        // Group records by date
        $recordsByDate = $records->groupBy(function($r) {
            return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
        });
        
        // Process each date to calculate present/absent per day (roster-based)
        foreach ($dates as $date) {
            $dayRecords = $recordsByDate[$date] ?? collect();
            $volunteersWithValidTime = [];
            
            foreach ($dayRecords as $rec) {
                // Only count as present if they have attendance_time (time in)
                // Mark user as present (don't increment multiple times for same user/day)
                if ($rec->attendance_time) {
                    $volunteersWithValidTime[$rec->user_id] = true;
                }
            }
            
            // Count unique present volunteers for this day
            $result['present'] += count($volunteersWithValidTime);
            
            // Count absent (volunteers with NO time records for this date)
            $result['absent'] += $totalVolunteers - count($volunteersWithValidTime);
        }
        
        // Calculate total hours worked
        $recordsByDateUser = $records->groupBy(function($r) {
            return \Carbon\Carbon::parse($r->date)->format('Y-m-d') . '_' . $r->user_id;
        });
        
        foreach ($recordsByDateUser as $key => $userDayRecords) {
            // Calculate hours if has time in and time out
            $sorted = $userDayRecords->sortBy('attendance_time')->values();
            if ($sorted->count() >= 2 && $sorted->first()->attendance_time) {
                $timeIn = \Carbon\Carbon::parse($sorted->first()->attendance_time);
                $timeOut = \Carbon\Carbon::parse($sorted->get(1)->attendance_time);
                $totalMinutes += $timeIn->diffInMinutes($timeOut);
            }
        }
        
        $result['total_hours'] = round($totalMinutes / 60, 1);
        
        return $result;
    }
}
