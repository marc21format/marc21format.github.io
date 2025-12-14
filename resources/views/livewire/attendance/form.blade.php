<div>
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="form-label">ID</label>
                <div class="form-input">{{ $displayId ?? '—' }}</div>
            </div>
            <div>
                <label class="form-label">Full name</label>
                <div class="form-input">{{ $displayName ?? '—' }}</div>
            </div>

            <div>
                <label class="form-label">Committee / Group</label>
                <div class="form-input">{{ $displayGroup ?? '—' }}</div>
            </div>

            <div>
                <label for="date">Date</label>
                <input type="date" wire:model.defer="fields.date" id="date" class="form-input" />
            </div>

            <div>
                <label for="attendance_time">Time</label>
                <input type="time" wire:model.defer="fields.attendance_time" id="attendance_time" class="form-input" />
            </div>
            @if(isset($attendance))
                <div>
                    <label for="session">Session</label>
                    <select wire:model.defer="fields.session" id="session" class="form-input">
                        <option value="am">AM</option>
                        <option value="pm">PM</option>
                    </select>
                </div>
            @endif
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
