@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <h3 class="text-lg font-medium">Edit Field of Work</h3>

    <form method="POST" action="{{ route('fields_of_work.update', ['field' => $field->fieldofwork_id]) }}" class="mt-4">
        @csrf
        @method('PATCH')
        <div class="form-group">
            <label class="form-label">Name</label>
            <input name="name" class="form-input" value="{{ old('name',$field->name) }}" />
        </div>
        <div class="form-actions mt-4">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('fields_of_work.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
