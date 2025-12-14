@php
    // Accept role override from parent (student/volunteer)
    $actor = $actor ?? (auth()->check() ? auth()->user() : null);
    $role = $role ?? ($actor && method_exists($actor, 'isStudent') && $actor->isStudent() ? 'student' : ($actor && method_exists($actor, 'isVolunteer') && $actor->isVolunteer() ? 'volunteer' : null));
    // Default $mode to 'view' if not set (for direct blade include)
    if (!isset($mode)) $mode = 'view';
    // Default $fields to empty array if not set
    if (!isset($fields) || !is_array($fields)) $fields = [];
@endphp
<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($mode === 'edit')
        <div class="profile-summary">
            <form wire:submit.prevent="save">
                <table class="profile-table table-horizontal summary-table">
                    <tbody>
                        <tr>
                            <th class="text-left">User</th>
                            <td>{{ $actor ? $actor->name : $fields['user_id'] }}</td>
                        </tr>
                        @if($role === 'student')
                            <tr>
                                <th class="text-left">Student Number</th>
                                <td><input type="text" wire:model="fields.student_number"></td>
                            </tr>
                            <tr>
                                <th class="text-left">Student FCEER Batch</th>
                                <td><input type="text" wire:model="fields.fceer_batch"></td>
                            </tr>
                            <tr>
                                <th class="text-left">Student Group</th>
                                <td>
                                    <select wire:model="fields.student_group">
                                        <option value="">Select group</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->room_id }}">{{ $room->group }}</option>
                                        @endforeach
                                    </select>
                                </td>
                            </tr>
                        @elseif($role === 'volunteer')
                            <tr>
                                <th class="text-left">Volunteer Number</th>
                                <td><input type="text" wire:model="fields.volunteer_number"></td>
                            </tr>
                            <tr>
                                <th class="text-left">Volunteer FCEER Batch</th>
                                <td><input type="text" wire:model="fields.fceer_batch"></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="flex justify-end gap-3 mt-2">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" wire:click="$set('mode', 'view')" class="small-link">Cancel</button>
                </div>
            </form>
        </div>
    @else
        <div class="profile-summary">
            <table class="profile-table summary-table">
                <tbody>
                    <tr><th class="text-left">User</th><td>{{ $fields['user_id'] }}</td></tr>
                    @if($role === 'student')
                        <tr><th class="text-left">Student Number</th><td>{{ $fields['student_number'] }}</td></tr>
                        <tr><th class="text-left">Student FCEER Batch</th><td>{{ $fields['fceer_batch'] }}</td></tr>
                        <tr><th class="text-left">Student Group</th><td>{{ $fields['student_group'] }}</td></tr>
                    @elseif($role === 'volunteer')
                        <tr><th class="text-left">Volunteer Number</th><td>{{ $fields['volunteer_number'] }}</td></tr>
                        <tr><th class="text-left">Volunteer FCEER Batch</th><td>{{ $fields['fceer_batch'] }}</td></tr>
                    @endif
                </tbody>
            </table>
            @if($actor && ($actor->isAdmin() || $actor->isExecutive()))
                <div class="flex justify-end mt-2">
                    <button wire:click="edit" class="btn btn-primary btn-sm">Edit</button>
                </div>
            @endif
        </div>
    @endif
</div>
