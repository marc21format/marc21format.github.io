@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-lg font-medium">Fields of Work</h3>
        <a href="{{ route('fields_of_work.create') }}" class="btn btn-primary">Add field</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fields as $f)
                    <tr>
                        <td class="border px-4 py-2">{{ $f->name }}</td>
                        <td class="border px-4 py-2 text-right">
                            <a href="{{ route('fields_of_work.edit', ['field' => $f->fieldofwork_id]) }}" class="btn">Edit</a>
                            <form method="POST" action="{{ route('fields_of_work.destroy', ['field' => $f->fieldofwork_id]) }}" style="display:inline" onsubmit="return confirm('Delete this field?');">
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

    <div class="mt-4">{{ $fields->links() }}</div>
</div>
@endsection
