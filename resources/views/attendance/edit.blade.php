@extends('layouts.app')

@section('content')
    <div class="container mx-auto py-6">
        <h1 class="text-2xl font-semibold mb-4">Edit Attendance</h1>
        @livewire('attendance.edit', ['attendanceId' => $attendanceId ?? null])
    </div>
@endsection
