@extends('layouts.app')

@section('content')
    <div class="container">
        @livewire('universities.show', ['universityId' => $university->university_id])
    </div>
@endsection
