{{-- Student Attendance - Matching Volunteer Style --}}

<div class="attendance-page" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="attendance-wrapper">
        @php
            // Flatten profiles across rooms and apply committee filter + sorting/unique
            $profs = collect();
            foreach ($profilesByRoom as $rid => $plist) {
                foreach ($plist as $p) {
                    $p->room_ref = $rooms[$rid] ?? null;
                    $profs->push($p);
                }
            }

            if (!empty($committeeFilter)) {
                $profs = $profs->filter(function ($p) use ($committeeFilter) {
                    return isset($p->room_ref) && ($p->room_ref->room_id == $committeeFilter);
                });
            }

            $profs = $profs->unique('user_id')->filter()->sortBy(function ($p) {
                return strtolower(optional($p->user)->name ?? ($p->student_number ?? ''));
            })->values();

            // stats
            $total = $profs->count();
            $presentCount = 0;
            $absentCount = 0;
            $excusedCount = 0;
            foreach ($profs as $p) {
                $u = $p->user ?? null;
                $att = $u ? ($u->attendanceRecords->first() ?? null) : null;
                $status = 'absent';
                if ($att) {
                    // Check excused first (can be without time)
                    if (strtolower($att->student_status ?? '') === 'excused') {
                        $status = 'excused';
                    } elseif ($att->attendance_time) {
                        $status = 'present';
                    } else {
                        $status = 'absent';
                    }
                }
                if ($status === 'present') {
                    $presentCount++;
                } elseif ($status === 'excused') {
                    $excusedCount++;
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
            $sessionLabel = $session ? strtoupper($session) : 'AM';
            $sessionText = "{$sessionLabel} Session";
            $groupLabel = 'All groups';
            if (!empty($committeeFilter)) {
                $match = collect($allCommittees ?? [])->firstWhere('room_id', $committeeFilter);
                if ($match) {
                    $groupLabel = $match->group ?? ($match->room_id ?? $committeeFilter);
                } else {
                    $groupLabel = $committeeFilter;
                }
            }
            $calMonthLabel = \Carbon\Carbon::create($calYear, $calMonth, 1)->format('F Y');
            $matrixMonthLabel = \Carbon\Carbon::create($matrixYear ?? $calYear, $matrixMonth ?? $calMonth, 1)->format('F Y');
            $dailyPreviewText = "{$selectedDateLabel} ({$sessionText})";
            $monthlyMatrixHeader = "{$matrixMonthLabel} ({$sessionText})";
        @endphp

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="px-5 pt-5 pb-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">Student Attendance</h1>
                        <p class="text-sm text-slate-500 mt-1">
                            @if(!empty($monthlyView) && $monthlyView)
                                {{ $monthlyMatrixHeader }}
                            @else
                                {{ $dailyPreviewText }}
                            @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="setSession('am')" class="p-2 rounded-md transition {{ $session === 'am' ? 'bg-amber-100 text-amber-500' : 'text-slate-300 hover:text-slate-400' }}" title="AM Session">
                            <i class="fa fa-sun-o text-lg"></i>
                        </button>
                        <button type="button" wire:click="setSession('pm')" class="p-2 rounded-md transition {{ $session === 'pm' ? 'bg-indigo-100 text-indigo-500' : 'text-slate-300 hover:text-slate-400' }}" title="PM Session">
                            <i class="fa fa-moon-o text-lg"></i>
                        </button>
                        @if(!empty($monthlyView) && $monthlyView)
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click.prevent="toggleMonthlyView">Daily view</button>
                        @else
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click.prevent="toggleMonthlyView">Monthly view</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="px-5 pb-4">
                <form wire:submit.prevent="applyFilters" class="flex items-end gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <div class="flex flex-col gap-1 flex-1">
                        <label class="text-[10px] uppercase tracking-widest text-slate-500">Group</label>
                        <select wire:model.defer="committeeFilter" class="w-full appearance-none rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-slate-400 focus:outline-none" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                            <option value="">All groups</option>
                            @foreach($allCommittees as $c)
                                <option value="{{ $c->room_id }}">{{ $c->group }}</option>
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
                                            <th class="py-3 px-4">Student ID</th>
                                            <th class="py-3 px-4">Full Name</th>
                                            <th class="py-3 px-4">Time In</th>
                                            <th class="py-3 px-4">Status</th>
                                            <th class="py-3 px-4">Letter</th>
                                            @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                <th class="py-3 px-4">Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($profs as $profile)
                                            @php
                                                $u = $profile->user ?? null;
                                                $r = $profile->room_ref ?? null;
                                                $att = $u ? (collect($u->attendanceRecords)->filter(function($r) use ($date) {
                                                    return \Carbon\Carbon::parse($r->date)->format('Y-m-d') === $date;
                                                })->first() ?? null) : null;
                                                $status = 'absent';
                                                
                                                // If weekday or outside review season, status is always N/A
                                                if ($showAsNA) {
                                                    $status = 'n/a';
                                                } elseif ($att) {
                                                    // Check excused first (can be without time)
                                                    if (strtolower($att->student_status ?? '') === 'excused') {
                                                        $status = 'excused';
                                                    } elseif ($att->attendance_time) {
                                                        $status = 'present';
                                                    } else {
                                                        $status = 'absent';
                                                    }
                                                }
                                                
                                                // Check if late: AM > 8:15, PM > 12:15
                                                $isLate = false;
                                                $noteText = '';
                                                if ($att && $att->attendance_time) {
                                                    $timeIn = \Carbon\Carbon::parse($att->attendance_time);
                                                    $sessionLower = strtolower($att->session ?? 'am');
                                                    if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                                                        $isLate = true;
                                                        $noteText = 'Late';
                                                    } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                                                        $isLate = true;
                                                        $noteText = 'Late';
                                                    }
                                                }

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
                                                    $fullName = $u->name ?? ($profile->student_number ?? '');
                                                }
                                                $groupText = $r->group ?? $profile->student_group ?? null;
                                                
                                                // Build full name with prefix/suffix for modal
                                                $fullNameWithTitles = $fullName;
                                                if ($u && isset($u->professionalCredentials)) {
                                                    $prefixes = $u->professionalCredentials->pluck('prefix.prefix_title')->filter()->implode(' ');
                                                    $suffixes = $u->professionalCredentials->pluck('suffix.suffix_title')->filter()->implode(', ');
                                                    if ($prefixes) $fullNameWithTitles = $prefixes . ' ' . $fullNameWithTitles;
                                                    if ($suffixes) $fullNameWithTitles .= ', ' . $suffixes;
                                                }
                                                
                                                $attendanceId = $att?->attendance_id ?? 0;
                                                $userId = $u?->id ?? 0;
                                                $currentDate = $date ?? now()->toDateString();
                                                
                                                // Check if this row is in edit mode
                                                $isEditing = isset($editingRow[$userId]);
                                                
                                                // Check if there's a pending edit for this attendance
                                                $hasPendingEdit = isset($editingAttendance[$attendanceId]) || isset($pendingChanges[$attendanceId]);
                                                
                                                // Check if current user is the same as this row's user (prevent self-edit)
                                                $isSelf = (auth()->id() == $userId);
                                            @endphp
                                            <tr class="hover:bg-slate-50 transition {{ $isEditing ? 'bg-amber-50' : '' }}">
                                                <td class="py-3 px-4 text-slate-400 text-sm">{{ $profile->student_number ?? ($u->id ?? '') }}</td>
                                                <td class="py-3 px-4">
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-sm">{{ $fullName }}</p>
                                                        @if($groupText)
                                                            <span class="text-xs text-slate-400">{{ $groupText }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                @php
                                                    // Time in display and value for input
                                                    $timeInDisplay = $att && $att->attendance_time 
                                                        ? \Carbon\Carbon::parse($att->attendance_time)->format('g:i A') 
                                                        : '—';
                                                    $timeInValue = $att && $att->attendance_time 
                                                        ? \Carbon\Carbon::parse($att->attendance_time)->format('H:i') 
                                                        : '';
                                                    
                                                    // Status with color coding
                                                    $statusValue = 'absent';
                                                    $statusText = 'Absent';
                                                    $statusClass = 'bg-red-100 text-red-600';
                                                    
                                                    // If weekday or outside review season, force N/A
                                                    if ($showAsNA) {
                                                        $statusValue = 'n/a';
                                                        $statusText = 'N/A';
                                                        $statusClass = 'bg-slate-100 text-slate-400';
                                                        $timeInDisplay = '—';
                                                    } elseif ($att) {
                                                        // Check excused status first (can be without time)
                                                        if (strtolower($att->student_status ?? '') === 'excused') {
                                                            $statusValue = 'excused';
                                                            $statusText = 'Excused';
                                                            $statusClass = 'bg-blue-100 text-blue-600';
                                                        } elseif ($att->attendance_time) {
                                                            // Has time - check late status
                                                            if (strtolower($att->student_status ?? '') === 'late' || $isLate) {
                                                                $statusValue = 'late';
                                                                $statusText = 'Late';
                                                                $statusClass = 'bg-yellow-100 text-yellow-600';
                                                            } else {
                                                                $statusValue = 'on time';
                                                                $statusText = 'On Time';
                                                                $statusClass = 'bg-green-100 text-green-600';
                                                            }
                                                        }
                                                    }
                                                    // Check for pending status change (only if not showing as N/A)
                                                    if (!$showAsNA) {
                                                        $pendingKey = $attendanceId ?: "new_{$userId}_{$currentDate}";
                                                        $pendingStatus = $editingAttendance[$pendingKey]['status'] ?? ($editingAttendance[$attendanceId]['status'] ?? null);
                                                        if ($pendingStatus) {
                                                            $statusValue = $pendingStatus;
                                                            if ($pendingStatus === 'on time') {
                                                                $statusText = 'On Time';
                                                                $statusClass = 'bg-green-100 text-green-600';
                                                            } elseif ($pendingStatus === 'late') {
                                                                $statusText = 'Late';
                                                                $statusClass = 'bg-yellow-100 text-yellow-600';
                                                            } elseif ($pendingStatus === 'excused') {
                                                                $statusText = 'Excused';
                                                                $statusClass = 'bg-blue-100 text-blue-600';
                                                            } else {
                                                                $statusText = 'Absent';
                                                                $statusClass = 'bg-red-100 text-red-600';
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <td class="py-3 px-4">
                                                    @if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA)
                                                        <input 
                                                            type="time" 
                                                            value="{{ $timeInValue }}"
                                                            class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime({{ $attendanceId }}, {{ $userId }}, $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $currentDate }}')"
                                                        >
                                                    @else
                                                        <span class="text-slate-600">{{ $timeInDisplay }}</span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA)
                                                        <select 
                                                            class="appearance-none rounded-full px-2.5 py-1 text-[10px] font-semibold border-0 cursor-pointer focus:ring-2 focus:ring-slate-300 {{ $statusClass }}"
                                                            wire:change="updateAttendanceStatus({{ $attendanceId }}, {{ $userId }}, $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $currentDate }}')"
                                                        >
                                                            <option value="on time" {{ $statusValue === 'on time' ? 'selected' : '' }}>On Time</option>
                                                            <option value="late" {{ $statusValue === 'late' ? 'selected' : '' }}>Late</option>
                                                            <option value="excused" {{ $statusValue === 'excused' ? 'selected' : '' }}>Excused</option>
                                                            <option value="absent" {{ $statusValue === 'absent' ? 'selected' : '' }}>Absent</option>
                                                        </select>
                                                    @else
                                                        <span class="inline-flex items-center justify-center rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-3 px-4">
                                                    @if($att && $att->excuseLetter)
                                                        @php
                                                            $letterStatus = $att->excuseLetter->status === 'approved' ? 'Received' : ucfirst($att->excuseLetter->status);
                                                            $letterClass = match(strtolower($att->excuseLetter->status)) {
                                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                                'approved' => 'bg-green-100 text-green-800',
                                                                default => 'bg-gray-100 text-gray-800'
                                                            };
                                                        @endphp
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $letterClass }}">
                                                            {{ $letterStatus }}
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold bg-gray-100 text-gray-800">
                                                            n/a
                                                        </span>
                                                    @endif
                                                </td>
                                                @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                    <td class="py-3 px-4">
                                                        @if($showAsNA)
                                                            {{-- N/A date - no editing allowed --}}
                                                            <span class="text-slate-300">—</span>
                                                        @elseif($isEditing)
                                                            {{-- In edit mode: show save (check) and cancel (x) --}}
                                                            <button 
                                                                type="button" 
                                                                wire:click="prepareConfirmSave({{ $userId }}, '{{ addslashes($fullNameWithTitles) }}')"
                                                                class="text-green-600 hover:text-green-800 transition"
                                                                title="Save changes"
                                                            >
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                            <button 
                                                                type="button" 
                                                                wire:click="cancelEditing({{ $userId }})"
                                                                class="text-red-400 hover:text-red-600 transition ml-2"
                                                                title="Cancel changes"
                                                            >
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        @else
                                                            {{-- Not in edit mode: show edit (pencil) for everyone including self and absent --}}
                                                            <button 
                                                                type="button" 
                                                                wire:click="startEditing({{ $userId }})"
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
                                    <div class="text-xl font-bold text-slate-900">{{ $totalStudents ?? $total }}</div>
                                    <div class="text-xs text-slate-500">Total</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-green-500 bg-green-50 p-3">
                                    <div class="text-xl font-bold text-green-700">{{ $dailyAnalytics['on_time'] ?? 0 }}</div>
                                    <div class="text-xs text-green-600">On Time</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-yellow-500 bg-yellow-50 p-3">
                                    <div class="text-xl font-bold text-yellow-700">{{ $dailyAnalytics['late'] ?? 0 }}</div>
                                    <div class="text-xs text-yellow-600">Late</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-red-500 bg-red-50 p-3">
                                    <div class="text-xl font-bold text-red-700">{{ $dailyAnalytics['absent'] ?? $absentCount }}</div>
                                    <div class="text-xs text-red-600">Absent</div>
                                </div>
                                <div class="rounded-lg border-l-4 border-l-blue-500 bg-blue-50 p-3">
                                    <div class="text-xl font-bold text-blue-700">{{ $dailyAnalytics['excused'] ?? $excusedCount }}</div>
                                    <div class="text-xs text-blue-600">Excused</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Weekly Analytics Section (Separate from Daily View Table) --}}
            @if(!empty($weeklyAnalytics) && !empty($weeklyAnalytics['weeks']) && count($weeklyAnalytics['weeks']) > 0)
                <div class="px-5 pb-5">
                    <div class="rounded-lg border border-slate-200 bg-white p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold text-slate-700">
                                Weekly Attendance Breakdown - {{ $weeklyAnalytics['month_label'] ?? 'This Month' }}
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
                            $weekOnTime = [];
                            $weekLate = [];
                            $weekAbsent = [];
                            $weekExcused = [];
                            
                            foreach ($weeklyAnalytics['weeks'] as $wData) {
                                $weekNum = $wData['week_number'] ?? 1;
                                $weekDays = $wData['total_days'] ?? 1;
                                
                                $weekLabels[] = 'Week ' . $weekNum;
                                // Calculate AVERAGE per day in this week
                                $weekOnTime[] = $weekDays > 0 ? round(($wData['on_time'] ?? 0) / $weekDays, 1) : 0;
                                $weekLate[] = $weekDays > 0 ? round(($wData['late'] ?? 0) / $weekDays, 1) : 0;
                                $weekAbsent[] = $weekDays > 0 ? round(($wData['absent'] ?? 0) / $weekDays, 1) : 0;
                                $weekExcused[] = $weekDays > 0 ? round(($wData['excused'] ?? 0) / $weekDays, 1) : 0;
                            }
                            
                            $weeklyChartData = [
                                'labels' => $weekLabels,
                                'onTime' => $weekOnTime,
                                'late' => $weekLate,
                                'absent' => $weekAbsent,
                                'excused' => $weekExcused,
                            ];
                        @endphp
                        
                        <div wire:ignore
                            wire:key="student-weekly-chart-{{ $session }}-{{ $weeklyAnalytics['year'] ?? '' }}-{{ $weeklyAnalytics['month'] ?? '' }}"
                            x-data="{
                                weeklyChart: null,
                                chartData: @js($weeklyChartData),
                                init() {
                                    this.$nextTick(() => {
                                        if (this.weeklyChart) this.weeklyChart.destroy();
                                        
                                        const ctx = this.$refs.weeklyCanvas;
                                        if (ctx && window.Chart) {
                                            this.weeklyChart = new Chart(ctx, {
                                                type: 'bar',
                                                data: {
                                                    labels: this.chartData.labels,
                                                    datasets: [
                                                        {
                                                            label: 'On Time',
                                                            data: this.chartData.onTime,
                                                            backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                                            borderColor: 'rgb(34, 197, 94)',
                                                            borderWidth: 1
                                                        },
                                                        {
                                                            label: 'Late',
                                                            data: this.chartData.late,
                                                            backgroundColor: 'rgba(234, 179, 8, 0.8)',
                                                            borderColor: 'rgb(234, 179, 8)',
                                                            borderWidth: 1
                                                        },
                                                        {
                                                            label: 'Excused',
                                                            data: this.chartData.excused,
                                                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                                            borderColor: 'rgb(59, 130, 246)',
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
                                                        y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Average Students per Day', font: { size: 11 } } }
                                                    },
                                                    plugins: {
                                                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 10 } } }
                                                    }
                                                }
                                            });
                                        }
                                    });
                                }
                            }"
                        >
                            <div class="h-64">
                                <canvas x-ref="weeklyCanvas"></canvas>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-400 mt-3">* Stacked bar chart showing AVERAGE attendance per day for each Saturday-Sunday pair in the selected month.</p>
                    </div>
                </div>
            @endif

            @if(!empty($monthlyView) && $monthlyView)
                {{-- Monthly View --}}
                <div class="px-5 pb-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <span class="text-base font-semibold text-slate-900">{{ $matrixMonthLabel }} ({{ strtoupper($session ?? 'am') }})</span>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="inline-flex items-center gap-1 text-[10px]"><span class="w-2 h-2 rounded-full bg-green-400"></span> On Time</span>
                                    <span class="inline-flex items-center gap-1 text-[10px]"><span class="w-2 h-2 rounded-full bg-yellow-400"></span> Late</span>
                                    <span class="inline-flex items-center gap-1 text-[10px]"><span class="w-2 h-2 rounded-full bg-red-400"></span> Absent</span>
                                    <span class="inline-flex items-center gap-1 text-[10px]"><span class="w-2 h-2 rounded-full bg-blue-400"></span> Excused</span>
                                </div>
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
                                        <th class="py-3 px-4">Student</th>
                                        @foreach($weekendDates as $wd)
                                            <th class="py-3 px-2 text-center">{{ \Carbon\Carbon::parse($wd)->format('M j') }}</th>
                                        @endforeach
                                        @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                            <th class="py-3 px-4 text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($profs as $profile)
                                        @php
                                            $u = $profile->user ?? null;
                                            $fullName = null;
                                            if ($u && isset($u->userProfile)) {
                                                $parts = array_filter([
                                                    $u->userProfile->f_name ?? null,
                                                    $u->userProfile->m_name ?? null,
                                                    $u->userProfile->s_name ?? null,
                                                ]);
                                                $fullName = trim(implode(' ', $parts));
                                            }
                                            if (!$fullName) $fullName = $u->name ?? ($profile->student_number ?? '');
                                            $studentNumber = $profile->student_number ?? ($u->id ?? '');
                                            $room = $profile->room_ref ?? null;
                                            $groupText = $room->group ?? $profile->student_group ?? null;
                                            // Filter attendance by selected session (case-insensitive, default to 'am' if null)
                                            $sessionFilter = strtolower($session ?? 'am');
                                            $attMap = collect($u?->attendanceRecords ?? [])->filter(function($r) use ($sessionFilter) {
                                                return strtolower($r->session ?? '') === $sessionFilter;
                                            })->keyBy(function($r) {
                                                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
                                            });
                                            
                                            // Build full name with titles for modal
                                            $fullNameWithTitles = $fullName;
                                            if ($u && isset($u->professionalCredentials)) {
                                                $prefixes = $u->professionalCredentials->pluck('prefix.prefix_title')->filter()->implode(' ');
                                                $suffixes = $u->professionalCredentials->pluck('suffix.suffix_title')->filter()->implode(', ');
                                                if ($prefixes) $fullNameWithTitles = $prefixes . ' ' . $fullNameWithTitles;
                                                if ($suffixes) $fullNameWithTitles .= ', ' . $suffixes;
                                            }
                                            $userId = $u?->id ?? 0;
                                            
                                            // Check if this row is in edit mode
                                            $isEditing = isset($editingRow[$userId]);
                                            
                                            // Check if there's a pending edit for this user
                                            $hasPendingEdit = collect($editingAttendance)->filter(function($change) use ($userId) {
                                                return ($change['user_id'] ?? null) == $userId;
                                            })->isNotEmpty();
                                            
                                            // Check if current user is the same as this row's user (prevent self-edit)
                                            $isSelf = (auth()->id() == $userId);
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition {{ $isEditing ? 'bg-amber-50' : '' }}">
                                            <td class="py-3 px-4">
                                                <div>
                                                    <p class="font-semibold text-slate-900 text-sm">[{{ $studentNumber }}] {{ $fullName }}</p>
                                                    @if($groupText)
                                                        <span class="text-xs text-slate-400">{{ $groupText }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            @foreach($weekendDates as $wd)
                                                @php
                                                    $ar = $attMap[$wd] ?? null;
                                                    $attendanceId = $ar?->attendance_id ?? 0;
                                                    
                                                    // Check if date is within review season
                                                    $isWithinSeason = !$reviewSeason || $reviewSeason->isDateWithinSeason($wd);
                                                    
                                                    if (!$isWithinSeason) {
                                                        // Outside review season = N/A (grey)
                                                        $cellClass = 'bg-slate-100 text-slate-400';
                                                        $cellText = 'N/A';
                                                        $timeInValue = '';
                                                    } elseif (!$ar) {
                                                        // Within season but no record = Absent (red)
                                                        $cellClass = 'bg-red-100 text-red-600';
                                                        $cellText = 'Absent';
                                                        $timeInValue = '';
                                                    } else {
                                                        // Has record - determine status
                                                        $cellClass = 'bg-red-100 text-red-600'; // default: absent
                                                        $cellText = 'Absent';
                                                        $timeInValue = '';
                                                        
                                                        // Check excused first (can be without time)
                                                        if (strtolower($ar->student_status ?? '') === 'excused') {
                                                            $cellClass = 'bg-blue-100 text-blue-600';
                                                            $cellText = $ar->attendance_time 
                                                                ? \Carbon\Carbon::parse($ar->attendance_time)->format('g:i') 
                                                                : 'E';
                                                            $timeInValue = $ar->attendance_time 
                                                                ? \Carbon\Carbon::parse($ar->attendance_time)->format('H:i') 
                                                                : '';
                                                        } elseif (strtolower($ar->student_status ?? '') === 'n/a') {
                                                            // Explicitly set to N/A
                                                            $cellClass = 'bg-slate-100 text-slate-400';
                                                            $cellText = 'N/A';
                                                        } elseif ($ar->attendance_time) {
                                                            $timeIn = \Carbon\Carbon::parse($ar->attendance_time);
                                                            $timeText = $timeIn->format('g:i');
                                                            $timeInValue = $timeIn->format('H:i');
                                                            
                                                            // Check student_status enum first, fallback to time-based logic
                                                            if (strtolower($ar->student_status ?? '') === 'late') {
                                                                $cellClass = 'bg-yellow-100 text-yellow-600';
                                                                $cellText = $timeText;
                                                            } elseif (strtolower($ar->student_status ?? '') === 'on time') {
                                                                $cellClass = 'bg-green-100 text-green-600';
                                                                $cellText = $timeText;
                                                            } else {
                                                                // Fallback: calculate based on time (AM > 8:15, PM > 12:15)
                                                                $sessionLower = strtolower($ar->session ?? 'am');
                                                                $isLate = false;
                                                                if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                                                                    $isLate = true;
                                                                } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                                                                    $isLate = true;
                                                                }
                                                                
                                                                if ($isLate) {
                                                                    $cellClass = 'bg-yellow-100 text-yellow-600';
                                                                } else {
                                                                    $cellClass = 'bg-green-100 text-green-600';
                                                                }
                                                                $cellText = $timeText;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <td class="py-3 px-2 text-center">
                                                    @if($isEditing && in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isWithinSeason)
                                                        <input 
                                                            type="time" 
                                                            value="{{ $timeInValue }}"
                                                            class="text-[10px] text-slate-600 border border-slate-200 rounded px-1 py-0.5 w-20 focus:ring-1 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime({{ $attendanceId }}, {{ $userId }}, $event.target.value, '{{ addslashes($fullNameWithTitles) }}', '{{ $wd }}')"
                                                        >
                                                    @else
                                                        <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $cellClass }}">
                                                            {{ $cellText }}
                                                        </span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            @if(in_array(auth()->user()->role_id ?? 0, [1, 2]))
                                                <td class="py-3 px-4 text-center">
                                                    @if($isEditing)
                                                        {{-- In edit mode: show save (check) and cancel (x) --}}
                                                        <button 
                                                            type="button" 
                                                            wire:click="prepareConfirmSave({{ $userId }}, '{{ addslashes($fullNameWithTitles) }}')"
                                                            class="text-green-600 hover:text-green-800 transition"
                                                            title="Save changes"
                                                        >
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelEditing({{ $userId }})"
                                                            class="text-red-400 hover:text-red-600 transition ml-2"
                                                            title="Cancel changes"
                                                        >
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    @else
                                                        {{-- Not in edit mode: show edit (pencil) for everyone including self --}}
                                                        <button 
                                                            type="button" 
                                                            wire:click="startEditing({{ $userId }})"
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
                            <h4 class="text-sm font-semibold text-slate-700 mb-4">Monthly Attendance Breakdown</h4>
                            
                            @if(!empty($monthlyAnalytics) && count($monthlyAnalytics) > 0)
                                @php
                                    $monthLabels = [];
                                    $monthOnTime = [];
                                    $monthLate = [];
                                    $monthAbsent = [];
                                    $monthExcused = [];
                                    
                                    foreach ($monthlyAnalytics as $mData) {
                                        $monthDays = $mData['total_days'] ?? 1;
                                        
                                        $monthLabels[] = $mData['label'] ?? '';
                                        // Calculate AVERAGE per day in this month
                                        $monthOnTime[] = $monthDays > 0 ? round(($mData['on_time'] ?? 0) / $monthDays, 1) : 0;
                                        $monthLate[] = $monthDays > 0 ? round(($mData['late'] ?? 0) / $monthDays, 1) : 0;
                                        $monthAbsent[] = $monthDays > 0 ? round(($mData['absent'] ?? 0) / $monthDays, 1) : 0;
                                        $monthExcused[] = $monthDays > 0 ? round(($mData['excused'] ?? 0) / $monthDays, 1) : 0;
                                    }
                                    
                                    $monthlyChartData = [
                                        'labels' => $monthLabels,
                                        'onTime' => $monthOnTime,
                                        'late' => $monthLate,
                                        'absent' => $monthAbsent,
                                        'excused' => $monthExcused,
                                    ];
                                @endphp
                                
                                <div wire:ignore
                                    wire:key="student-monthly-chart-{{ $session }}"
                                    x-data="{
                                        monthlyChart: null,
                                        chartData: @js($monthlyChartData),
                                        init() {
                                            this.$nextTick(() => {
                                                if (this.monthlyChart) this.monthlyChart.destroy();
                                                
                                                const ctx = this.$refs.monthlyCanvas;
                                                if (ctx && window.Chart) {
                                                    this.monthlyChart = new Chart(ctx, {
                                                        type: 'bar',
                                                        data: {
                                                            labels: this.chartData.labels,
                                                            datasets: [
                                                                {
                                                                    label: 'On Time',
                                                                    data: this.chartData.onTime,
                                                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                                                    borderColor: 'rgb(34, 197, 94)',
                                                                    borderWidth: 1
                                                                },
                                                                {
                                                                    label: 'Late',
                                                                    data: this.chartData.late,
                                                                    backgroundColor: 'rgba(234, 179, 8, 0.8)',
                                                                    borderColor: 'rgb(234, 179, 8)',
                                                                    borderWidth: 1
                                                                },
                                                                {
                                                                    label: 'Excused',
                                                                    data: this.chartData.excused,
                                                                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                                                    borderColor: 'rgb(59, 130, 246)',
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
                                                                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Average Students per Day', font: { size: 11 } } }
                                                            },
                                                            plugins: {
                                                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 10 } } }
                                                            }
                                                        }
                                                    });
                                                }
                                            });
                                        }
                                    }"
                                >
                                    <div class="h-80">
                                        <canvas x-ref="monthlyCanvas"></canvas>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-slate-400 mt-3">* Stacked bar chart showing AVERAGE attendance per day across all months in the review season.</p>
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
                        Dates outside this range will show as <span class="font-semibold">N/A</span> for students.
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
