@php
    $user = $user ?? ($this->user ?? auth()->user());
    $hsUserId = isset($userId) ? $userId : ($user->id ?? auth()->id());

    if (! isset($subjects)) {
        try {
            $subjects = \App\Models\HighschoolSubjectRecord::with('subject')
                ->where('user_id', $hsUserId)
                ->orderBy('record_id', 'desc')
                ->paginate(10);
        } catch (\Throwable $e) {
            $subjects = collect();
        }
    }

    $roleLabel = 'Student';
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
                <i class="fa fa-book-open"></i>
                Highschool Subjects
            </p>
            <p class="profile-card-subtitle">Of {{ $fullName }}</p>
        </div>
        <div class="profile-card-actions">
            @if(auth()->check() && (auth()->id() === $hsUserId || auth()->user()->isAdmin() || auth()->user()->isExecutive()) && Route::has('profile.highschool_subject_records.create'))
                <a href="{{ route('profile.highschool_subject_records.create', $hsUserId) }}" class="btn btn-ghost btn-sm gear-button" aria-label="Add subject record"><i class="fa fa-plus" aria-hidden="true"></i></a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-3">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($subjects ?? [] as $s)
                @php $editUrl = route('users.highschool_subject_records.edit', ['user' => $hsUserId, 'record' => $s->record_id ?? $s->id]); @endphp
                <a href="{{ $editUrl }}" class="block">
                    <div class="space-y-1 rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100 hover:border-slate-200 transition">
                        <p class="text-base font-semibold text-slate-900">{{ optional($s->subject)->subject_name ?? '—' }}</p>
                        <p class="text-sm text-slate-400">Grade: {{ $s->grade ?? '—' }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-sm text-slate-500 py-4">No highschool subjects.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-4">{{ method_exists($subjects ?? collect(), 'links') ? $subjects->links() : '' }}</div>
</div>
