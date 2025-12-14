@php
    $user = $user ?? ($teacher->user ?? ($this->user ?? (isset($userId) ? \App\Models\User::find($userId) : auth()->user())));
    $p = $user->userProfile ?? null;
    $cred = $user->professionalCredentials && $user->professionalCredentials->count() ? $user->professionalCredentials->first() : null;
    $prefixTitle = optional(optional($cred)->prefix)->title;
    $suffixTitle = optional(optional($cred)->suffix)->title;

    $nameParts = [];
    if ($prefixTitle) $nameParts[] = $prefixTitle;
    if ($p && ($p->f_name || $p->m_name || $p->s_name)) {
        if ($p->f_name) $nameParts[] = $p->f_name;
        if ($p->m_name) $nameParts[] = $p->m_name;
        if ($p->s_name) $nameParts[] = $p->s_name;
        if (!empty($p->generational_suffix)) $nameParts[] = $p->generational_suffix;
    } else {
        $nameParts[] = $user->name ?? $user->email ?? 'User';
    }

    $displayName = trim(implode(' ', $nameParts));
    if ($suffixTitle) $displayName .= ($displayName ? ', ' : '') . $suffixTitle;

    $cancelRoute = route('profile.volunteer.show', ['user' => $user->id ?? ($teacher->user_id ?? ($userId ?? auth()->id()))]);
@endphp

<div class="profile-component-card fill">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit subject assignment</p>
            <p class="profile-card-subtitle">Of {{ $displayName }}</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ $cancelRoute }}" class="gear-button" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            <div class="form-group">
                <label class="form-label">Subject</label>
                <select wire:model.defer="subject_id" class="form-select placeholder-empty" data-placeholder="true">
                    <option value="">Select a subject...</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->subject_id }}">{{ $s->subject_name }}</option>
                    @endforeach
                </select>
                @error('subject_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Proficiency</label>
                <select wire:model.defer="subject_proficiency" class="form-select placeholder-empty" data-placeholder="true">
                    <option value="">Select proficiency...</option>
                    <option value="beginner">Beginner</option>
                    <option value="competent">Competent</option>
                    <option value="proficient">Proficient</option>
                </select>
                @error('subject_proficiency') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            {{-- actions moved to header to avoid duplicate controls --}}

            @if(auth()->check() && auth()->user()->role_id === 1)
                <div class="flex justify-end mt-4">
                    <a href="{{ route('volunteer_subjects.index') }}" class="small-link">Manage subjects</a>
                    <a href="{{ route('volunteer_subjects.create') }}" class="small-link ms-3">Add subject</a>
                </div>
            @endif
        </form>
    </div>
</div>
