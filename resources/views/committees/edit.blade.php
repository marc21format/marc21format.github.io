@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Committee</h1>
        <livewire:committee.edit :id="request()->route('committee')" />
    </div>
@endsection
