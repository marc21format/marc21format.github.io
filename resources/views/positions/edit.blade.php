@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Position</h1>
        <livewire:positions.edit :id="request()->route('id')" />
    </div>
@endsection
