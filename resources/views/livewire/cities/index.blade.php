<div class="max-w-4xl mx-auto p-4 bg-white">
    <h2 class="text-lg font-bold mb-3">Cities</h2>

    @livewire('cities.create')
    <div class="mt-4">
        <input type="text" wire:model="search" placeholder="Search cities..." class="form-input rounded-lg px-4 py-2" />
    </div>

    <div class="mt-4">
        <input type="text" wire:model="search" placeholder="Search cities..." class="form-input rounded-lg px-4 py-2" />
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
                @foreach($cities as $c)
                <tr>
                    <td>{{ $c->city_name }}</td>
                    <td class="text-right"><a href="{{ route('cities.edit', $c->city_id) }}" class="small-link">Edit</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3">{{ $cities->links() }}</div>
    </div>
</div>
