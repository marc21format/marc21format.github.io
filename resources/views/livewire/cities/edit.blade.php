<div class="profile-form-card max-w-2xl mx-auto">
    <h3 class="profile-card-title">Edit City</h3>
    <form wire:submit.prevent="save">
        <div class="form-group">
            <label class="form-label">Province</label>
            <select wire:model.defer="province_id" class="form-select">
                <option value="">-- select province --</option>
                @foreach($provinces as $p)
                    <option value="{{ $p->province_id }}">{{ $p->province_name }}</option>
                @endforeach
            </select>
            @error('province_id') <x-input-error>{{ $message }}</x-input-error> @enderror
        </div>

        <div class="form-group">
            <label class="form-label">City name</label>
            <input type="text" wire:model.defer="city_name" class="form-input">
            @error('city_name') <x-input-error>{{ $message }}</x-input-error> @enderror
        </div>
        <div class="form-actions">
            <a href="{{ route('cities.index') }}" class="small-link">Cancel</a>
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
</div>
