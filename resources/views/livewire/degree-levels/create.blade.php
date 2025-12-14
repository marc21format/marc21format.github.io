<div class="profile-component-card">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Create Degree Level</p>
		</div>
		<div class="profile-card-actions">
			<button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Add">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<a href="{{ route('degree-levels.index') }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form wire:submit.prevent="save">
			@csrf

			<div class="form-group">
				<label for="level_name" class="form-label">Level name</label>
				<input id="level_name" type="text" class="form-input" wire:model.defer="level_name" autocomplete="off" />
				@error('level_name') <x-input-error>{{ $message }}</x-input-error> @enderror
				<div class="mt-1 text-sm text-muted">
					<small>Will auto-capitalize and auto-generate abbreviation</small>
				</div>
			</div>

			<div class="form-group">
				<label for="degree_level" class="form-label">Degree level (internal key)</label>
				<input id="degree_level" type="text" class="form-input" wire:model.defer="degree_level" autocomplete="off" />
				@error('degree_level') <x-input-error>{{ $message }}</x-input-error> @enderror
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
