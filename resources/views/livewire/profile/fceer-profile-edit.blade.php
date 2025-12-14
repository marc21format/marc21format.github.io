@php
    $user = $user ?? ($this->user ?? auth()->user());
@endphp

@php
    $targetId = $user->id ?? auth()->id();
    $target = \App\Models\User::find($targetId);
    $cancelRoute = ( $target && in_array($target->role_id, [1,2,3], true) ) ? route('profile.volunteer.show', ['user' => $targetId]) : route('profile.student.show', ['user' => $targetId]);
@endphp

<div class="profile-component-card profile-form-card max-w-2xl mx-auto">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit FCEER Profile</p>
            <p class="profile-card-subtitle">Of {{ $user->name ?? ($user->email ?? 'User') }}</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button" wire:click.prevent="saveFceerProfile" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ $cancelRoute }}" class="gear-button" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        @if(session('status'))
            <div class="mb-3 text-green-700">{{ session('status') }}</div>
        @endif

        <form wire:submit.prevent="saveFceerProfile" class="space-y-4">
            @csrf

            @if(in_array($user->role_id, [1,2,3]))
                <div class="form-group">
                    <label class="form-label" for="volunteer_number">Volunteer Number</label>
                    <input id="volunteer_number" type="text" wire:model.defer="fields.volunteer_number" class="form-input">
                    @error('fields.volunteer_number') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fceer_batch">FCEER Batch</label>
                    <input id="fceer_batch" type="text" wire:model.defer="fields.fceer_batch" class="form-input">
                    @error('fields.fceer_batch') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            @elseif($user->role_id == 4)
                <div class="form-group">
                    <label class="form-label" for="student_number">Student Number</label>
                    <input id="student_number" type="text" wire:model.defer="fields.student_number" class="form-input">
                    @error('fields.student_number') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="student_group">Student Group</label>
                    <select id="student_group" wire:model.defer="fields.student_group" class="form-select">
                        <option value="">-- Select group --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->room_id }}">{{ $r->group }}</option>
                        @endforeach
                    </select>
                    @error('fields.student_group') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="fceer_batch">FCEER Batch</label>
                    <input id="fceer_batch" type="text" wire:model.defer="fields.fceer_batch" class="form-input">
                    @error('fields.fceer_batch') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            @endif

        </form>
    </div>
</div>
