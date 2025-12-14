@extends('layouts.app')

@section('content')
    <div class="container">
        @livewire('universities.edit', ['university_id' => $id])
    </div>
@endsection
