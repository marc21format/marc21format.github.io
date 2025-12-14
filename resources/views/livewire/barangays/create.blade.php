<div class="profile-form-card">
    <form wire:submit.prevent="save">
        <div class="form-row">
            <div class="form-col">
                <label class="form-label">City</label>
                <select wire:model.defer="city_id" class="form-select">
                    <option value="">-- select city --</option>
                    @foreach($cities as $c)
                        <option value="{{ $c->city_id }}">{{ $c->city_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-col">
                <label class="form-label">Barangay name</label>
                <input type="text" wire:model.defer="barangay_name" class="form-input" placeholder="New barangay name">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Add barangay</button>
        </div>
    </form>
</div>
