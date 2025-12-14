@extends('layouts.app')

@section('content')
<div class="p-6">
    @livewire('student-excuse-letters.create', ['user_id' => $user_id, 'date' => $date, 'session' => $session, 'attendance_id' => $attendance_id])
</div>
@endsection