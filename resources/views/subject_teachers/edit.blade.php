@extends('layouts.app')

@section('content')
<div class="container">
    @livewire('profile.volunteer.subject-teachers.edit', ['teacherId' => $teacher->teacher_id ?? null])
</div>
@endsection
