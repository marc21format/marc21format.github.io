<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Edit Highschool Subject</p>
			<p class="profile-card-subtitle">Update subject details</p>
		</div>
		<div class="profile-card-actions">
			<button type="button" class="gear-button text-slate-800" wire:click.prevent="store" title="Save">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<a href="{{ route('highschool_subjects.index') }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form wire:submit.prevent="store">
			@csrf

			<div class="form-group">
				<label for="subject_name" class="form-label">Subject name</label>
				<input id="subject_name" type="text" class="form-input w-full h-10" wire:model.defer="subject_name" autocomplete="off" />
				@error('subject_name') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			<div class="form-group">
				<label for="subject_subname" class="form-label">Subject subname (optional)</label>
				<input id="subject_subname" type="text" class="form-input w-full h-10" wire:model.defer="subject_subname" autocomplete="off" />
				@error('subject_subname') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			<div class="form-group">
				<label for="subject_code" class="form-label">Subject code (optional)</label>
				<input id="subject_code" type="text" class="form-input w-full h-10" wire:model.defer="subject_code" autocomplete="off" />
				@error('subject_code') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
