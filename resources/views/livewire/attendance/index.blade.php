<div>
    <div class="mb-4">
        <input wire:model="search" placeholder="Search by user name or email" class="form-input" />
        <input type="date" wire:model="date" class="form-input ml-2" />
        <div class="ml-2 inline-flex gap-2">
            <button type="button" wire:click.prevent="setSession('am')" class="px-3 py-1 bg-gray-100 rounded">AM</button>
            <button type="button" wire:click.prevent="setSession('pm')" class="px-3 py-1 bg-gray-100 rounded">PM</button>
            <button type="button" wire:click.prevent="setSession(null)" class="px-3 py-1 bg-gray-100 rounded">All</button>
        </div>
        <div class="mt-2 text-sm text-gray-600">
            <span wire:loading.class="opacity-50" wire:target="date,session,search">Applying filters...</span>
            <div class="mt-1">Livewire state: <strong>Date:</strong> {{ $date ?? 'null' }} &nbsp; <strong>Session:</strong> {{ $session ?? 'null' }} &nbsp; <strong>Search:</strong> {{ $search ?: 'empty' }}
                <button type="button" wire:click="setDate" class="ml-4 px-3 py-1 bg-blue-600 text-white rounded">Apply Date</button>
            </div>
        </div>
    </div>

    <table class="min-w-full">
        <thead>
            <tr>
                <th>User</th>
                <th>Date</th>
                <th>Session</th>
                <th>Time</th>
                <th>Recorded By</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                @php $att = $u->attendanceRecords->first() ?? null; @endphp
                <tr>
                    <td>{{ $u->name ?? '—' }}</td>
                    <td>{{ $att ? optional($att->date)->toDateString() : ($date ?? '—') }}</td>
                    <td>{{ $att ? $att->session : ($session ?? '—') }}</td>
                    <td>{{ $att ? $att->attendance_time : '—' }}</td>
                    <td>{{ $att ? ($att->recordedBy->name ?? '—') : '—' }}</td>
                    <td>
                        @if($att)
                            @if($actor && ($actor->isStaff() || ($actor->id === $u->id)))
                                <a href="{{ route('attendance.edit', ['attendance' => $att->attendance_id]) }}">Edit</a>
                            @endif
                        @else
                            @if($actor && ($actor->isStaff() || ($actor->id === $u->id)))
                                <a href="{{ route('attendance.create', ['user' => $u->id]) }}">Add</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $users->links() }}</div>
</div>


