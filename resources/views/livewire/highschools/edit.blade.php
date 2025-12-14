<div class="profile-component-card">
	<div class="profile-card-header">
		<div>
			<p class="profile-card-title">Edit Highschool</p>
		</div>
		<div class="profile-card-actions">
			<button type="button" class="gear-button text-slate-800" wire:click.prevent="updateHighschool" title="Save">
				<i class="fa fa-check" aria-hidden="true"></i>
			</button>
			<a href="{{ route('highschools.index') }}" class="gear-button text-slate-800" title="Cancel">
				<i class="fa fa-times" aria-hidden="true"></i>
			</a>
		</div>
	</div>

	<div class="border-t border-slate-200 pt-4">
		<form wire:submit.prevent="updateHighschool">
			@csrf

			<div class="form-group">
				<label for="name" class="form-label">Name</label>
				<input id="name" type="text" class="form-input" wire:model.debounce.300ms="name" autocomplete="off" />
				@error('name') <x-input-error>{{ $message }}</x-input-error> @enderror
				<div class="mt-1 text-sm text-muted">
					<small>Will auto-capitalize the name.</small>
				</div>
			</div>

			<div class="form-row">
				<div class="form-col w-2/3">
					<label for="abbreviation" class="form-label">Abbreviation</label>
					<input id="abbreviation" type="text" class="form-input" wire:model="abbreviation" wire:input="$set('abbreviationTouched', true)" />
					@error('abbreviation') <x-input-error>{{ $message }}</x-input-error> @enderror
					<div class="mt-1 text-sm text-muted">
						<small>Will auto-generate an abbreviation if left empty. Typing here prevents auto-generation.</small>
					</div>
				</div>

				<div class="form-col w-1/3">
					<label class="form-label">Type</label>
					<div class="flex gap-3">
						<button type="button" wire:click.prevent="$set('type','public')" class="{{ ($type ?? '') === 'public' ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">Public</button>
						<button type="button" wire:click.prevent="$set('type','private')" class="{{ ($type ?? '') === 'private' ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">Private</button>
					</div>
					@error('type') <x-input-error>{{ $message }}</x-input-error> @enderror
				</div>
			</div>

			{{-- actions moved to header --}}
		</form>
	</div>
</div>
