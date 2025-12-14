{{-- Volunteer Attendance - Matching Student Style --}}

<div class="attendance-page" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="attendance-wrapper">
        @php
            // stats
            $total = $members->count();
            $presentCount = 0;
            $absentCount = 0;
            $leaveCount = 0;
            foreach ($members as $m) {
                $u = $m->user ?? null;
                $att = $u ? ($u->attendanceRecords->first() ?? null) : null;
                $status = 'absent';
                if ($att) {
                    if ($att->attendance_time) {
                        if ($att->letter_id) {
                            $status = 'leave';
                        } elseif ($att->is_late) {
                            $status = 'present'; // Late counts as present
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

            // calendar calculations
            $calYear = $calendarYear ?: ($date ? \Carbon\Carbon::parse($date)->year : now()->year);
            $calMonth = $calendarMonth ?: ($date ? \Carbon\Carbon::parse($date)->month : now()->month);
            $daysInMonth = \Carbon\Carbon::create($calYear, $calMonth, 1)->daysInMonth;
            $firstDayOfWeek = \Carbon\Carbon::create($calYear, $calMonth, 1)->dayOfWeek;

            $selectedDate = $date ? \Carbon\Carbon::parse($date) : now();
            $selectedDateLabel = $selectedDate->format('F j, Y');
            
            $calMonthLabel = \Carbon\Carbon::create($calYear, $calMonth, 1)->format('F Y');
            $matrixMonthLabel = \Carbon\Carbon::create($matrixYear ?? $calYear, $matrixMonth ?? $calMonth, 1)->format('F Y');
            $dailyPreviewText = $selectedDateLabel;
            $monthlyMatrixHeader = $matrixMonthLabel;
        @endphp

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-5 pt-5 pb-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Volunteer Attendance</h1>
                        <p class="text-sm text-slate-500 mt-1">
                            @if(!empty($monthlyView) && $monthlyView)
                                {{ $monthlyMatrixHeader }}
                            @else
                                {{ $dailyPreviewText }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        {{-- Monthly/Daily Toggle --}}
                        @if(!empty($monthlyView) && $monthlyView)
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click="toggleMonthlyView">Switch to Daily view</button>
                        @else
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click="toggleMonthlyView">Switch to Monthly view</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="px-5 pb-4">
                <form wire:submit.prevent="applyFilters" class="flex items-end gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-[10px] uppercase tracking-widest text-slate-500">Committee</label>
                        <select wire:model.defer="committeeFilter" class="w-full appearance-none rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-slate-400 focus:outline-none" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">All committees</option>
                            @foreach($allCommittees as $cOpt)
                                <option value="{{ $cOpt->committee_id }}">{{ $cOpt->committee_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-[10px] uppercase tracking-widest text-slate-500">Position</label>
                        <select wire:model.defer="positionFilter" class="w-full appearance-none rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-slate-400 focus:outline-none" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">All positions</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->position_id }}">{{ $pos->position_name ?? $pos->position_title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1" style="width: 100px;">
                        <label class="text-[10px] uppercase tracking-widest text-slate-500">&nbsp;</label>
                        <button type="submit" class="w-full rounded-md bg-slate-400 hover:bg-slate-500 px-3 py-2 text-sm text-white font-medium transition">Filter</button>
                    </div>
                </form>

                {{-- Review Season Info & Set Button (Exec Only) --}}
                @if($actor && $actor->role_id == 1)
                    <div class="flex items-center justify-between mt-3 p-3 rounded-lg border border-indigo-200 bg-indigo-50">
                        <div class="flex items-center gap-3">
                            <i class="fa fa-calendar-check-o text-indigo-500"></i>
                            <div>
                                <span class="text-xs font-medium text-indigo-700 uppercase tracking-wider">Review Season:</span>
                                @if($reviewSeason)
                                    <span class="ml-2 text-sm font-semibold text-indigo-900">{{ $reviewSeason->range_label }}</span>
                                @else
                                    <span class="ml-2 text-sm text-indigo-600 italic">Not set</span>
                                @endif
                            </div>
                        </div>
                        <button 
                            type="button" 
                            wire:click="openReviewSeasonModal"
                            class="px-3 py-1.5 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition"
                        >
                            <i class="fa fa-cog mr-1"></i> Set Review Season
                        </button>
                    </div>
                @elseif($reviewSeason)
                    <div class="flex items-center gap-3 mt-3 p-3 rounded-lg border border-slate-200 bg-slate-50">
                        <i class="fa fa-calendar-check-o text-slate-400"></i>
                        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Review Season:</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $reviewSeason->range_label }}</span>
                    </div>
                @endif
            </div>

            @if(empty($monthlyView) || !$monthlyView)
                {{-- Daily View --}}
                @php
                    // Check if selected date is a weekend (Sat/Sun) and within review season
                    $selectedDateCarbon = \Carbon\Carbon::parse($date ?? now()->toDateString());
                    $isWeekend = $selectedDateCarbon->isSaturday() || $selectedDateCarbon->isSunday();
                    $isDateWithinSeason = !$reviewSeason || $reviewSeason->isDateWithinSeason($selectedDateCarbon);
                    
                    // If weekday OR outside review season, all statuses should be N/A
                    $showAsNA = !$isWeekend || !$isDateWithinSeason;
                @endphp
                <div class="px-5 pb-5">
                    @if($showAsNA)
                        {{-- Show notice when date is a weekday or outside review season --}}
                        <div class="mb-4 p-3 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-sm flex items-center gap-2">
                            <i class="fa fa-info-circle"></i>
                            @if(!$isWeekend)
                                <span>This date is a weekday. Review sessions are only on weekends (Saturday/Sunday).</span>
                            @else
                                <span>This date is outside the current review season ({{ $reviewSeason?->range_label ?? 'not set' }}).</span>
                            @endif
                        </div>
                    @endif
                    <div class="grid grid-cols-[1fr_300px] gap-5">
                        {{-- Left: Attendance Table --}}
                        <div class="rounded-lg border border-slate-200 bg-slate-50/50 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead>
                                        <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                            <th class="py-3 px-4">Volunteer #</th>
                                            <th class="py-3 px-4">Member</th>
                                            <th class="py-3 px-4">Time In</th>
                                            <th class="py-3 px-4">Time Out</th>
                                            <th class="py-3 px-4">Total Time</th>
                                            @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                <th class="py-3 px-4">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($members as $member)
                                            @php
                                                $u = $member->user ?? null;
                                                $uid = optional($u)->id ?? null;
                                                $att = $u ? ($u->attendanceRecords->first() ?? null) : null;
                                                $status = 'absent';
                                                
                                                // If weekday or outside review season, force N/A
                                                if ($showAsNA) {
                                                    $status = 'n/a';
                                                } elseif ($att) {
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
                                                $committeeText = $uid ? implode(', ', ($userCommittees[$uid] ?? [])) : ($member->committee_ref->committee_name ?? '');
                                                $positionText = $uid ? implode(', ', ($userPositions[$uid] ?? [])) : (optional($member->position)->position_title ?? optional($member->position)->position_name ?? '');
                                                $fullName = null;
                                                if ($u && isset($u->userProfile)) {
                                                    $parts = array_filter([
                                                        $u->userProfile->f_name ?? null,
                                                        $u->userProfile->m_name ?? null,
                                                        $u->userProfile->s_name ?? null,
                                                    ]);
                                                    $fullName = trim(implode(' ', $parts));
                                                }
                                                if (!$fullName) {
                                                    $fullName = $u->name ?? '';
                                                }
                                                $memberRoles = $uid ? ($userRoles[$uid] ?? []) : [];
                                                if (!$memberRoles && ($committeeText || $positionText)) {
                                                    $fallback = trim($committeeText ?: '');
                                                    if ($positionText) {
                                                        $fallback .= $fallback ? ', ' . $positionText : $positionText;
                                                    }
                                                    if ($fallback) {
                                                        $memberRoles[] = $fallback;
                                                    }
                                                }
                                                $volunteerNumber = optional($u?->fceerProfile)->volunteer_number ?? '';
                                                
                                                // Build full name with prefix/suffix for modal
                                                $fullNameWithTitles = $fullName;
                                                if ($u && isset($u->professionalCredentials)) {
                                                    $prefixes = $u->professionalCredentials->pluck('prefix.prefix_title')->filter()->implode(' ');
                                                    $suffixes = $u->professionalCredentials->pluck('suffix.suffix_title')->filter()->implode(', ');
                                                    if ($prefixes) $fullNameWithTitles = $prefixes . ' ' . $fullNameWithTitles;
                                                    if ($suffixes) $fullNameWithTitles .= ', ' . $suffixes;
                                                }
                                                
                                                $currentDate = $date ?? now()->toDateString();
                                                
                                                // Check if this row is in edit mode
                                                $isEditing = isset($editingRow[$uid]);
                                                
                                                // Check if there's a pending edit for this user
                                                $userPendingKey = collect($editingAttendance)->filter(function($change) use ($uid) {
                                                    return ($change['user_id'] ?? null) == $uid;
                                                })->keys()->first();
                                                $hasPendingEdit = !empty($userPendingKey);
                                                
                                                // Check if current user is the same as this row's user (prevent self-edit)
                                                $isSelf = (auth()->id() == $uid);
                                            @endphp
                                            <tr class="hover:bg-slate-50 transition {{ $isEditing ? 'bg-amber-50' : '' }}">
                                                <td class="py-3 px-4 text-slate-400 text-sm">{{ $volunteerNumber }}</td>
                                                <td class="py-3 px-4">
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-sm">{{ $fullName }}</p>
                                                        @if(!empty($memberRoles))
                                                            <span class="text-xs text-slate-400">{{ implode(', ', $memberRoles) }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @php
                                                    // Get all records for this user on this date to find time in (first) and time out (second)
                                                    $userRecords = collect($u?->attendanceRecords ?? [])->sortBy('attendance_time')->values();
                                                    $firstRecord = $userRecords->first();
                                                    $secondRecord = $userRecords->count() > 1 ? $userRecords->get(1) : null;
                                                    
                                                    $timeInValue = $firstRecord && $firstRecord->attendance_time 
                                                        ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('H:i') 
                                                        : '';
                                                    $timeOutValue = $secondRecord && $secondRecord->attendance_time 
                                                        ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('H:i') 
                                                        : '';
                                                    
                                                    $timeInDisplay = $firstRecord && $firstRecord->attendance_time 
                                                        ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('g:i A') 
                                                        : '—';
                                                    $timeOutDisplay = $secondRecord && $secondRecord->attendance_time 
                                                        ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('g:i A') 
                                                        : '—';
                                                    
                                                    // Calculate total time
                                                    $totalTimeDisplay = '—';
                                                    if ($firstRecord && $firstRecord->attendance_time && $secondRecord && $secondRecord->attendance_time) {
                                                        $timeIn = \Carbon\Carbon::parse($firstRecord->attendance_time);
                                                        $timeOut = \Carbon\Carbon::parse($secondRecord->attendance_time);
                                                        $diffMinutes = $timeIn->diffInMinutes($timeOut);
                                                        $hours = floor($diffMinutes / 60);
                                                        $mins = $diffMinutes % 60;
                                                        $totalTimeDisplay = $hours . 'h ' . $mins . 'm';
                                                    }
                                                    
                                                    $firstRecordId = $firstRecord?->attendance_id ?? 0;
                                                    
                                                    // Override display if N/A
                                                    if ($showAsNA) {
                                                        $timeInDisplay = '—';
                                                        $timeOutDisplay = '—';
                                                        $totalTimeDisplay = 'N/A';
                                                    }
                                                @endphp
                                                <td class="py-3 px-4">
                                                    @if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA)
                                                        <input 
                                                            type="time" 
                                                            value="{{ $timeInValue }}"
                                                            class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime({{ $firstRecordId }}, {{ $uid }}, 'time_in', $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $currentDate }}')"
                                                        >
                                                    @else
                                                        <span class="text-slate-600">{{ $timeInDisplay }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA)
                                                        <input 
                                                            type="time" 
                                                            value="{{ $timeOutValue }}"
                                                            class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime({{ $firstRecordId }}, {{ $uid }}, 'time_out', $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $currentDate }}')"
                                                        >
                                                    @else
                                                        <span class="text-slate-600">{{ $timeOutDisplay }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4 text-slate-600 {{ $showAsNA ? 'text-slate-400' : '' }}">{{ $totalTimeDisplay }}</td>
                                                @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                    <td class="py-3 px-4">
                                                        @if($showAsNA)
                                                            {{-- N/A date - no editing allowed --}}
                                                            <span class="text-slate-300">—</span>
                                                        @elseif($isEditing)
                                                            {{-- In edit mode: show save (check) and cancel (x) --}}
                                                            <button 
                                                                type="button" 
                                                                wire:click="prepareConfirmSave({{ $uid }}, '{{ addslashes($fullNameWithTitles) }}')"
                                                                class="text-green-600 hover:text-green-800 transition"
                                                                title="Save changes"
                                                            >
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                            <button 
                                                                type="button" 
                                                                wire:click="cancelEditing({{ $uid }})"
                                                                class="text-red-400 hover:text-red-600 transition ml-2"
                                                                title="Cancel changes"
                                                            >
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        @else
                                                            {{-- Not in edit mode: show edit (pencil) for everyone including self --}}
                                                            <button 
                                                                type="button" 
                                                                wire:click="startEditing({{ $uid }})"
                                                                class="text-slate-400 hover:text-slate-600 transition"
                                                                title="Edit attendance"
                                                            >
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Right: Calendar & Stats --}}
                        <div class="space-y-4">
                            {{-- Calendar --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900">{{ $calMonthLabel }}</span>
                                    <div class="flex gap-1">
                                        <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click.prevent="prevCalendarMonth">&lsaquo;</button>
                                        <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click.prevent="nextCalendarMonth">&rsaquo;</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-1 text-center">
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Sun</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Mon</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Tue</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Wed</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Thu</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Fri</div>
                                    <div class="text-[10px] uppercase tracking-wide text-slate-400 py-1">Sat</div>

                                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                                        <div></div>
                                    @endfor

                                    @for($i = 1; $i <= $daysInMonth; $i++)
                                        @php
                                            $dayDate = \Carbon\Carbon::create($calYear, $calMonth, $i)->format('Y-m-d');
                                            $isToday = $date && (\Carbon\Carbon::parse($date)->toDateString() === $dayDate);
                                        @endphp
                                        <div
                                            class="h-8 flex items-center justify-center text-sm rounded cursor-pointer transition {{ $isToday ? 'bg-slate-700 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100' }}"
                                            wire:click.prevent="setDate('{{ $dayDate }}')"
                                        >
                                            {{ $i }}
                                        </div>
                                    @endfor
                                </div>
                                <div class="text-right text-xs text-slate-400 mt-3">{{ $calMonthLabel }}</div>
                            </div>

                            {{-- Stats --}}
                            <div class="space-y-2">
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <div class="text-xl font-bold text-slate-900">{{ $totalVolunteers ?? $total }}</div>
                                    <div class="text-xs text-slate-500">Total</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-green-500 bg-green-50 p-3">
                                    <div class="text-xl font-bold text-green-700">{{ $presentCount }}</div>
                                    <div class="text-xs text-green-600">Present</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-red-500 bg-red-50 p-3">
                                    <div class="text-xl font-bold text-red-700">{{ $absentCount }}</div>
                                    <div class="text-xs text-red-600">Absent</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-indigo-500 bg-indigo-50 p-3">
                                    <div class="text-xl font-bold text-indigo-700">{{ $dailyAvgTime ?? '—' }}</div>
                                    <div class="text-xs text-indigo-600">Avg Time</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Weekly Analytics Section (Separate from Daily View Table) --}}
            @if(!empty($weeklyAnalytics) && !empty($weeklyAnalytics['weeks']) && count($weeklyAnalytics['weeks']) > 0)
                        <div class="mt-4 px-5">
                            <div class="rounded-lg border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="text-sm font-semibold text-slate-700">
                                        Weekly Analytics - {{ $weeklyAnalytics['month_label'] ?? 'This Month' }}
                                        @if(!empty($weeklyAnalytics['current_week_label']))
                                            <span class="text-xs font-normal text-slate-500 ml-2">(Selected: {{ $weeklyAnalytics['current_week_label'] }})</span>
                                        @endif
                                    </h4>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="prevWeeklyAnalyticsMonth" 
                                            class="px-2 py-1 text-xs rounded border border-slate-300 hover:bg-slate-50 transition-colors">
                                            ← Prev
                                        </button>
                                        <button wire:click="nextWeeklyAnalyticsMonth" 
                                            class="px-2 py-1 text-xs rounded border border-slate-300 hover:bg-slate-50 transition-colors">
                                            Next →
                                        </button>
                                    </div>
                                </div>
                                
                                @php
                                    $weekLabels = [];
                                    $weekAvgHours = [];
                                    $weekPresent = [];
                                    $weekAbsent = [];
                                    $totalVolsForWeek = $totalVolunteers ?? 0;
                                    
                                    foreach ($weeklyAnalytics['weeks'] as $wData) {
                                        $weekNum = $wData['week_number'] ?? 1;
                                        $weekLabels[] = 'Week ' . $weekNum;
                                        $weekDays = $wData['total_days'] ?? 1;
                                        $weekAvgHours[] = $weekDays > 0 ? round(($wData['total_hours'] ?? 0) / $weekDays, 1) : 0;
                                        // Use roster-based absent from backend calculation
                                        $present = $wData['present'] ?? 0;
                                        $absent = $wData['absent'] ?? 0;
                                        // Calculate average per day
                                        $weekPresent[] = $weekDays > 0 ? round($present / $weekDays, 1) : 0;
                                        $weekAbsent[] = $weekDays > 0 ? round($absent / $weekDays, 1) : 0;
                                    }
                                    
                                    $weeklyChartData = [
                                        'labels' => $weekLabels,
                                        'avgHours' => $weekAvgHours,
                                        'present' => $weekPresent,
                                        'absent' => $weekAbsent,
                                    ];
                                @endphp
                                
                                <div class="grid grid-cols-2 gap-4"
                                    wire:ignore
                                    wire:key="volunteer-weekly-charts-{{ $weeklyAnalytics['year'] ?? '' }}-{{ $weeklyAnalytics['month'] ?? '' }}"
                                    x-data="{
                                        lineChart: null,
                                        barChart: null,
                                        chartData: @js($weeklyChartData),
                                        init() {
                                            this.$nextTick(() => {
                                                if (this.lineChart) this.lineChart.destroy();
                                                if (this.barChart) this.barChart.destroy();
                                                
                                                // Line chart for average hours
                                                const lineCtx = this.$refs.lineCanvas;
                                                if (lineCtx && window.Chart) {
                                                    this.lineChart = new Chart(lineCtx, {
                                                        type: 'line',
                                                        data: {
                                                            labels: this.chartData.labels,
                                                            datasets: [{
                                                                label: 'Avg Working Hours',
                                                                data: this.chartData.avgHours,
                                                                borderColor: 'rgb(79, 70, 229)',
                                                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                                                borderWidth: 2,
                                                                tension: 0.3,
                                                                fill: true
                                                            }]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                x: { title: { display: true, text: 'Weeks', font: { size: 11 } } },
                                                                y: { beginAtZero: true, title: { display: true, text: 'Hours', font: { size: 11 } } }
                                                            },
                                                            plugins: {
                                                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                                                            }
                                                        }
                                                    });
                                                }
                                                
                                                // 100% Stacked bar chart for attendance rate
                                                const barCtx = this.$refs.barCanvas;
                                                if (barCtx && window.Chart) {
                                                    this.barChart = new Chart(barCtx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: this.chartData.labels,
                                                            datasets: [
                                                                {
                                                                    label: 'Present',
                                                                    data: this.chartData.present,
                                                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                                                    borderColor: 'rgb(34, 197, 94)',
                                                                    borderWidth: 1
                                                                },
                                                                {
                                                                    label: 'Absent',
                                                                    data: this.chartData.absent,
                                                                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                                                    borderColor: 'rgb(239, 68, 68)',
                                                                    borderWidth: 1
                                                                }
                                                            ]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                x: { stacked: true, title: { display: true, text: 'Weeks', font: { size: 11 } } },
                                                                y: { 
                                                                    stacked: true, 
                                                                    beginAtZero: true,
                                                                    title: { display: true, text: 'Average Volunteers per Day', font: { size: 11 } }
                                                                }
                                                            },
                                                            plugins: {
                                                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }"
                                >
                                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                                        <h5 class="text-xs font-semibold text-slate-700 mb-3">Average Working Hours (Weekly)</h5>
                                        <div class="h-48">
                                            <canvas x-ref="lineCanvas"></canvas>
                                        </div>
                                    </div>
                                    
                                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                                        <h5 class="text-xs font-semibold text-slate-700 mb-3">Attendance Rate (Weekly)</h5>
                                        <div class="h-48">
                                            <canvas x-ref="barCanvas"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-slate-400 mt-3">* Weekly analytics show average working hours and attendance rate for each Saturday-Sunday pair in the selected month.</p>
                            </div>
                        </div>
                    </div>
                @endif

            @if(!empty($monthlyView) && $monthlyView)
                {{-- Monthly View --}}
                <div class="px-5 pb-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <span class="text-base font-semibold text-slate-900">{{ $matrixMonthLabel }}</span>
                                <p class="text-xs text-slate-400 mt-1">Format: Time In - Time Out</p>
                            </div>
                            <div class="flex gap-1">
                                <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click.prevent="prevMatrixMonth">&lsaquo;</button>
                                <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click.prevent="nextMatrixMonth">&rsaquo;</button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                        <th class="py-3 px-4">Member</th>
                                        @foreach($weekendDates as $wd)
                                            <th class="py-3 px-2 text-center">{{ \Carbon\Carbon::parse($wd)->format('M j') }}</th>
                                        @endforeach
                                        @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                            <th class="py-3 px-4 text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($members as $member)
                                        @php
                                            $u = $member->user ?? null;
                                            $uid = optional($u)->id ?? null;
                                            $committeeText = $uid ? implode(', ', ($userCommittees[$uid] ?? [])) : ($member->committee_ref->committee_name ?? '');
                                            $positionText = $uid ? implode(', ', ($userPositions[$uid] ?? [])) : (optional($member->position)->position_title ?? optional($member->position)->position_name ?? '');
                                            $fullName = null;
                                            if ($u && isset($u->userProfile)) {
                                                $parts = array_filter([
                                                    $u->userProfile->f_name ?? null,
                                                    $u->userProfile->m_name ?? null,
                                                    $u->userProfile->s_name ?? null,
                                                ]);
                                                $fullName = trim(implode(' ', $parts));
                                            }
                                            if (!$fullName) {
                                                $fullName = $u->name ?? '';
                                            }
                                            // Group ALL records by date (no session filter) to get time in (first) and time out (second)
                                            $allRecords = collect(optional($u)->attendanceRecords ?? []);
                                            $attGrouped = $allRecords->groupBy(function($r) {
                                                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
                                            });
                                            $volunteerNumber = optional($u?->fceerProfile)->volunteer_number ?: '';
                                            $roleEntries = $uid ? ($userRoles[$uid] ?? []) : [];
                                            if (!$roleEntries && ($committeeText || $positionText)) {
                                                $fallback = trim($committeeText ?: '');
                                                if ($positionText) {
                                                    $fallback .= $fallback ? ', ' . $positionText : $positionText;
                                                }
                                                if ($fallback) {
                                                    $roleEntries[] = $fallback;
                                                }
                                            }
                                            
                                            // Build full name with titles for modal
                                            $fullNameWithTitles = $fullName;
                                            if ($u && isset($u->professionalCredentials)) {
                                                $prefixes = $u->professionalCredentials->pluck('prefix.prefix_title')->filter()->implode(' ');
                                                $suffixes = $u->professionalCredentials->pluck('suffix.suffix_title')->filter()->implode(', ');
                                                if ($prefixes) $fullNameWithTitles = $prefixes . ' ' . $fullNameWithTitles;
                                                if ($suffixes) $fullNameWithTitles .= ', ' . $suffixes;
                                            }
                                            
                                            // Check if this row is in edit mode
                                            $isEditing = isset($editingRow[$uid]);
                                            
                                            // Check if there's a pending edit for this user
                                            $hasPendingEdit = collect($editingAttendance)->filter(function($change) use ($uid) {
                                                return ($change['user_id'] ?? null) == $uid;
                                            })->isNotEmpty();
                                            
                                            // Check if current user is the same as this row's user (prevent self-edit)
                                            $isSelf = (auth()->id() == $uid);
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition {{ $isEditing ? 'bg-amber-50' : '' }}">
                                            <td class="py-3 px-4">
                                                <div>
                                                    <p class="font-semibold text-slate-900 text-sm">[{{ $volunteerNumber }}] {{ $fullName }}</p>
                                                    @if(!empty($roleEntries))
                                                        <span class="text-xs text-slate-400">{{ implode(', ', $roleEntries) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            @foreach($weekendDates as $wd)
                                                @php
                                                    $dayRecords = $attGrouped[$wd] ?? collect();
                                                    $timeInValue = '';
                                                    $timeOutValue = '';
                                                    $timeDisplay = '—';
                                                    $firstRecordId = 0;
                                                    if ($dayRecords->count() > 0) {
                                                        // Sort by attendance_time to ensure correct order
                                                        $sorted = $dayRecords->sortBy('attendance_time')->values();
                                                        $firstRecord = $sorted->first();
                                                        $secondRecord = $sorted->count() > 1 ? $sorted->get(1) : null;
                                                        
                                                        $firstRecordId = $firstRecord?->attendance_id ?? 0;
                                                        
                                                        $timeInValue = $firstRecord && $firstRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('H:i') 
                                                            : '';
                                                        $timeOutValue = $secondRecord && $secondRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('H:i') 
                                                            : '';
                                                        
                                                        $timeIn = $firstRecord && $firstRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('g:i') 
                                                            : '—';
                                                        $timeOut = $secondRecord && $secondRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('g:i') 
                                                            : '—';
                                                        $timeDisplay = $timeIn . '-' . $timeOut;
                                                    }
                                                @endphp
                                                <td class="py-3 px-2 text-center text-xs text-slate-600">
                                                    @if($isEditing && in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                        <div class="flex flex-col gap-1">
                                                            <input 
                                                                type="time" 
                                                                value="{{ $timeInValue }}"
                                                                class="text-xs text-slate-600 border border-slate-200 rounded px-1 py-0.5 w-20 focus:ring-1 focus:ring-slate-300"
                                                                wire:change="updateAttendanceTime({{ $firstRecordId }}, {{ $uid }}, 'time_in', $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $wd }}')"
                                                                title="Time In"
                                                            >
                                                            <input 
                                                                type="time" 
                                                                value="{{ $timeOutValue }}"
                                                                class="text-xs text-slate-600 border border-slate-200 rounded px-1 py-0.5 w-20 focus:ring-1 focus:ring-slate-300"
                                                                wire:change="updateAttendanceTime({{ $firstRecordId }}, {{ $uid }}, 'time_out', $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $wd }}')"
                                                                title="Time Out"
                                                            >
                                                        </div>
                                                    @else
                                                        {{ $timeDisplay }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                <td class="py-3 px-4 text-center">
                                                    @if($isEditing)
                                                        {{-- In edit mode: show save (check) and cancel (x) --}}
                                                        <button 
                                                            type="button" 
                                                            wire:click="prepareConfirmSave({{ $uid }}, '{{ addslashes($fullNameWithTitles) }}')"
                                                            class="text-green-600 hover:text-green-800 transition"
                                                            title="Save changes"
                                                        >
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelEditing({{ $uid }})"
                                                            class="text-red-400 hover:text-red-600 transition ml-2"
                                                            title="Cancel changes"
                                                        >
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    @else
                                                        {{-- Not in edit mode: show edit (pencil) for everyone including self --}}
                                                        <button 
                                                            type="button" 
                                                            wire:click="startEditing({{ $uid }})"
                                                            class="text-slate-400 hover:text-slate-600 transition"
                                                            title="Edit attendance"
                                                        >
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Monthly Analytics Section with Charts --}}
                        <div class="mt-6 pt-4 border-t border-slate-200">
                            <h4 class="text-sm font-semibold text-slate-700 mb-4">Monthly Volunteer Analytics</h4>
                            
                            @if(!empty($monthlyAnalytics) && count($monthlyAnalytics) > 0)
                                @php
                                    $monthLabels = [];
                                    $monthAvgHours = [];
                                    $monthPresent = [];
                                    $monthAbsent = [];
                                    $totalVolsForMonth = $totalVolunteers ?? 0;
                                    
                                    foreach ($monthlyAnalytics as $mData) {
                                        $monthLabels[] = $mData['label'] ?? '';
                                        $monthDays = $mData['total_days'] ?? 1;
                                        $monthAvgHours[] = $monthDays > 0 ? round(($mData['total_hours'] ?? 0) / $monthDays, 1) : 0;
                                        // Use roster-based absent from backend calculation
                                        $present = $mData['present'] ?? 0;
                                        $absent = $mData['absent'] ?? 0;
                                        // Calculate average per day
                                        $monthPresent[] = $monthDays > 0 ? round($present / $monthDays, 1) : 0;
                                        $monthAbsent[] = $monthDays > 0 ? round($absent / $monthDays, 1) : 0;
                                    }
                                    
                                    $monthlyChartData = [
                                        'labels' => $monthLabels,
                                        'avgHours' => $monthAvgHours,
                                        'present' => $monthPresent,
                                        'absent' => $monthAbsent,
                                    ];
                                @endphp
                                
                                <div class="grid grid-cols-2 gap-4"
                                    wire:ignore
                                    wire:key="volunteer-monthly-charts"
                                    x-data="{
                                        lineChart: null,
                                        barChart: null,
                                        chartData: @js($monthlyChartData),
                                        init() {
                                            this.$nextTick(() => {
                                                if (this.lineChart) this.lineChart.destroy();
                                                if (this.barChart) this.barChart.destroy();
                                                
                                                // Line chart for monthly average hours
                                                const lineCtx = this.$refs.lineCanvas;
                                                if (lineCtx && window.Chart) {
                                                    this.lineChart = new Chart(lineCtx, {
                                                        type: 'line',
                                                        data: {
                                                            labels: this.chartData.labels,
                                                            datasets: [{
                                                                label: 'Avg Working Hours',
                                                                data: this.chartData.avgHours,
                                                                borderColor: 'rgb(79, 70, 229)',
                                                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                                                borderWidth: 2,
                                                                tension: 0.3,
                                                                fill: true
                                                            }]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                x: { title: { display: true, text: 'Months', font: { size: 11 } } },
                                                                y: { beginAtZero: true, title: { display: true, text: 'Hours', font: { size: 11 } } }
                                                            },
                                                            plugins: {
                                                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                                                            }
                                                        }
                                                    });
                                                }
                                                
                                                // 100% Stacked bar chart for monthly attendance rate
                                                const barCtx = this.$refs.barCanvas;
                                                if (barCtx && window.Chart) {
                                                    this.barChart = new Chart(barCtx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: this.chartData.labels,
                                                            datasets: [
                                                                {
                                                                    label: 'Present',
                                                                    data: this.chartData.present,
                                                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                                                    borderColor: 'rgb(34, 197, 94)',
                                                                    borderWidth: 1
                                                                },
                                                                {
                                                                    label: 'Absent',
                                                                    data: this.chartData.absent,
                                                                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                                                    borderColor: 'rgb(239, 68, 68)',
                                                                    borderWidth: 1
                                                                }
                                                            ]
                                                        },
                                                        options: {
                                                            responsive: true,
                                                            maintainAspectRatio: false,
                                                            scales: {
                                                                x: { stacked: true, title: { display: true, text: 'Months', font: { size: 11 } } },
                                                                y: { 
                                                                    stacked: true, 
                                                                    beginAtZero: true,
                                                                    title: { display: true, text: 'Average Volunteers per Day', font: { size: 11 } }
                                                                }
                                                            },
                                                            plugins: {
                                                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 10 } } }
                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }"
                                >
                                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                                        <h5 class="text-xs font-semibold text-slate-700 mb-3">Average Working Hours (Monthly)</h5>
                                        <div class="h-64">
                                            <canvas x-ref="lineCanvas"></canvas>
                                        </div>
                                    </div>
                                    
                                    <div class="rounded-lg border border-slate-200 bg-white p-3">
                                        <h5 class="text-xs font-semibold text-slate-700 mb-3">Attendance Rate (Monthly)</h5>
                                        <div class="h-64">
                                            <canvas x-ref="barCanvas"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-slate-400 mt-3">* Monthly analytics show average working hours and attendance rate across all months in the review season.</p>
                            @else
                                <p class="text-sm text-slate-500">No monthly analytics data available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Confirmation Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelConfirmModal">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Confirm Changes</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-600">
                        Are you sure you want to modify the attendance record(s) for 
                        <span class="font-semibold text-slate-900">{{ $confirmModalData['full_name'] ?? 'this user' }}</span>
                        on the following date(s)?
                    </p>
                    <ul class="mt-3 space-y-1">
                        @foreach($confirmModalData['dates'] ?? [] as $dateStr)
                            <li class="text-sm text-slate-700 flex items-center gap-2">
                                <i class="fa fa-calendar text-slate-400"></i>
                                {{ $dateStr }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">
                    <button 
                        type="button" 
                        wire:click="cancelConfirmModal"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmSaveAttendance"
                        class="px-4 py-2 text-sm font-medium text-white bg-slate-700 hover:bg-slate-800 rounded-md transition"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Self-Edit Error Modal --}}
    @if($showSelfEditError)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeSelfEditError">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                    <h3 class="text-lg font-semibold text-red-700 flex items-center gap-2">
                        <i class="fa fa-exclamation-circle"></i>
                        Cannot Edit
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-600">
                        You cannot edit your own attendance record. Please ask another administrator or executive to make changes to your attendance.
                    </p>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="closeSelfEditError"
                        class="px-4 py-2 text-sm font-medium text-white bg-slate-700 hover:bg-slate-800 rounded-md transition"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Review Season Modal (Exec Only) --}}
    @if($showReviewSeasonModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeReviewSeasonModal">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-indigo-200 bg-indigo-50">
                    <h3 class="text-lg font-semibold text-indigo-900 flex items-center gap-2">
                        <i class="fa fa-calendar-check-o"></i>
                        Set Review Season Range
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-600 mb-4">
                        Set the review season date range. Only attendance records within this range can be edited or created. 
                        Dates outside this range will be restricted.
                    </p>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Start Month</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="reviewSeasonStartMonth"
                                    class="flex-1 text-sm border-slate-200 rounded-md focus:border-indigo-400 focus:ring-indigo-400"
                                >
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endfor
                                </select>
                                <select 
                                    wire:model="reviewSeasonStartYear"
                                    class="w-24 text-sm border-slate-200 rounded-md focus:border-indigo-400 focus:ring-indigo-400"
                                >
                                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">End Month</label>
                            <div class="flex gap-2">
                                <select 
                                    wire:model="reviewSeasonEndMonth"
                                    class="flex-1 text-sm border-slate-200 rounded-md focus:border-indigo-400 focus:ring-indigo-400"
                                >
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endfor
                                </select>
                                <select 
                                    wire:model="reviewSeasonEndYear"
                                    class="w-24 text-sm border-slate-200 rounded-md focus:border-indigo-400 focus:ring-indigo-400"
                                >
                                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-lg border border-amber-200 bg-amber-50">
                        <p class="text-xs text-amber-700">
                            <i class="fa fa-info-circle mr-1"></i>
                            <strong>Warning:</strong> Changing the review season will affect all attendance views across the system (Students, Volunteers, and User pages).
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">
                    <button 
                        type="button" 
                        wire:click="closeReviewSeasonModal"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmSetReviewSeason"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md transition"
                    >
                        <i class="fa fa-check mr-1"></i> Set Review Season
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Outside Season Error Modal --}}
    @if($showOutsideSeasonError)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeOutsideSeasonError">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                    <h3 class="text-lg font-semibold text-red-700 flex items-center gap-2">
                        <i class="fa fa-calendar-times-o"></i>
                        Outside Review Season
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-600">
                        The date you are trying to set attendance for (<span class="font-semibold">{{ $outsideSeasonErrorData['date'] ?? 'Unknown' }}</span>) 
                        is outside the current review season (<span class="font-semibold">{{ $outsideSeasonErrorData['range'] ?? 'Unknown' }}</span>).
                    </p>
                    <p class="text-sm text-slate-600 mt-2">
                        Please contact an administrator or executive if you think this is a mistake.
                    </p>
                </div>
                <div class="px-6 py-4 bg-slate-50 flex justify-end">
                    <button 
                        type="button" 
                        wire:click="closeOutsideSeasonError"
                        class="px-4 py-2 text-sm font-medium text-white bg-slate-700 hover:bg-slate-800 rounded-md transition"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
