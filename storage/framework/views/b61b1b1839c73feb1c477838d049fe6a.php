

<div class="attendance-page" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="attendance-wrapper">
        <?php
            // Calendar calculations
            $calYear = $calendarYear ?? now()->year;
            $calMonth = $calendarMonth ?? now()->month;
            $daysInMonth = \Carbon\Carbon::create($calYear, $calMonth, 1)->daysInMonth;
            $firstDayOfWeek = \Carbon\Carbon::create($calYear, $calMonth, 1)->dayOfWeek;
            $calMonthLabel = \Carbon\Carbon::create($calYear, $calMonth, 1)->format('F Y');

            // Selected date info
            $selectedDateObj = $selectedDate ? \Carbon\Carbon::parse($selectedDate) : now();
            $selectedDateLabel = $selectedDateObj->format('F j, Y');

            // Matrix month label
            $mYear = $matrixYear ?? now()->year;
            $mMonth = $matrixMonth ?? now()->month;
            $matrixMonthLabel = \Carbon\Carbon::create($mYear, $mMonth, 1)->format('F Y');

            // Build attendance map by date+session for monthly view filtering
            $attMap = collect($attendanceRecords)->keyBy(function($r) {
                return \Carbon\Carbon::parse($r->date)->format('Y-m-d') . '_' . $r->session;
            });

            // Session-filtered map for students monthly view (keyed by date only)
            $sessionAttMap = collect($attendanceRecords)->filter(function($r) use ($session) {
                return $r->session === $session;
            })->keyBy(function($r) {
                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
            });

            // For volunteers: group ALL records by date (no session filter) to get time in (first) and time out (second)
            $volunteerAttGrouped = collect($attendanceRecords)->groupBy(function($r) {
                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
            });

            // Filter for selected date
            $selectedAttendance = collect($attendanceRecords)->filter(function($r) use ($selectedDateObj) {
                return \Carbon\Carbon::parse($r->date)->format('Y-m-d') === $selectedDateObj->format('Y-m-d');
            });

            // Stats calculation
            $totalRecords = count($attendanceRecords);
            $presentCount = collect($attendanceRecords)->filter(function($r) {
                return $r->attendance_time && !$r->is_late && !$r->letter_id;
            })->count();
            $lateCount = collect($attendanceRecords)->filter(function($r) {
                return $r->is_late && !$r->letter_id;
            })->count();
            $excusedCount = collect($attendanceRecords)->filter(function($r) {
                return $r->letter_id;
            })->count();

            // Current attendance for selected date and session (for students)
            $currentAttendanceId = null;
            if ($user->role_id == 4) {
                $currentAttendance = $sessionAttMap[$selectedDateObj->format('Y-m-d')] ?? null;
                $currentAttendanceId = $currentAttendance ? $currentAttendance->attendance_id : null;
            }
        ?>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            
            <div class="px-5 pt-5 pb-3">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900"><?php echo e($fullName); ?></h1>
                        <p class="text-sm text-slate-500 mt-1">
                            <!--[if BLOCK]><![endif]--><?php if(!empty($monthlyView) && $monthlyView): ?>
                                <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                                    <?php echo e($matrixMonthLabel); ?> Attendance (<?php echo e(strtoupper($session)); ?>)
                                <?php else: ?>
                                    <?php echo e($matrixMonthLabel); ?> Attendance
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <?php else: ?>
                                <?php echo e($selectedDateLabel); ?>

                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                            
                            <button type="button" wire:click="setSession('am')" class="p-2 rounded-md transition <?php echo e($session === 'am' ? 'bg-amber-100 text-amber-500' : 'text-slate-300 hover:text-slate-400'); ?>" title="AM Session">
                                <i class="fa fa-sun-o text-lg"></i>
                            </button>
                            <button type="button" wire:click="setSession('pm')" class="p-2 rounded-md transition <?php echo e($session === 'pm' ? 'bg-indigo-100 text-indigo-500' : 'text-slate-300 hover:text-slate-400'); ?>" title="PM Session">
                                <i class="fa fa-moon-o text-lg"></i>
                            </button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        <!--[if BLOCK]><![endif]--><?php if(!empty($monthlyView) && $monthlyView): ?>
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click="toggleMonthlyView">Daily view</button>
                        <?php else: ?>
                            <button type="button" class="text-sm text-slate-500 hover:text-slate-700 transition" wire:click="toggleMonthlyView">Monthly view</button>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            </div>

            
            <div class="px-5 pb-4">
                <div class="flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm">
                    <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-widest text-slate-400">Student #</span>
                            <span class="font-semibold text-slate-700"><?php echo e($user->fceerProfile->student_number ?? 'N/A'); ?></span>
                        </div>
                        <div class="h-4 border-l border-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-widest text-slate-400">Batch</span>
                            <span class="font-medium text-slate-600"><?php echo e($user->fceerProfile->fceer_batch ?? 'N/A'); ?></span>
                        </div>
                        <div class="h-4 border-l border-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-widest text-slate-400">Group</span>
                            <span class="font-medium text-slate-600"><?php echo e($user->fceerProfile->group->group_name ?? 'N/A'); ?></span>
                        </div>
                        <div class="h-4 border-l border-slate-200"></div>
                        <div class="ml-auto flex items-center gap-2">
                            <a href="<?php echo e(route('student.excuse-letters.index', $user->id)); ?>" class="flex items-center gap-2 text-slate-600 hover:text-slate-800 transition">
                                <span class="text-[10px] uppercase tracking-widest">Excuse Letters</span>
                                <i class="fa fa-envelope text-sm"></i>
                            </a>
                            <!--[if BLOCK]><![endif]--><?php if($currentAttendanceId): ?>
                                <button type="button" wire:click="redirectToCreateExcuse(<?php echo e($currentAttendanceId); ?>, '<?php echo e($session); ?>')" class="gear-button text-slate-800" title="Add Excuse Letter">
                                    <i class="fa fa-plus"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" wire:click="redirectToCreateExcuse(0, '<?php echo e($session); ?>')" class="gear-button text-slate-800" title="Add Excuse Letter">
                                    <i class="fa fa-plus"></i>
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php elseif(in_array($user->role_id, [1, 2, 3])): ?>
                        
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-widest text-slate-400">Volunteer #</span>
                            <span class="font-semibold text-slate-700"><?php echo e($user->fceerProfile->volunteer_number ?? 'N/A'); ?></span>
                        </div>
                        <div class="h-4 border-l border-slate-200"></div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] uppercase tracking-widest text-slate-400">Batch</span>
                            <span class="font-medium text-slate-600"><?php echo e($user->fceerProfile->fceer_batch ?? 'N/A'); ?></span>
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>

            
            <!--[if BLOCK]><![endif]--><?php if(in_array($user->role_id, [1, 2, 3]) && $committees->count() > 0): ?>
                <div class="px-5 pb-4">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-widest text-slate-400 mb-2">Committees & Positions</p>
                        <div class="flex flex-wrap gap-2">
                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $committees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membership): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1 text-xs text-slate-600 border border-slate-200">
                                    <span class="font-semibold"><?php echo e($membership->committee->committee_name ?? 'N/A'); ?></span>
                                    <span class="text-slate-400"></span>
                                    <span><?php echo e($membership->position->position_name ?? 'N/A'); ?></span>
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if(empty($monthlyView) || !$monthlyView): ?>
                
                <?php
                    // Filter attendance records for selected date only
                    $selectedDateStr = $selectedDateObj->format('Y-m-d');
                    $dailyRecords = collect($attendanceRecords)->filter(function($r) use ($selectedDateStr) {
                        return \Carbon\Carbon::parse($r->date)->format('Y-m-d') === $selectedDateStr;
                    })->sortBy('attendance_time')->values();
                    
                    // For volunteers: first record = time in, second = time out
                    $firstRecord = $dailyRecords->first();
                    $secondRecord = $dailyRecords->count() > 1 ? $dailyRecords->get(1) : null;
                    
                    // Check if selected date is a weekend (Sat/Sun) and within review season
                    $isWeekend = $selectedDateObj->isSaturday() || $selectedDateObj->isSunday();
                    $isDateWithinSeason = !$reviewSeason || $reviewSeason->isDateWithinSeason($selectedDateObj);
                    
                    // If weekday OR outside review season, all statuses should be N/A
                    $showAsNA = !$isWeekend || !$isDateWithinSeason;
                ?>
                <div class="px-5 pb-5">
                    <!--[if BLOCK]><![endif]--><?php if($showAsNA): ?>
                        
                        <div class="mb-4 p-3 rounded-lg bg-slate-100 border border-slate-200 text-slate-600 text-sm flex items-center gap-2">
                            <i class="fa fa-info-circle"></i>
                            <!--[if BLOCK]><![endif]--><?php if(!$isWeekend): ?>
                                <span>This date is a weekday. Review sessions are only on weekends (Saturday/Sunday).</span>
                            <?php else: ?>
                                <span>This date is outside the current review season (<?php echo e($reviewSeason?->range_label ?? 'not set'); ?>).</span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    <div class="grid grid-cols-[1fr_300px] gap-5">
                        
                        <div class="rounded-lg border border-slate-200 bg-slate-50/50 overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                                        
                                        <?php
                                            // Check if currently in edit mode for this user
                                            $isEditing = isset($editingRow[$user->id]);
                                            // Check if current logged-in user is viewing their own page (prevent self-edit)
                                            $isSelf = (auth()->id() == $user->id);
                                            
                                            // Build both AM and PM sessions for display
                                            $sessions = ['am', 'pm'];
                                            $sessionRecords = [];
                                            foreach ($sessions as $sess) {
                                                $record = $dailyRecords->first(function($r) use ($sess) {
                                                    return strtolower($r->session ?? '') === $sess;
                                                });
                                                $sessionRecords[$sess] = $record;
                                            }
                                        ?>
                                        <thead>
                                            <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                                <th class="py-3 px-4">Session</th>
                                                <th class="py-3 px-4">Time In</th>
                                                <th class="py-3 px-4">Status</th>
                                                <th class="py-3 px-4">Letter</th>
                                                <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                                    <th class="py-3 px-4">Actions</th>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sess): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php
                                                    $record = $sessionRecords[$sess];
                                                    $attendanceId = $record->attendance_id ?? 0;
                                                    
                                                    $timeInDisplay = $record && $record->attendance_time 
                                                        ? \Carbon\Carbon::parse($record->attendance_time)->format('g:i A') 
                                                        : '—';
                                                    $timeInValue = $record && $record->attendance_time 
                                                        ? \Carbon\Carbon::parse($record->attendance_time)->format('H:i') 
                                                        : '';
                                                    
                                                    // Determine status
                                                    $statusValue = 'absent';
                                                    $statusText = 'Absent';
                                                    $statusClass = 'bg-red-100 text-red-600';
                                                    
                                                    // If weekday or outside review season, force N/A
                                                    if ($showAsNA) {
                                                        $statusValue = 'n/a';
                                                        $statusText = 'N/A';
                                                        $statusClass = 'bg-slate-100 text-slate-400';
                                                        $timeInDisplay = '—';
                                                    } elseif ($record) {
                                                        // Check excused first (can be without time)
                                                        if (strtolower($record->student_status ?? '') === 'excused') {
                                                            $statusValue = 'excused';
                                                            $statusText = 'Excused';
                                                            $statusClass = 'bg-blue-100 text-blue-600';
                                                        } elseif ($record->attendance_time) {
                                                            $timeIn = \Carbon\Carbon::parse($record->attendance_time);
                                                            $sessionLower = strtolower($record->session ?? 'am');
                                                            $isLateCalc = false;
                                                            if ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') {
                                                                $isLateCalc = true;
                                                            } elseif ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15') {
                                                                $isLateCalc = true;
                                                            }
                                                            
                                                            if (strtolower($record->student_status ?? '') === 'late' || $isLateCalc) {
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
                                                    
                                                    // Check for pending status change (only if not N/A)
                                                    if (!$showAsNA) {
                                                        $pendingKey = $attendanceId ?: "new_{$user->id}_{$selectedDate}_{$sess}";
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
                                                ?>
                                                <tr class="hover:bg-slate-50 transition <?php echo e($isEditing ? 'bg-amber-50' : ''); ?>">
                                                    <td class="py-3 px-4 font-semibold text-slate-900"><?php echo e(strtoupper($sess)); ?></td>
                                                    <td class="py-3 px-4">
                                                        <?php if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA): ?>
                                                            <input 
                                                                type="time" 
                                                                value="<?php echo e($timeInValue); ?>"
                                                                class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                                wire:change="updateStudentTime(<?php echo e($attendanceId); ?>, $event.target.value, '<?php echo e($selectedDate); ?>', '<?php echo e($sess); ?>')"
                                                            >
                                                        <?php else: ?>
                                                            <span class="text-slate-600"><?php echo e($timeInDisplay); ?></span>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <?php if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing && !$showAsNA): ?>
                                                            <select 
                                                                class="appearance-none rounded-full px-2.5 py-1 text-[10px] font-semibold border-0 cursor-pointer focus:ring-2 focus:ring-slate-300 <?php echo e($statusClass); ?>"
                                                                wire:change="updateAttendanceStatus(<?php echo e($attendanceId); ?>, $event.target.value, '<?php echo e($selectedDate); ?>', '<?php echo e($sess); ?>')"
                                                            >
                                                                <option value="on time" <?php echo e($statusValue === 'on time' ? 'selected' : ''); ?>>On Time</option>
                                                                <option value="late" <?php echo e($statusValue === 'late' ? 'selected' : ''); ?>>Late</option>
                                                                <option value="excused" <?php echo e($statusValue === 'excused' ? 'selected' : ''); ?>>Excused</option>
                                                                <option value="absent" <?php echo e($statusValue === 'absent' ? 'selected' : ''); ?>>Absent</option>
                                                            </select>
                                                        <?php else: ?>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold <?php echo e($statusClass); ?>">
                                                                <?php echo e($statusText); ?>

                                                            </span>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <!--[if BLOCK]><![endif]--><?php if($record && $record->excuseLetter): ?>
                                                            <?php
                                                                $letterStatus = $record->excuseLetter->status === 'approved' ? 'Received' : ucfirst($record->excuseLetter->status);
                                                                $letterClass = match(strtolower($record->excuseLetter->status)) {
                                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                                    'approved' => 'bg-green-100 text-green-800',
                                                                    default => 'bg-gray-100 text-gray-800'
                                                                };
                                                            ?>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold <?php echo e($letterClass); ?>">
                                                                <?php echo e($letterStatus); ?>

                                                            </span>
                                                        <?php else: ?>
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-semibold bg-gray-100 text-gray-800">
                                                                n/a
                                                            </span>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                    <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                                        <td class="py-3 px-4">
                                                            <!--[if BLOCK]><![endif]--><?php if($showAsNA): ?>
                                                                
                                                                <span class="text-slate-300">—</span>
                                                            <?php elseif($isEditing): ?>
                                                                
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="prepareConfirmSave"
                                                                    class="text-green-600 hover:text-green-800 transition"
                                                                    title="Save changes"
                                                                >
                                                                    <i class="fa fa-check"></i>
                                                                </button>
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="cancelEditing"
                                                                    class="text-red-400 hover:text-red-600 transition ml-2"
                                                                    title="Cancel changes"
                                                                >
                                                                    <i class="fa fa-times"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                
                                                                <button 
                                                                    type="button" 
                                                                    wire:click="startEditing"
                                                                    class="text-slate-400 hover:text-slate-600 transition"
                                                                    title="Edit attendance"
                                                                >
                                                                    <i class="fa fa-pencil"></i>
                                                                </button>
                                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                        </td>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        </tbody>
                                    <?php else: ?>
                                        
                                        <?php
                                            // Check if currently in edit mode for this user
                                            $isEditing = isset($editingRow[$user->id]);
                                            // Check if current logged-in user is viewing their own page (prevent self-edit)
                                            $isSelf = (auth()->id() == $user->id);
                                        ?>
                                        <thead>
                                            <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                                <th class="py-3 px-4">Time In</th>
                                                <th class="py-3 px-4">Time Out</th>
                                                <th class="py-3 px-4">Total Time</th>
                                                <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                                    <th class="py-3 px-4">Actions</th>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <?php
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
                                            ?>
                                            
                                            <tr class="hover:bg-slate-50 transition <?php echo e($isEditing ? 'bg-amber-50' : ''); ?>">
                                                <td class="py-3 px-4">
                                                    <?php if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing): ?>
                                                        <input 
                                                            type="time" 
                                                            value="<?php echo e($timeInValue); ?>"
                                                            class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime(<?php echo e($firstRecordId); ?>, 'time_in', $event.target.value, '<?php echo e($selectedDate); ?>')"
                                                        >
                                                    <?php else: ?>
                                                        <span class="text-slate-600"><?php echo e($timeInDisplay); ?></span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                                <td class="py-3 px-4">
                                                    <?php if(in_array(auth()->user()->role_id ?? 0, [1, 2]) && $isEditing): ?>
                                                        <input 
                                                            type="time" 
                                                            value="<?php echo e($timeOutValue); ?>"
                                                            class="text-sm text-slate-600 border border-slate-200 rounded px-2 py-1 w-24 focus:ring-2 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateAttendanceTime(<?php echo e($firstRecordId); ?>, 'time_out', $event.target.value, '<?php echo e($selectedDate); ?>')"
                                                        >
                                                    <?php else: ?>
                                                        <span class="text-slate-600"><?php echo e($timeOutDisplay); ?></span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                                <td class="py-3 px-4 text-slate-600"><?php echo e($totalTimeDisplay); ?></td>
                                                <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                                    <td class="py-3 px-4">
                                                        <!--[if BLOCK]><![endif]--><?php if($isEditing): ?>
                                                            
                                                            <button 
                                                                type="button" 
                                                                wire:click="prepareConfirmSave"
                                                                class="text-green-600 hover:text-green-800 transition"
                                                                title="Save changes"
                                                            >
                                                                <i class="fa fa-check"></i>
                                                            </button>
                                                            <button 
                                                                type="button" 
                                                                wire:click="cancelEditing"
                                                                class="text-red-400 hover:text-red-600 transition ml-2"
                                                                title="Cancel changes"
                                                            >
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            
                                                            <button 
                                                                type="button" 
                                                                wire:click="startEditing"
                                                                class="text-slate-400 hover:text-slate-600 transition"
                                                                title="Edit attendance"
                                                            >
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                    </td>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </tr>
                                        </tbody>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </table>
                            </div>
                        </div>

                        
                        <div class="space-y-4">
                            
                            <div class="rounded-lg border border-slate-200 bg-white p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-semibold text-slate-900"><?php echo e($calMonthLabel); ?></span>
                                    <div class="flex gap-1">
                                        <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click="prevCalendarMonth">&lsaquo;</button>
                                        <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click="nextCalendarMonth">&rsaquo;</button>
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

                                    <!--[if BLOCK]><![endif]--><?php for($i = 0; $i < $firstDayOfWeek; $i++): ?>
                                        <div></div>
                                    <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->

                                    <!--[if BLOCK]><![endif]--><?php for($i = 1; $i <= $daysInMonth; $i++): ?>
                                        <?php
                                            $dayDate = \Carbon\Carbon::create($calYear, $calMonth, $i)->format('Y-m-d');
                                            $isSelected = $selectedDate && (\Carbon\Carbon::parse($selectedDate)->toDateString() === $dayDate);
                                            $hasRecord = $attMap->has($dayDate);
                                        ?>
                                        <div
                                            class="h-8 flex items-center justify-center text-sm rounded cursor-pointer transition <?php echo e($isSelected ? 'bg-slate-700 text-white font-semibold' : ($hasRecord ? 'bg-slate-200 text-slate-700 font-medium' : 'text-slate-600 hover:bg-slate-100')); ?>"
                                            wire:click="setDate('<?php echo e($dayDate); ?>')"
                                        >
                                            <?php echo e($i); ?>

                                        </div>
                                    <?php endfor; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                <div class="text-right text-xs text-slate-400 mt-3"><?php echo e($calMonthLabel); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

            <!--[if BLOCK]><![endif]--><?php if(!empty($monthlyView) && $monthlyView): ?>
                
                <div class="px-5 pb-5">
                    <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                        <div class="flex justify-between items-center mb-4">
                            <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                                <span class="text-base font-semibold text-slate-900"><?php echo e($matrixMonthLabel); ?> (<?php echo e(strtoupper($session)); ?>)</span>
                            <?php else: ?>
                                <span class="text-base font-semibold text-slate-900"><?php echo e($matrixMonthLabel); ?></span>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            <div class="flex gap-1">
                                <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click="prevMatrixMonth">&lsaquo;</button>
                                <button type="button" class="text-slate-400 hover:text-slate-600 px-2 py-1 text-sm" wire:click="nextMatrixMonth">&rsaquo;</button>
                            </div>
                        </div>

                        <?php
                            // Check if currently in edit mode for this user
                            $isEditing = isset($editingRow[$user->id]);
                            // Check if current logged-in user is viewing their own page
                            $isSelf = (auth()->id() == $user->id);
                        ?>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-[10px] uppercase tracking-widest text-slate-500 border-b border-slate-200">
                                        <th class="py-3 px-4">Date</th>
                                        <th class="py-3 px-4">Day</th>
                                        <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                                            <th class="py-3 px-4 text-center">Time In</th>
                                            <th class="py-3 px-4 text-center">Status</th>
                                        <?php else: ?>
                                            <th class="py-3 px-4">Time In</th>
                                            <th class="py-3 px-4">Time Out</th>
                                            <th class="py-3 px-4">Total Time</th>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                            <th class="py-3 px-4 text-center">Actions</th>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $weekendDates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $dtObj = \Carbon\Carbon::parse($wd);
                                            $isEditing = isset($editingRow[$user->id]) && $editingRow[$user->id];
                                            $isSelf = (auth()->id() == $user->id);
                                        ?>
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="py-3 px-4 font-medium text-slate-900"><?php echo e($dtObj->format('M j, Y')); ?></td>
                                            <td class="py-3 px-4 text-slate-600"><?php echo e($dtObj->format('l')); ?></td>
                                            <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                                                
                                                <?php
                                                    $ar = $sessionAttMap[$wd] ?? null;
                                                    
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
                                                        $cellClass = 'bg-red-100 text-red-600';
                                                        $cellText = 'Absent';
                                                        $timeInValue = '';
                                                        
                                                        // Check excused first (can be without time)
                                                        if (strtolower($ar->student_status ?? '') === 'excused') {
                                                            $cellClass = 'bg-blue-100 text-blue-600';
                                                            $cellText = $ar->attendance_time 
                                                                ? \Carbon\Carbon::parse($ar->attendance_time)->format('g:i A') 
                                                                : 'Excused';
                                                            $timeInValue = $ar->attendance_time 
                                                                ? \Carbon\Carbon::parse($ar->attendance_time)->format('H:i') 
                                                                : '';
                                                        } elseif (strtolower($ar->student_status ?? '') === 'n/a') {
                                                            // Explicitly set to N/A
                                                            $cellClass = 'bg-slate-100 text-slate-400';
                                                            $cellText = 'N/A';
                                                        } elseif ($ar->attendance_time) {
                                                            $timeIn = \Carbon\Carbon::parse($ar->attendance_time);
                                                            $timeText = $timeIn->format('g:i A');
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
                                                    
                                                    // Determine status display
                                                    $statusValue = 'absent';
                                                    $statusText = 'Absent';
                                                    $statusClass = 'bg-red-100 text-red-600';
                                                    
                                                    if (!$isWithinSeason) {
                                                        $statusValue = 'N/A';
                                                        $statusText = 'N/A';
                                                        $statusClass = 'bg-slate-100 text-slate-400';
                                                    } elseif ($ar) {
                                                        $st = strtolower($ar->student_status ?? '');
                                                        if ($st === 'n/a') {
                                                            $statusValue = 'N/A';
                                                            $statusText = 'N/A';
                                                            $statusClass = 'bg-slate-100 text-slate-400';
                                                        } elseif ($st === 'excused') {
                                                            $statusValue = 'excused';
                                                            $statusText = 'Excused';
                                                            $statusClass = 'bg-blue-100 text-blue-600';
                                                        } elseif ($st === 'late') {
                                                            $statusValue = 'late';
                                                            $statusText = 'Late';
                                                            $statusClass = 'bg-yellow-100 text-yellow-600';
                                                        } elseif ($st === 'on time') {
                                                            $statusValue = 'on time';
                                                            $statusText = 'On Time';
                                                            $statusClass = 'bg-green-100 text-green-600';
                                                        } elseif ($ar->attendance_time) {
                                                            // Fallback: calculate based on time
                                                            $timeIn = \Carbon\Carbon::parse($ar->attendance_time);
                                                            $sessionLower = strtolower($ar->session ?? 'am');
                                                            $isLateCalc = ($sessionLower === 'am' && $timeIn->format('H:i') > '08:15') || ($sessionLower === 'pm' && $timeIn->format('H:i') > '12:15');
                                                            if ($isLateCalc) {
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
                                                ?>
                                                
                                                <td class="py-3 px-4 text-center">
                                                    <!--[if BLOCK]><![endif]--><?php if($isEditing && $isWithinSeason): ?>
                                                        <input 
                                                            type="time" 
                                                            value="<?php echo e($timeInValue); ?>"
                                                            class="text-xs text-slate-600 border border-slate-200 rounded px-1 py-0.5 w-24 focus:ring-1 focus:ring-slate-300 focus:border-slate-400"
                                                            wire:change="updateStudentTime(<?php echo e($ar?->attendance_id ?? 0); ?>, $event.target.value, '<?php echo e($wd); ?>', '<?php echo e($session); ?>')"
                                                        >
                                                    <?php else: ?>
                                                        <span class="text-slate-600 text-xs"><?php echo e($cellText !== 'N/A' && $cellText !== 'Absent' && $timeInValue ? \Carbon\Carbon::parse($timeInValue)->format('g:i A') : '—'); ?></span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                                
                                                <td class="py-3 px-4 text-center">
                                                    <!--[if BLOCK]><![endif]--><?php if($isEditing && $isWithinSeason): ?>
                                                        <select 
                                                            class="appearance-none rounded-full px-2.5 py-1 text-[10px] font-semibold border-0 cursor-pointer focus:ring-2 focus:ring-slate-300 <?php echo e($statusClass); ?>"
                                                            wire:change="updateStudentStatus(<?php echo e($ar?->attendance_id ?? 0); ?>, $event.target.value, '<?php echo e($wd); ?>', '<?php echo e($session); ?>')"
                                                        >
                                                            <option value="on time" <?php echo e($statusValue === 'on time' ? 'selected' : ''); ?>>On Time</option>
                                                            <option value="late" <?php echo e($statusValue === 'late' ? 'selected' : ''); ?>>Late</option>
                                                            <option value="excused" <?php echo e($statusValue === 'excused' ? 'selected' : ''); ?>>Excused</option>
                                                            <option value="absent" <?php echo e($statusValue === 'absent' ? 'selected' : ''); ?>>Absent</option>
                                                            <option value="N/A" <?php echo e($statusValue === 'N/A' ? 'selected' : ''); ?>>N/A</option>
                                                        </select>
                                                    <?php else: ?>
                                                        <span class="inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold <?php echo e($statusClass); ?>">
                                                            <?php echo e($statusText); ?>

                                                        </span>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                            <?php else: ?>
                                                
                                                <?php
                                                    $dayRecords = $volunteerAttGrouped[$wd] ?? collect();
                                                    $timeInDisplay = '—';
                                                    $timeOutDisplay = '—';
                                                    $totalTimeDisplay = '—';
                                                    $firstRecord = null;
                                                    $secondRecord = null;
                                                    $timeInValue = '';
                                                    $timeOutValue = '';
                                                    
                                                    if ($dayRecords->count() > 0) {
                                                        // Sort by attendance_time to ensure correct order
                                                        $sorted = $dayRecords->sortBy('attendance_time')->values();
                                                        $firstRecord = $sorted->first();
                                                        $secondRecord = $sorted->count() > 1 ? $sorted->get(1) : null;
                                                        
                                                        $timeInDisplay = $firstRecord && $firstRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('g:i A') 
                                                            : '—';
                                                        $timeOutDisplay = $secondRecord && $secondRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('g:i A') 
                                                            : '—';
                                                        
                                                        $timeInValue = $firstRecord && $firstRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($firstRecord->attendance_time)->format('H:i') 
                                                            : '';
                                                        $timeOutValue = $secondRecord && $secondRecord->attendance_time 
                                                            ? \Carbon\Carbon::parse($secondRecord->attendance_time)->format('H:i') 
                                                            : '';
                                                        
                                                        // Calculate total time
                                                        if ($firstRecord && $firstRecord->attendance_time && $secondRecord && $secondRecord->attendance_time) {
                                                            $timeIn = \Carbon\Carbon::parse($firstRecord->attendance_time);
                                                            $timeOut = \Carbon\Carbon::parse($secondRecord->attendance_time);
                                                            $diffMinutes = $timeIn->diffInMinutes($timeOut);
                                                            $hours = floor($diffMinutes / 60);
                                                            $mins = $diffMinutes % 60;
                                                            $totalTimeDisplay = $hours . 'h ' . $mins . 'm';
                                                        }
                                                    }
                                                ?>
                                                <td class="py-3 px-4 text-slate-600">
                                                    <!--[if BLOCK]><![endif]--><?php if($isEditing): ?>
                                                        <input 
                                                            type="time" 
                                                            wire:change="updateAttendanceTime(<?php echo e($firstRecord?->attendance_id ?? 0); ?>, 'time_in', $event.target.value, '<?php echo e($wd); ?>')"
                                                            value="<?php echo e($timeInValue); ?>"
                                                            class="text-xs border border-slate-200 rounded px-2 py-1 w-24 focus:ring-slate-500 focus:border-slate-500"
                                                        />
                                                    <?php else: ?>
                                                        <?php echo e($timeInDisplay); ?>

                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                                <td class="py-3 px-4 text-slate-600">
                                                    <!--[if BLOCK]><![endif]--><?php if($isEditing): ?>
                                                        <input 
                                                            type="time" 
                                                            wire:change="updateAttendanceTime(<?php echo e($firstRecord?->attendance_id ?? 0); ?>, 'time_out', $event.target.value, '<?php echo e($wd); ?>')"
                                                            value="<?php echo e($timeOutValue); ?>"
                                                            class="text-xs border border-slate-200 rounded px-2 py-1 w-24 focus:ring-slate-500 focus:border-slate-500"
                                                        />
                                                    <?php else: ?>
                                                        <?php echo e($timeOutDisplay); ?>

                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                                <td class="py-3 px-4 text-slate-600"><?php echo e($totalTimeDisplay); ?></td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            
                                            <!--[if BLOCK]><![endif]--><?php if(in_array(auth()->user()->role_id ?? 0, [1, 2])): ?>
                                                <td class="py-3 px-4 text-center">
                                                    <!--[if BLOCK]><![endif]--><?php if($isEditing): ?>
                                                        <button 
                                                            type="button" 
                                                            wire:click="prepareConfirmSave"
                                                            class="text-green-600 hover:text-green-800 transition mr-2"
                                                            title="Save"
                                                        >
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="cancelEditing"
                                                            class="text-red-400 hover:text-red-600 transition"
                                                            title="Cancel"
                                                        >
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button 
                                                            type="button" 
                                                            wire:click="startEditing"
                                                            class="text-slate-400 hover:text-slate-600 transition"
                                                            title="Edit"
                                                        >
                                                            <i class="fa fa-pencil"></i>
                                                        </button>
                                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                                </td>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </tbody>
                            </table>
                        </div>

                        
                        <?php
                            // Check if logged-in user is exec (role_id 1)
                            $loggedInUser = Auth::user();
                            $isExec = $loggedInUser && $loggedInUser->role_id == 1;
                            
                            // For analytics, use the review season if set, otherwise use current month
                            $analyticsWeekendDates = $weekendDates; // default to current month
                            $analyticsRangeLabel = $matrixMonthLabel;
                            
                            if ($reviewSeason) {
                                // Use review season weekend dates
                                $analyticsWeekendDates = $reviewSeason->getWeekendDates();
                                $analyticsRangeLabel = $reviewSeason->range_label;
                            }
                            
                            // Build session-filtered map for entire analytics range (students)
                            $analyticsSessionAttMap = collect($attendanceRecords)->filter(function($r) use ($session) {
                                return strtolower($r->session) === strtolower($session);
                            })->keyBy(function($r) {
                                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
                            });
                            
                            // Build volunteer grouped map for analytics range
                            $analyticsVolunteerGrouped = collect($attendanceRecords)->groupBy(function($r) {
                                return \Carbon\Carbon::parse($r->date)->format('Y-m-d');
                            });
                            
                            // Calculate analytics based on weekend dates for the analytics range
                            $analyticsOnTime = 0;
                            $analyticsLate = 0;
                            $analyticsExcused = 0;
                            $analyticsAbsent = 0;
                            $analyticsNA = 0;
                            $analyticsTotalDays = count($analyticsWeekendDates);
                            $totalMinutesMonth = 0;
                            $totalDaysWithAttendance = 0;
                            
                            if ($user->role_id == 4) {
                                // Student analytics
                                foreach ($analyticsWeekendDates as $wd) {
                                    // Check if date is within review season
                                    $isWithinSeason = !$reviewSeason || $reviewSeason->isDateWithinSeason($wd);
                                    
                                    if (!$isWithinSeason) {
                                        $analyticsNA++; // Outside review season = N/A
                                        continue;
                                    }
                                    
                                    $ar = $analyticsSessionAttMap[$wd] ?? null;
                                    if ($ar) {
                                        $st = strtolower($ar->student_status ?? '');
                                        if ($st === 'on time') {
                                            $analyticsOnTime++;
                                        } elseif ($st === 'late') {
                                            $analyticsLate++;
                                        } elseif ($st === 'excused') {
                                            $analyticsExcused++;
                                        } elseif ($st === 'n/a') {
                                            $analyticsNA++;
                                        } else {
                                            // No status - check if has time in, else absent
                                            if ($ar->attendance_time) {
                                                // Auto-calculate based on time
                                                $timeIn = \Carbon\Carbon::parse($ar->attendance_time);
                                                $sessLower = strtolower($ar->session ?? 'am');
                                                $isLateCalc = ($sessLower === 'am' && $timeIn->format('H:i') > '08:15') || ($sessLower === 'pm' && $timeIn->format('H:i') > '12:15');
                                                if ($isLateCalc) $analyticsLate++;
                                                else $analyticsOnTime++;
                                            } else {
                                                $analyticsAbsent++;
                                            }
                                        }
                                    } else {
                                        // No record within season = Absent
                                        $analyticsAbsent++;
                                    }
                                }
                            } else {
                                // Volunteer analytics - calculate total time
                                foreach ($analyticsWeekendDates as $wd) {
                                    // Check if date is within review season
                                    $isWithinSeason = !$reviewSeason || $reviewSeason->isDateWithinSeason($wd);
                                    if (!$isWithinSeason) continue;
                                    
                                    $dayRecs = $analyticsVolunteerGrouped[$wd] ?? collect();
                                    if ($dayRecs->count() >= 2) {
                                        $sorted = $dayRecs->sortBy('attendance_time')->values();
                                        $firstRec = $sorted->first();
                                        $secondRec = $sorted->get(1);
                                        if ($firstRec && $secondRec && $firstRec->attendance_time && $secondRec->attendance_time) {
                                            $tIn = \Carbon\Carbon::parse($firstRec->attendance_time);
                                            $tOut = \Carbon\Carbon::parse($secondRec->attendance_time);
                                            $diffMins = $tOut->diffInMinutes($tIn);
                                            $totalMinutesMonth += $diffMins;
                                            $totalDaysWithAttendance++;
                                        }
                                    }
                                }
                            }
                            
                            // Format total time for volunteers
                            $totalHoursMonth = floor($totalMinutesMonth / 60);
                            $totalMinsRemainder = $totalMinutesMonth % 60;
                            $avgMinutesPerDay = $totalDaysWithAttendance > 0 ? round($totalMinutesMonth / $totalDaysWithAttendance) : 0;
                            $avgHours = floor($avgMinutesPerDay / 60);
                            $avgMins = $avgMinutesPerDay % 60;
                        ?>

                        
                        <!--[if BLOCK]><![endif]--><?php if($reviewSeason): ?>
                            <div class="flex items-center gap-3 mt-4 p-3 rounded-lg border border-indigo-200 bg-indigo-50">
                                <i class="fa fa-calendar-check-o text-indigo-500"></i>
                                <span class="text-xs font-medium text-indigo-700 uppercase tracking-wider">Review Season:</span>
                                <span class="text-sm font-semibold text-indigo-900"><?php echo e($reviewSeason->range_label); ?></span>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

                        <!--[if BLOCK]><![endif]--><?php if($user->role_id == 4): ?>
                            
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mt-4">
                                <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                                    <div class="text-2xl font-bold text-green-700"><?php echo e($analyticsOnTime); ?></div>
                                    <div class="text-xs text-green-600">On Time</div>
                                </div>
                                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
                                    <div class="text-2xl font-bold text-yellow-700"><?php echo e($analyticsLate); ?></div>
                                    <div class="text-xs text-yellow-600">Late</div>
                                </div>
                                <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                                    <div class="text-2xl font-bold text-red-700"><?php echo e($analyticsAbsent); ?></div>
                                    <div class="text-xs text-red-600">Absent</div>
                                </div>
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                    <div class="text-2xl font-bold text-blue-700"><?php echo e($analyticsExcused); ?></div>
                                    <div class="text-xs text-blue-600">Excused</div>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <div class="text-2xl font-bold text-slate-700"><?php echo e($analyticsNA); ?></div>
                                    <div class="text-xs text-slate-600">N/A</div>
                                </div>
                                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
                                    <div class="text-2xl font-bold text-indigo-700"><?php echo e($analyticsTotalDays); ?></div>
                                    <div class="text-xs text-indigo-600">Total Days</div>
                                </div>
                            </div>
                        <?php else: ?>
                            
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                                <div class="rounded-lg border border-indigo-200 bg-indigo-50 p-3">
                                    <div class="text-2xl font-bold text-indigo-700"><?php echo e($totalHoursMonth); ?>h <?php echo e($totalMinsRemainder); ?>m</div>
                                    <div class="text-xs text-indigo-600">Total Time (<?php echo e($reviewSeason ? $analyticsRangeLabel : 'This Month'); ?>)</div>
                                </div>
                                <div class="rounded-lg border border-green-200 bg-green-50 p-3">
                                    <div class="text-2xl font-bold text-green-700"><?php echo e($avgHours); ?>h <?php echo e($avgMins); ?>m</div>
                                    <div class="text-xs text-green-600">Avg Time/Day</div>
                                </div>
                                <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                                    <div class="text-2xl font-bold text-blue-700"><?php echo e($totalDaysWithAttendance); ?></div>
                                    <div class="text-xs text-blue-600">Days Attended</div>
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                    <div class="text-2xl font-bold text-slate-700"><?php echo e($analyticsTotalDays); ?></div>
                                    <div class="text-xs text-slate-600">Total Days</div>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>
    </div>

    
    <!--[if BLOCK]><![endif]--><?php if($showConfirmModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="cancelConfirmModal">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Confirm Changes</h3>
                </div>
                <div class="px-6 py-4">
                    <p class="text-sm text-slate-600">
                        Are you sure you want to modify the attendance record(s) for 
                        <span class="font-semibold text-slate-900"><?php echo e($confirmModalData['full_name'] ?? $fullName); ?></span>
                        on the following date(s)?
                    </p>
                    <ul class="mt-3 space-y-1">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $confirmModalData['dates'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dateStr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="text-sm text-slate-700 flex items-center gap-2">
                                <i class="fa fa-calendar text-slate-400"></i>
                                <?php echo e($dateStr); ?>

                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($showSelfEditError): ?>
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    
    <!--[if BLOCK]><![endif]--><?php if($showOutsideSeasonError): ?>
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
                        The date you are trying to set attendance for (<span class="font-semibold"><?php echo e($outsideSeasonErrorData['date'] ?? 'Unknown'); ?></span>) 
                        is outside the current review season (<span class="font-semibold"><?php echo e($outsideSeasonErrorData['range'] ?? 'Unknown'); ?></span>).
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
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div>
<?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/livewire/attendance/user.blade.php ENDPATH**/ ?>