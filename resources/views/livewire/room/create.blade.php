<div>
    <h2 class="text-lg font-bold mb-4">Create Room</h2>

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm">Group</label>
            <input type="text" wire:model.defer="fields.group" class="form-input" required />
            @error('fields.group') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm">Adviser</label>
            <select wire:model="fields.adviser_id" class="form-select">
                <option value="">-- none --</option>
                @foreach($staffUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ optional($u->role)->role_title }})</option>
                @endforeach
            </select>
            @error('fields.adviser_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm">Co-adviser</label>
            <select wire:model="fields.co_adviser_id" class="form-select">
                <option value="">-- none --</option>
                @foreach($staffUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ optional($u->role)->role_title }})</option>
                @endforeach
            </select>
            @error('fields.co_adviser_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm">President</label>
            <select wire:model="fields.president_id" class="form-select">
                <option value="">-- none --</option>
                @foreach($studentUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ optional($u->role)->role_title }})</option>
                @endforeach
            </select>
            @error('fields.president_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm">Secretary</label>
            <select wire:model="fields.secretary_id" class="form-select">
                <option value="">-- none --</option>
                @foreach($studentUsers as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ optional($u->role)->role_title }})</option>
                @endforeach
            </select>
            @error('fields.secretary_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        </div>

        <div>
            <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Create</button>
            <a href="{{ route('rooms.index') }}" class="ml-3 text-sm">Cancel</a>
        </div>
    </form>
</div>
