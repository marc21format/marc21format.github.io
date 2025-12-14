<div class="profile-form-card max-w-2xl mx-auto">
    <h3 class="profile-card-title">Edit Barangay</h3>
    <form wire:submit.prevent="save">
        <div class="form-group">
            <label class="form-label">City</label>
            <select wire:model.defer="city_id" class="form-select">
                <option value="">-- select city --</option>
                @foreach($cities as $c)
                    <option value="{{ $c->city_id }}">{{ $c->city_name }}</option>
                @endforeach
            </select>
            @error('city_id') <x-input-error>{{ $message }}</x-input-error> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">Barangay name</label>
            <input type="text" wire:model.defer="barangay_name" class="form-input">
            @error('barangay_name') <x-input-error>{{ $message }}</x-input-error> @enderror
        </div>

        <div class="form-actions" style="justify-content:space-between;">
            <a href="{{ route('barangays.index') }}" class="small-link">Cancel</a>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <form method="POST" action="{{ route('barangays.destroy', $barangay->barangay_id) }}" onsubmit="return confirm('Delete this barangay?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </div>
    </form>
</div>
