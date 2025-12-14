@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium">Suffix Titles</h3>
        <a href="{{ route('suffixes.create') }}" class="btn btn-primary">Add suffix</a>
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
                @foreach($suffixes as $s)
                    <tr>
                        <td class="border px-4 py-2">{{ $s->title }}</td>
                        <td class="border px-4 py-2">{{ $s->abbreviation ?? '-' }}</td>
                        <td class="border px-4 py-2">{{ optional($s->fieldOfWork)->name ?? '-' }}</td>
                        <td class="border px-4 py-2 text-right">
                            <a href="{{ route('suffixes.edit', ['suffix' => $s->suffix_id]) }}" class="btn">Edit</a>
                            <form method="POST" action="{{ route('suffixes.destroy', ['suffix' => $s->suffix_id]) }}" style="display:inline" onsubmit="return confirm('Delete this suffix?');">
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

    <div class="mt-4">{{ $suffixes->links() }}</div>
</div>
@endsection
