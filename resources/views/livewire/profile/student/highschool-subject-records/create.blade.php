@php
	// Ensure $userId is available
	$userId = $userId ?? $this->userId ?? auth()->id();
	$user = $user ?? \App\Models\User::find($userId);
@endphp

<div class="profile-component-card">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Add Highschool Subject</p>
			@php
				$prefix = optional(optional(optional($user)->professionalCredentials->last())->prefix)->title ?? '';
				$f = optional(optional($user)->userProfile)->f_name ?? optional($user)->name ?? '';
				$m = optional(optional($user)->userProfile)->m_name ?? '';
				$l = optional(optional($user)->userProfile)->l_name ?? '';
				$gen = optional(optional($user)->userProfile)->generational_suffix ?? optional(optional(optional($user)->professionalCredentials->last())->suffix)->title ?? '';
				$creds = optional($user)->professionalCredentials ? optional($user)->professionalCredentials->pluck('credential_title')->filter()->join(', ') : '';
				$displayName = trim(trim($prefix).' '.trim($f).' '.trim($m).' '.trim($l));
			@endphp
			<p class="profile-card-subtitle">{{ $displayName ?: (optional($user)->email ?? 'User') }}@if($gen) , {{ $gen }}@endif@if($creds) — {{ $creds }}@endif</p>
		</div>
		<div class="profile-card-actions">
			<button type="button" class="gear-button text-slate-800" wire:click.prevent="store" title="Add">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<a href="{{ route('profile.student.show', $userId) }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form wire:submit.prevent="store">
			<div class="form-group">
				<div class="flex items-center justify-between">
					<label for="highschoolsubject_id" class="form-label">Subject</label>
					<div class="flex items-center gap-2">
						@if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()))
							<a href="{{ route('highschool_subjects.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Subjects list">
								<i class="fa fa-list" aria-hidden="true"></i>
							</a>
						@endif
						<a href="{{ route('highschool_subjects.create', ['user_id' => $userId]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Add subject">
							<i class="fa fa-plus" aria-hidden="true"></i>
						</a>
					</div>
				</div>
				<select id="highschoolsubject_id" wire:model="highschoolsubject_id" class="form-select">
					<option value="">Select a Subject...</option>
					@foreach(($highschoolSubjects ?? []) as $s)
						<option value="{{ $s->highschoolsubject_id }}">{{ $s->subject_name }}</option>
					@endforeach
				</select>
				@error('highschoolsubject_id') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			<div class="form-group">
				<label for="grade" class="form-label">Grade</label>
				<input id="grade" type="text" wire:model.defer="grade" class="form-input" placeholder="e.g. A, 95, 3.5">
				@error('grade') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
