@extends('layouts.app')

@section('content')
<div class="p-6">
    @livewire('student-excuse-letters.show', ['user_id' => $user_id, 'letter_id' => $letter_id])
</div>
@endsection