@php
    use Illuminate\Support\Str;

    $formatStudentDisplayName = function ($student) {
        $parts = [];
        $credential = $student->professionalCredentials->first();

        if ($credential?->prefix) {
            $parts[] = $credential->prefix->title;
        }
        if ($student->userProfile?->f_name) {
            $parts[] = $student->userProfile->f_name;
        }
        if ($student->userProfile?->m_name) {
            $parts[] = $student->userProfile->m_name;
        }
        if ($student->userProfile?->s_name) {
            $parts[] = $student->userProfile->s_name;
        }
        if (empty($parts)) {
            $parts[] = $student->name ?? '';
        }

        return trim(implode(' ', $parts));
    };

    $formatStudentNumber = function ($student) {
        return optional($student->fceerProfile)->student_number;
    };

    $attendanceDate = now()->format('m/d');
@endphp

<div class="space-y-3 rounded-xl border border-slate-200 bg-white shadow-sm" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="px-5 pt-5 pb-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h1 class="text-xl font-bold text-slate-900">Student Roster</h1>
            <p class="text-sm text-slate-500">Showing {{ $students->total() ?? $students->count() }} students</p>
        </div>
    </div>

    <div class="px-5 pb-3">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-yellow-50 p-2">
            <button 
                wire:click="openAddModal"
                class="shrink-0 px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
            >
                <i class="fa fa-plus mr-1"></i> Add Student
            </button>
            <select wire:model.live="filterField" class="w-40 shrink-0 appearance-none rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200" style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-position: right 0.5rem center; background-repeat: no-repeat; background-size: 1.5em 1.5em; padding-right: 2.5rem;">
                <option value="">All fields</option>
                <option value="name">Name</option>
                <option value="student_number">Student #</option>
                <option value="student_group">Group</option>
                <option value="highschool">Highschool</option>
            </select>
            <input
                type="text"
                wire:model.live.debounce.350ms="search"
                placeholder="Search students..."
                class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200"
            />
        </div>
    </div>

    <div class="flex justify-end gap-6 px-5 border-b border-slate-100 pb-3">
        @foreach(["all" => 'All', "groups" => 'Groups', "highschools" => 'Highschools'] as $tabKey => $tabLabel)
            <button
                type="button"
                wire:click="setTab('{{ $tabKey }}')"
                class="text-sm transition {{ ($activeTab ?? 'all') === $tabKey ? 'text-slate-900 font-semibold' : 'text-slate-400 hover:text-slate-700' }}"
            >
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    <div class="px-5">
        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
            @if(($activeTab ?? 'all') === 'all')
                @if($students->isEmpty())
                    <p class="text-xs text-slate-500">No students available yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="text-xs uppercase tracking-[0.3em] text-slate-500">
                                    <th class="py-2 pr-3">Student</th>
                                    <th class="px-2 py-2">Highschool</th>
                                    <th class="px-2 py-2">Group</th>
                                    <th class="px-2 py-2">Attendance ({{ $attendanceDate }})</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($students as $student)
                                    @php
                                        $displayName = $formatStudentDisplayName($student);
                                        $displayNumber = $formatStudentNumber($student);
                                        $statusRecord = $student->attendanceRecords->first() ?? null;
                                        $hsRecord = $student->highschoolRecords->first() ?? null;
                                        $hsName = $hsRecord?->highschool?->highschool_name ?? null;
                                        $hsLevel = $hsRecord?->level ?? null;
                                        $hsYearEnd = $hsRecord?->year_end ?? null;
                                        $groupName = optional(optional($student->fceerProfile)->group)->group ?? null;
                                    @endphp
                                    <tr class="group bg-white hover:bg-slate-50 transition">
                                        <td class="py-3 pr-3 pl-4 align-top">
                                            <a href="{{ route('profile.student.show', ['user' => $student->id]) }}" class="flex items-center gap-3">
                                                <div class="h-10 w-10 overflow-hidden rounded-full bg-slate-200">
                                                    <img
                                                        src="https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&size=40&background=cbd5e1&color=475569"
                                                        alt="{{ $displayName }} avatar"
                                                        class="h-full w-full object-cover"
                                                    />
                                                </div>
                                                <div class="text-sm">
                                                    <p class="font-semibold text-slate-800 hover:text-slate-600">{{ $displayName }}</p>
                                                    <p class="text-xs uppercase tracking-widest text-slate-400">
                                                        {{ $displayNumber ? '#'.$displayNumber : '' }}
                                                    </p>
                                                </div>
                                            </a>
                                        </td>
                                        <td class="px-2 py-3 align-top text-xs text-slate-600">
                                            @if($hsName)
                                                <div class="mb-1 rounded bg-white px-2 py-1">
                                                    <p class="font-semibold text-slate-800">{{ $hsName }}</p>
                                                    @if($hsLevel)
                                                        <p class="text-[10px] uppercase tracking-[0.2em] text-slate-500">{{ $hsLevel }}</p>
                                                    @endif
                                                    @if($hsYearEnd)
                                                        <p class="text-[10px] text-slate-400">{{ $hsYearEnd }}</p>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-slate-300">None</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-3 align-top text-xs text-slate-600">
                                            @if($groupName)
                                                <div class="mb-1 rounded bg-white px-2 py-1">
                                                    <p class="font-semibold text-slate-800">{{ $groupName }}</p>
                                                </div>
                                            @else
                                                <span class="text-slate-300">None</span>
                                            @endif
                                        </td>
                                        <td class="px-2 py-3 align-top">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-500">
                                                {{ optional($statusRecord)->status_label ?? optional($statusRecord)->status ?? 'No status' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @elseif(($activeTab ?? 'all') === 'groups')
                @php
                    $groups = $students->groupBy(function($s) {
                        return optional(optional($s->fceerProfile)->group)->group ?? 'Unassigned';
                    });
                @endphp
                @if($groups->isEmpty())
                    <p class="text-xs text-slate-500">No groups available yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($groups as $groupName => $groupStudents)
                            <article class="rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-base font-bold text-slate-900">{{ $groupName }}</p>
                                        <p class="text-sm text-slate-500">{{ $groupStudents->count() }} student{{ $groupStudents->count() === 1 ? '' : 's' }}</p>
                                    </div>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Group</span>
                                </div>
                                <div class="mt-3 space-y-3">
                                    @forelse($groupStudents as $student)
                                        @php
                                            $studentName = $formatStudentDisplayName($student);
                                            $studentNumber = $formatStudentNumber($student);
                                            $statusRecord = $student->attendanceRecords->first() ?? null;
                                        @endphp
                                        <a href="{{ route('profile.student.show', ['user' => $student->id]) }}" class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 transition">
                                            <div class="h-10 w-10 overflow-hidden rounded-full bg-slate-200">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&size=40&background=cbd5e1&color=475569" alt="{{ $studentName }} avatar" class="h-full w-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-slate-900">{{ $studentName }}</p>
                                                <p class="text-xs uppercase tracking-widest text-slate-400">Student</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-xs text-slate-400">{{ $studentNumber ? '#'.$studentNumber : '' }}</span>
                                                <span class="ml-2 inline-flex items-center rounded-full bg-slate-200 px-2.5 py-1 text-[10px] font-medium uppercase tracking-wide text-slate-600">
                                                    {{ optional($statusRecord)->status_label ?? optional($statusRecord)->status ?? 'No status' }}
                                                </span>
                                            </div>
                                        </a>
                                    @empty
                                        <p class="text-xs text-slate-500">No students in this group.</p>
                                    @endforelse
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            @else
                @php
                    $highschools = $students->groupBy(function($s) {
                        $rec = $s->highschoolRecords->first();
                        return $rec?->highschool?->highschool_name ?? 'Unknown';
                    });
                @endphp
                @if($highschools->isEmpty())
                    <p class="text-xs text-slate-500">No highschools available yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($highschools as $hsName => $hsStudents)
                            <article class="rounded-lg border border-slate-200 bg-white px-5 py-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-base font-bold text-slate-900">{{ $hsName }}</p>
                                        <p class="text-sm text-slate-500">{{ $hsStudents->count() }} student{{ $hsStudents->count() === 1 ? '' : 's' }}</p>
                                    </div>
                                    <span class="text-[10px] uppercase tracking-widest text-slate-400">Highschool</span>
                                </div>
                                <div class="mt-3 space-y-3">
                                    @forelse($hsStudents as $student)
                                        @php
                                            $studentName = $formatStudentDisplayName($student);
                                            $studentNumber = $formatStudentNumber($student);
                                            $hsRecord = $student->highschoolRecords->first();
                                            $hsLevel = $hsRecord?->level ?? null;
                                            $hsYearEnd = $hsRecord?->year_end ?? null;
                                        @endphp
                                        <a href="{{ route('profile.student.show', ['user' => $student->id]) }}" class="flex flex-wrap items-center gap-3 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 transition">
                                            <div class="h-10 w-10 overflow-hidden rounded-full bg-slate-200">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($studentName) }}&size=40&background=cbd5e1&color=475569" alt="{{ $studentName }} avatar" class="h-full w-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-slate-900">{{ $studentName }}</p>
                                                @if($hsLevel)
                                                    <p class="text-xs uppercase tracking-widest text-slate-500">{{ $hsLevel }}</p>
                                                @endif
                                                @if($hsYearEnd)
                                                    <p class="text-xs text-slate-400">{{ $hsYearEnd }}</p>
                                                @endif
                                            </div>
                                            <span class="text-xs text-slate-400">{{ $studentNumber ? '#'.$studentNumber : '' }}</span>
                                        </a>
                                    @empty
                                        <p class="text-xs text-slate-500">No students from this highschool.</p>
                                    @endforelse
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        <div class="mt-4 text-right text-sm text-slate-500">
            Showing {{ $students->count() }} of {{ $students->total() ?? $students->count() }} students
        </div>
    </div>

    <div class="px-5 pb-5">
        {{ $students->links() }}
    </div>
    
    {{-- Add Student Modal --}}
    @if($showAddModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="closeAddModal">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Add New Student</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                        <input 
                            type="text" 
                            wire:model="newUsername"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter username"
                        />
                        @error('newUsername') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input 
                            type="email" 
                            wire:model="newEmail"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter email"
                        />
                        @error('newEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                        <input 
                            type="password" 
                            wire:model="newPassword"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter password (min. 6 characters)"
                        />
                        @error('newPassword') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <p class="text-xs text-slate-500">Role will be automatically set to <strong>Student</strong></p>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 flex justify-end gap-2">
                    <button 
                        wire:click="closeAddModal"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-md transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="saveNewStudent"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
                    >
                        Create Student
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
