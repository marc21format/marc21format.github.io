<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($mode === 'view')
        <table class="profile-table">
            <tbody>
                <tr><th>Group</th><td>{{ $room ? $room->group : $fields['group'] }}</td></tr>
                <tr><th>Adviser</th><td>{{ $room && $room->adviser ? $room->adviser->name : '' }}</td></tr>
                <tr><th>President</th><td>{{ $room && $room->president ? $room->president->name : '' }}</td></tr>
                <tr><th>Secretary</th><td>{{ $room && $room->secretary ? $room->secretary->name : '' }}</td></tr>
            </tbody>
        </table>
        @if($actor && ($actor->isAdmin() || $actor->isExecutive()))
            <button wire:click="edit">Edit</button>
        @endif
    @else
        <form wire:submit.prevent="save">
            <div>
                <label>Group</label>
                <input type="text" wire:model="fields.group">
            </div>
            <div>
                <label>Adviser</label>
                <select wire:model="fields.adviser_id">
                    <option value="">Select adviser</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>President</label>
                <select wire:model="fields.president_id">
                    <option value="">Select president</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Secretary</label>
                <select wire:model="fields.secretary_id">
                    <option value="">Select secretary</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit">Save</button>
        </form>
    @endif
</div>
