<div class="p-4 bg-gray-50 rounded">
    <form wire:submit.prevent="store" class="space-y-3">
        <div>
            <label class="form-label">Name</label>
            <input wire:model.defer="name" type="text" class="form-input" />
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="form-label">Email</label>
            <input wire:model.defer="email" type="email" class="form-input" />
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Create Student</button>
        </div>
    </form>
</div>
