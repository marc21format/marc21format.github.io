@extends('layouts.app')

@section('content')
<div class="profile-page">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <div class="profile-grid-wrapper">
        <div class="grid gap-6 sm:grid-cols-1">
            <div class="max-w-3xl mx-auto w-full">
                <h1 class="sr-only">Edit Committee Membership</h1>
                <livewire:profile.volunteer.committee-members.edit :id="$membership->member_id" />
            </div>
        </div>
    </div>
</div>
@endsection
