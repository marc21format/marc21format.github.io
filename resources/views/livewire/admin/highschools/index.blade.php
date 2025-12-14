<div class="p-4 bg-white rounded">
    <h3 class="text-lg font-semibold mb-2">Highschools (Admin)</h3>

    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">City</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($highschools as $h)
                <tr>
                    <td class="border px-4 py-2">{{ $h->highschool_name }}</td>
                    <td class="border px-4 py-2">{{ optional($h->city)->city_name ?? '—' }}</td>
                    <td class="border px-4 py-2">
                        <a href="{{ route('highschools.edit', $h->highschool_id) }}" class="text-sm text-blue-600">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">{{ $highschools->links() }}</div>
</div>
