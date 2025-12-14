@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <h3 class="text-lg font-medium">Add Suffix Title</h3>

    <form method="POST" action="{{ route('suffixes.store') }}" class="mt-4">
        @csrf
        <div class="form-group">
            <label class="form-label">Title</label>
            <input name="title" class="form-input" value="{{ old('title') }}" />
        </div>
        <div class="form-group">
            <label class="form-label">Abbreviation</label>
            <input name="abbreviation" class="form-input" value="{{ old('abbreviation') }}" />
        </div>
        <div class="form-group">
            <label class="form-label">Field of work (optional)</label>
            <select name="fieldofwork_id" class="form-input">
                <option value="">-- none --</option>
                @foreach($fields as $f)
                    <option value="{{ $f->fieldofwork_id }}">{{ $f->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-actions mt-4">
            <button class="btn btn-primary" type="submit">Save</button>
            <a href="{{ route('suffixes.index') }}" class="btn">Cancel</a>
        </div>
    </form>
</div>
@endsection
