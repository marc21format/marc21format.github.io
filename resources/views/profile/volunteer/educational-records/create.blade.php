@extends('layouts.app')

@section('content')
<div class="profile-page">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <div class="profile-grid-wrapper">
        <div class="grid gap-6 sm:grid-cols-1">
            <div class="max-w-3xl mx-auto w-full">
                @livewire('profile.volunteer.educational-records.create', ['userId' => $user->id ?? null])
            </div>
        </div>
    </div>
</div>
@endsection
