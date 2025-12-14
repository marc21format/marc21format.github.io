<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit Position</p>
            <p class="profile-card-subtitle">Update position details</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            @if(\Illuminate\Support\Facades\Route::has('positions.index'))
                <a href="{{ route('positions.index') }}" class="gear-button text-slate-800" title="Cancel">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            @csrf
            <div class="form-group">
                <label class="form-label">Position Name</label>
                <input type="text" class="form-input w-full h-10" wire:model.defer="position_name" />
                @error('position_name') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Committees</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                    @foreach($committees as $c)
                        <button type="button" wire:click="toggleCommittee({{ $c->committee_id }})" class="h-10 px-4 rounded-md {{ in_array($c->committee_id, $committee_ids ?? []) ? 'bg-slate-900 text-white hover:bg-slate-800' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }} flex items-center justify-center text-sm font-medium">
                            {{ $c->committee_name }}
                        </button>
                    @endforeach
                </div>
                @error('committee_ids') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>
        </form>
    </div>
</div>
