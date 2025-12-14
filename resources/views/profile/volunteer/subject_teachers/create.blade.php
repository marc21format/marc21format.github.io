@extends('layouts.app')

@section('content')
<div class="profile-page">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <div class="profile-grid-wrapper">
        <div class="grid gap-6 sm:grid-cols-[220px_1fr]">
            <aside class="bg-transparent"></aside>

            <div>
                @livewire('profile.volunteer.subject-teachers.create', ['userId' => $user->id ?? null])
            </div>
        </div>
    </div>
</div>
@endsection
