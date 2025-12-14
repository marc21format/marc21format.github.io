<div class="max-w-2xl mx-auto p-4 bg-white">
    <h3 class="mb-2">Edit Province</h3>
    <form wire:submit.prevent="save" class="space-y-3">
        <div>
            <label class="form-label">Province name</label>
            <input type="text" wire:model.defer="province_name" class="form-input rounded-lg px-4 py-2">
            @error('province_name') <div class="text-danger">{{ $message }}</div> @enderror
        </div>

        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('provinces.index') }}" class="small-link">Cancel</a>
            </div>
            <div class="flex gap-3">
                <form method="POST" action="{{ route('provinces.destroy', $province->province_id) }}" onsubmit="return confirm('Delete this province?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </div>
    </form>
</div>
