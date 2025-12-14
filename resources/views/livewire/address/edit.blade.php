<div class="profile-form-card">
    <div class="space-y-3">
        <h3 class="mb-3">Permanent Address</h3>
        <div>
            <label class="form-label">House / Unit #</label>
            <input type="text" wire:model.defer="house_number" class="form-input">
            @error('house_number') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="form-label">Block / Lot</label>
            <input type="text" wire:model.defer="block_number" class="form-input" placeholder="Block">
            @error('block_number') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="form-label">Street</label>
            <input type="text" wire:model.defer="street" class="form-input">
            @error('street') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        {{-- Put Province first so parent is selected before children --}}
        <div>
            <label class="form-label">Province</label>
            <div class="flex gap-3 items-center">
                <select wire:model="province_id" wire:change="changeProvince($event.target.value)" class="form-select" wire:key="province-select-{{ $province_id ?? 'none' }}">
                    <option value="">-- select province --</option>
                    @foreach(($provinces ?? []) as $p)
                        <option value="{{ $p->province_id }}">{{ $p->province_name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('provinces.create') }}" class="small-link">Create</a>
            </div>
            @error('province_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="form-label">City</label>
            <div class="flex gap-3 items-center">
                <select wire:model="city_id" wire:change="changeCity($event.target.value)" class="form-select"
                        wire:key="city-select-{{ $province_id ?? 'none' }}-{{ $city_id ?? 'none' }}"
                        @if(empty($cities) || count($cities) === 0) disabled @endif
                        wire:loading.attr="disabled" wire:target="changeProvince,province_id">
                    <option value="">@if(empty($cities) || count($cities) === 0) -- select province first -- @else -- select city -- @endif</option>
                    @foreach(($cities ?? []) as $c)
                        <option value="{{ $c->city_id }}">{{ $c->city_name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('cities.create') }}" class="small-link">Create</a>
            </div>
            @error('city_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="form-label">Barangay</label>
            <div class="flex gap-3 items-center">
                <select wire:model="barangay_id" class="form-select"
                        wire:key="barangay-select-{{ $city_id ?? 'none' }}-{{ $barangay_id ?? 'none' }}"
                        @if(empty($barangays) || count($barangays) === 0) disabled @endif
                        wire:loading.attr="disabled" wire:target="changeCity,city_id,changeProvince,province_id">
                    <option value="">@if(empty($barangays) || count($barangays) === 0) -- select city first -- @else -- select barangay -- @endif</option>
                    @foreach(($barangays ?? []) as $b)
                        <option value="{{ $b->barangay_id }}">{{ $b->barangay_name }}</option>
                    @endforeach
                </select>
                <a href="{{ route('barangays.create') }}" class="small-link">Create</a>
            </div>
            @error('barangay_id') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        @unless($hideActions)
            <div class="form-actions">
                <button wire:click.prevent="saveAddress" class="btn btn-primary">Save Address</button>
            </div>
        @endunless
    </div>
</div>
