<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Create Committee</p>
            <p class="profile-card-subtitle">New committee details</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            @if(\Illuminate\Support\Facades\Route::has('committees.index'))
                <a href="{{ route('committees.index') }}" class="gear-button text-slate-800" title="Cancel">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            @csrf
            <div class="form-group">
                <label class="form-label">Committee Name</label>
                <input type="text" class="form-input w-full h-10" wire:model.defer="committee_name" />
                @error('committee_name') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>
        </form>
    </div>
</div>
