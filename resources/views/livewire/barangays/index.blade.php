<div class="max-w-4xl mx-auto p-4 bg-white">
    <h2 class="text-lg font-bold mb-3">Barangays</h2>

    @livewire('barangays.create')

    <div class="mt-4">
        <input type="text" wire:model="search" placeholder="Search barangays..." class="form-input rounded-lg px-4 py-2" />
    </div>

    <div class="mt-4">
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-left">City</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($barangays as $b)
                <tr>
                    <td>{{ $b->barangay_name }}</td>
                    <td>{{ $b->city?->city_name }}</td>
                    <td class="text-right"><a href="{{ route('barangays.edit', $b->barangay_id) }}" class="small-link">Edit</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">{{ $barangays->links() }}</div>
    </div>
</div>
