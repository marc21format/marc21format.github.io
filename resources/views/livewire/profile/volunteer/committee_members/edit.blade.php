<div class="profile-form-card">
    <form wire:submit.prevent="save">

        <div class="form-group">
            <label class="form-label">Committee</label>
            <select name="committee_id" class="form-select" wire:model="committee_id">
                <option value="">-- select committee --</option>
                @foreach($committees as $c)
                    <option value="{{ $c->committee_id }}">{{ $c->committee_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Position</label>
            <select name="position_id" class="form-select" wire:model="position_id">
                <option value="">-- select position --</option>
                @foreach($availablePositions as $p)
                    <option value="{{ $p->position_id }}">{{ $p->position_name ?? $p->position }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-actions mt-4">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('profile.volunteer.show', ['user' => $membership->user_id]) }}" class="small-link ms-3">Cancel</a>
        </div>
    </form>
</div>
