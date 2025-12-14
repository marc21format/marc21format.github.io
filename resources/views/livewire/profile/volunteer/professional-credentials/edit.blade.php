
@php
    $user = $user ?? ($credential->user ?? auth()->user());
    $p = $user->userProfile ?? null;
    $nameParts = [];
    if ($p && ($p->f_name || $p->m_name || $p->s_name)) {
        if ($p->f_name) $nameParts[] = $p->f_name;
        if ($p->m_name) $nameParts[] = $p->m_name;
        if ($p->s_name) $nameParts[] = $p->s_name;
        if (!empty($p->generational_suffix)) $nameParts[] = $p->generational_suffix;
    } else {
        $nameParts[] = $user->name ?? $user->email ?? 'User';
    }
    $displayName = trim(implode(' ', $nameParts));
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit Professional Credential</p>
            <p class="profile-card-subtitle">Of {{ $displayName }}</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ route('profile.volunteer.show', ['user' => $credential->user_id ?? ($credential?->user_id ?? null)]) }}" class="gear-button" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
        <div class="form-group">
            <label class="form-label">Field of work</label>
            <select wire:model.defer="fieldofwork_id" class="form-select">
                <option value="">-- select field --</option>
                @foreach($fields as $f)
                    <option value="{{ $f->fieldofwork_id }}">{{ $f->name }}</option>
                @endforeach
            </select>
            @error('fieldofwork_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Prefix (optional)</label>
            <select wire:model.defer="prefix_id" class="form-select" @if(empty($fieldofwork_id)) disabled @endif>
                <option value="">-- none --</option>
                @foreach($prefixes as $p)
                    <option value="{{ $p->prefix_id }}">{{ $p->title }} @if($p->abbreviation) ({{ $p->abbreviation }}) @endif</option>
                @endforeach
            </select>
            @error('prefix_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Suffix (optional)</label>
            <select wire:model.defer="suffix_id" class="form-select" @if(empty($fieldofwork_id)) disabled @endif>
                <option value="">-- none --</option>
                @foreach($suffixes as $s)
                    <option value="{{ $s->suffix_id }}">{{ $s->title }} @if($s->abbreviation) ({{ $s->abbreviation }}) @endif</option>
                @endforeach
            </select>
            @error('suffix_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Issued year (YYYY)</label>
            <input type="number" wire:model.defer="issued_on" class="form-input" min="1900" max="2100" placeholder="YYYY" />
            @error('issued_on') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea wire:model.defer="notes" class="form-input form-textarea" rows="4" placeholder="Optional notes about this credential"></textarea>
            @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        {{-- actions moved to header --}}
        </form>
    </div>
</div>
