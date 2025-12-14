@extends('layouts.app')

@section('content')
<div class="p-6">
    @livewire('student-excuse-letters.index', ['user_id' => $user_id])
</div>
@endsection