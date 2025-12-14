@php
    $user = $user ?? ($this->user ?? auth()->user());
    $userId = $userId ?? ($user->id ?? auth()->id());
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
                <i class="fa fa-university"></i>
                Educational Records
            </p>
            <p class="profile-card-subtitle">Of {{ $fullName }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $userId || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('educational-records.create'))
                <a href="{{ route('educational-records.create', $userId) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Add educational record"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="grid gap-3 sm:grid-cols-2">
            @forelse($records as $record)
                <a href="{{ route('educational-records.edit', ['id' => $record->record_id]) }}" class="block">
                    <div class="space-y-1 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 hover:border-slate-200 transition">
                        <p class="text-base font-semibold text-slate-900">
                            {{ optional($record->degreeProgram)->full_degree_program_name ?? '-' }}
                            @if($record->latin_honor)
                                <span class="text-sm font-normal text-slate-500">({{ $record->latin_honor }})</span>
                            @endif
                        </p>
                        <p class="text-sm text-slate-600">{{ optional($record->university)->university_name ?? '-' }}</p>
                        <p class="text-xs text-slate-400">{{ $record->year_graduated ?? '-' }}</p>
                        @if($record->scholarship)
                            <p class="text-xs text-slate-400">{{ $record->scholarship }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="text-sm text-slate-500 py-4 col-span-full">No records found.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>
</div>
