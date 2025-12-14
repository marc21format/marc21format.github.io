@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium">Prefix Titles</h3>
        <a href="{{ route('prefixes.create') }}" class="btn btn-primary">Add prefix</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2">Title</th>
                    <th class="px-4 py-2">Abbreviation</th>
                    <th class="px-4 py-2">Field</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prefixes as $p)
                    <tr>
                        <td class="border px-4 py-2">{{ $p->title }}</td>
                        <td class="border px-4 py-2">{{ $p->abbreviation ?? '-' }}</td>
                        <td class="border px-4 py-2">{{ optional($p->fieldOfWork)->name ?? '-' }}</td>
                        <td class="border px-4 py-2 text-right">
                            <a href="{{ route('prefixes.edit', ['prefix' => $p->prefix_id]) }}" class="btn">Edit</a>
                            <form method="POST" action="{{ route('prefixes.destroy', ['prefix' => $p->prefix_id]) }}" style="display:inline" onsubmit="return confirm('Delete this prefix?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $prefixes->links() }}</div>
</div>
@endsection
