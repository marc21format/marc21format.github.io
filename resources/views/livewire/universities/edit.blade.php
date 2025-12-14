<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit University</p>
            <p class="profile-card-subtitle">Update university details</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            @if(\Illuminate\Support\Facades\Route::has('universities.index'))
                <a href="{{ route('universities.index') }}" class="gear-button text-slate-800" title="Cancel">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            @csrf
            <div class="mb-4">
                <label class="form-label">University name</label>
                <input type="text" wire:model.defer="university_name" class="form-input w-full h-10" />
                @error('university_name') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="mb-2 text-sm text-muted">
                <small>Will auto-capitalize the name and will auto-generate an abbreviation if left empty.</small>
            </div>

            <div class="mb-4">
                <label class="form-label">Abbreviation (optional)</label>
                <input type="text" wire:model.defer="abbreviation" class="form-input w-full h-10" />
                @error('abbreviation') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="mb-2 text-sm text-muted">
                <small>If left empty, an abbreviation will be generated automatically on save.</small>
            </div>
        </form>
    </div>
</div>
