<div class="max-w-4xl mx-auto p-4 bg-white">
    <h2 class="text-lg font-bold mb-3">Provinces</h2>

    @livewire('provinces.create')

    <div class="mt-4">
        <input type="text" wire:model="search" placeholder="Search provinces..." class="form-input rounded-lg px-4 py-2" />
    </div>

    <div class="mt-4">
        <table class="w-full table-auto">
            <thead>
                <tr>
                    <th class="text-left">Name</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($provinces as $p)
                <tr>
                    <td>{{ $p->province_name }}</td>
                    <td class="text-right"><a href="{{ route('provinces.edit', $p->province_id) }}" class="small-link">Edit</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">{{ $provinces->links() }}</div>
    </div>
</div>
