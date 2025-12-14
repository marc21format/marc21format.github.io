@php
    $user = $user ?? ($this->user ?? auth()->user());
    $hsUserId = $userId ?? ($user->id ?? auth()->id());

    if (! isset($records)) {
        try {
            $records = \App\Models\HighschoolRecord::where('user_id', $hsUserId)
                ->orderBy('year_end', 'desc')
                ->paginate(10);
        } catch (\Throwable $e) {
            $records = collect();
        }
    }

    $roleLabel = optional($user->role)->role_title ?? ($user->role_id === 4 ? 'Student' : 'Volunteer');
    $p = $user->userProfile ?? null;
    $fullName = trim(collect([
        optional($p)->f_name,
        optional($p)->m_name,
        optional($p)->s_name,
        optional($p)->generational_suffix
    ])->filter()->implode(' '));
    if (!$fullName) $fullName = $user->name ?? $user->email ?? 'User';
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title flex items-center gap-2">
                <i class="fa fa-graduation-cap"></i>
                Highschool Records
            </p>
            <p class="profile-card-subtitle">Of {{ $fullName }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $hsUserId || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('users.highschool_records.create'))
                <a href="{{ route('users.highschool_records.create', $hsUserId) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Add highschool record"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-3">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($records as $record)
                @php $editUrl = route('users.highschool_records.edit', ['user' => $hsUserId, 'record' => $record->record_id]); @endphp
                <a href="{{ $editUrl }}" class="block">
                    <div class="space-y-1 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 hover:border-slate-200 transition">
                        <p class="text-base font-semibold text-slate-900">{{ optional($record->highschool)->highschool_name ?? '—' }}</p>
                           <p class="text-sm text-slate-600 font-medium">{{ $record->level }}</p>
                        <p class="text-xs text-slate-400">{{ $record->year_start }} - {{ $record->year_end }}</p>
                    </div>
                </a>
            @empty
                <div class="text-sm text-slate-500 py-4 col-span-full">No highschool records.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">
        {{ method_exists($records, 'links') ? $records->links() : '' }}
    </div>
</div>
