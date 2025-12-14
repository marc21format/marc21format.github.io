@extends('layouts.app')

@section('content')
<div class="container">
    @livewire('profile.volunteer.subject-teachers.create', ['userId' => $user->id ?? null])
</div>
@endsection
