@php
    $user = $user ?? ($this->user ?? auth()->user());
    $numberLabel = in_array($user->role_id, [1, 2, 3]) ? 'Volunteer Number' : 'Student Number';
    $numberValue = $fields['volunteer_number'] ?? $fields['student_number'] ?? '-';
    $groupLabel = $user->role_id === 4 ? 'Student Group' : 'Volunteer Batch';
    $groupValue = $user->role_id === 4 ? ($fields['student_group'] ?? '-') : ($fields['fceer_batch'] ?? '-');
@endphp

<div class="profile-component-card">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-lg font-semibold text-slate-900 flex items-center gap-2">
                <i class="fa fa-id-card"></i>
                FCEER Profile
            </p>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">{{ $user->role_id === 4 ? 'Student' : 'Volunteer' }}</p>
        </div>
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('fceer-profile.edit'))
            <div class="profile-actions">
                <a href="{{ route('fceer-profile.edit', ['user' => $user->id]) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Edit volunteer profile">
                    <i class="fa fa-cog" aria-hidden="true"></i>
                </a>
            </div>
        @endif
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">{{ $numberLabel }}</p>
                <p class="text-sm font-semibold text-slate-800">{{ $numberValue }}</p>
            </div>
            <div class="space-y-1 rounded border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-[10px] uppercase tracking-[0.3em] text-slate-500">{{ $groupLabel }}</p>
                <p class="text-sm font-semibold text-slate-800">{{ $groupValue }}</p>
            </div>
        </div>
    </div>
</div>
