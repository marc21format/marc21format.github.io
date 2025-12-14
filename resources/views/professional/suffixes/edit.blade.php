@extends('layouts.app')

@section('content')
<div class="p-4 bg-white rounded">
    <h3 class="text-lg font-medium">Edit Suffix Title</h3>

    <form method="POST" action="{{ route('suffixes.update', ['suffix' => $suffix->suffix_id]) }}" class="mt-4">
        @csrf
        @method('PATCH')
        <div class="form-group">
            <label class="form-label">Title</label>
            <input name="title" class="form-input" value="{{ old('title',$suffix->title) }}" />
        </div>
        <div class="form-group">
            <label class="form-label">Abbreviation</label>
            <input name="abbreviation" class="form-input" value="{{ old('abbreviation',$suffix->abbreviation) }}" />
        </div>
        <div class="form-group">
            <label class="form-label">Field of work (optional)</label>
            <select name="fieldofwork_id" class="form-input">
                <option value="">-- none --</option>
                @foreach($fields as $f)
                    <option value="{{ $f->fieldofwork_id }}" @if(old('fieldofwork_id',$suffix->fieldofwork_id)==$f->fieldofwork_id) selected @endif>{{ $f->name }}</option>
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
