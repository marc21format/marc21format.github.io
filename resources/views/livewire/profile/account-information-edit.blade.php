@php
    $user = $user ?? ($this->user ?? auth()->user());
@endphp

@php
    $targetId = $user->id ?? auth()->id();
    $target = \App\Models\User::find($targetId);
    $cancelRoute = ( $target && in_array($target->role_id, [1,2,3], true) ) ? route('profile.volunteer.show', ['user' => $targetId]) : route('profile.student.show', ['user' => $targetId]);
@endphp

<div x-data="accountForm()" x-init="initFromDom()" class="profile-component-card profile-form-card max-w-2xl mx-auto">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit Account Information</p>
            <p class="profile-card-subtitle">Of {{ $user->name ?? ($user->email ?? 'User') }}</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button" wire:click.prevent="saveAccount" title="Save">
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

        <form wire:submit.prevent="saveAccount" class="space-y-4">
            @csrf

            <div class="form-group">
                <label class="form-label" for="name">Username / Display Name</label>
                <input id="name" type="text" wire:model.defer="name" x-on:input="onGenericInput($event, 'name')" class="form-input">
                @error('name') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email</label>
                <input id="email" type="email" wire:model.defer="email" x-on:input="onGenericInput($event, 'email')" class="form-input">
                @error('email') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password (leave blank to keep current)</label>
                <input id="password" type="password" wire:model.defer="password" x-on:input="onGenericInput($event, 'password')" autocomplete="new-password" class="form-input">
                @error('password') <div class="text-danger">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <input id="password_confirmation" type="password" wire:model.defer="password_confirmation" x-on:input="onGenericInput($event, 'password_confirmation')" autocomplete="new-password" class="form-input">
            </div>

            @if(in_array($user->role_id, [1,2,3]))
                <div class="form-group">
                    <label class="form-label" for="role_id">Role</label>
                    <select id="role_id" wire:model.defer="role_id" x-on:change="onGenericInput($event, 'role_id')" class="form-select">
                        <option value="">-- keep current --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->role_id }}">{{ $r->role_title }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
            @endif

            
        </form>
    </div>
</div>
