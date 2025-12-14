@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-semibold mb-4">Create Attendance</h1>
        @livewire('attendance.create', ['userId' => $userId ?? null])
    </div>
@endsection
