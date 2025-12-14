@extends('layouts.app')

@section('content')
<div class="p-6">
    @livewire('student-excuse-letters.edit', ['user_id' => $user_id, 'letter_id' => $letter_id])
</div>
@endsection