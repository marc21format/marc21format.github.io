<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Rooms</h2>
        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()))
            <a href="{{ route('rooms.create') }}" class="px-3 py-1 bg-blue-600 text-white rounded">New Room</a>
        @endif
    </div>

    <div class="mb-4">
        <input type="search" wire:model.debounce.300ms="search" placeholder="Search group..." class="form-input" />
    </div>

    <table class="min-w-full">
        <thead>
            <tr>
                <th class="text-left">Group</th>
                <th class="text-left">Adviser</th>
                <th class="text-left">President</th>
                <th class="text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $r)
                <tr>
                    <td>{{ $r->group }}</td>
                    <td>{{ optional($r->adviser)->name ?? '—' }}</td>
                    <td>{{ optional($r->president)->name ?? '—' }}</td>
                    <td>
                        <a href="{{ route('rooms.edit', ['room' => $r->room_id]) }}" class="text-blue-600">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $rooms->links() }}
    </div>
</div>
