@php
	// Defensive fallback: if the Livewire component didn't pass $highschools
	// (due to an unexpected mount/render path), load them here so the blade
	// doesn't error. Livewire components normally pass this via render().
	$highschools = $highschools ?? \App\Models\Highschool::orderBy('highschool_name')->get();
	// Ensure $userId is available in the blade. Prefer the component prop,
	// then a surrounding $user object, then the route param, then the auth user.
	$userId = $userId ?? ($user->id ?? request()->route('user') ?? auth()->id());
@endphp

<div class="profile-component-card">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Add Highschool Record</p>
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
			<a href="{{ in_array((int) (optional($user)->role_id ?? optional(optional($user)->role)->role_id), [1,2,3]) ? route('profile.volunteer.show', ['user' => $userId]) : route('profile.student.show', ['user' => $userId]) }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form wire:submit.prevent="store">
			@csrf

			<div class="form-group">
				<div class="flex items-center justify-between">
					<label for="highschool_id" class="form-label">Highschool</label>
					<div class="flex items-center gap-2">
						@if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()))
							<a href="{{ route('highschools.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Highschools list">
								<i class="fa fa-list" aria-hidden="true"></i>
							</a>
						@endif
						<a href="{{ route('highschools.create', ['user_id' => $userId]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Add highschool">
							<i class="fa fa-plus" aria-hidden="true"></i>
						</a>
					</div>
				</div>
				<select wire:model.defer="highschool_id" id="highschool_id" class="form-select">
					<option value="">Select a Highschool...</option>
					@foreach($highschools as $h)
						<option value="{{ $h->highschool_id }}">{{ $h->highschool_name }}</option>
					@endforeach
				</select>
				@error('highschool_id') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			<div class="form-row flex items-center gap-4">
				<div class="form-col w-1/3 min-w-0">
					<label class="form-label">Level</label>
					<div class="level-toggle">
						<input type="radio" id="junior" value="Junior Highschool" wire:model.defer="level">
						<label for="junior">Junior</label>
						<input type="radio" id="senior" value="Senior Highschool" wire:model.defer="level">
						<label for="senior">Senior</label>
					</div>
					@error('level') <x-input-error>{{ $message }}</x-input-error> @enderror
				</div>

				<div class="form-col w-1/3 min-w-0">
					<label for="year_start" class="form-label">Year Start</label>
					<input type="text" wire:model.defer="year_start" id="year_start" class="form-input w-full h-10" />
				</div>

				<div class="form-col w-1/3 min-w-0">
					<label for="year_end" class="form-label">Year Graduated</label>
					<input type="text" wire:model.defer="year_end" id="year_end" class="form-input w-full h-10" />
				</div>
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
