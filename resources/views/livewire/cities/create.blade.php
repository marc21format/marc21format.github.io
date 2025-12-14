<div class="profile-form-card">
    <form wire:submit.prevent="save">
        <div class="form-row">
            <div class="form-col">
                <label class="form-label">Province</label>
                <select wire:model.defer="province_id" class="form-select">
                    <option value="">-- select province --</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p->province_id }}">{{ $p->province_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-col">
                <label class="form-label">City name</label>
                <input type="text" wire:model.defer="city_name" class="form-input" placeholder="New city name">
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Add city</button>
        </div>
    </form>
</div>
