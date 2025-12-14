@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        @livewire('room.edit', ['roomId' => $room->room_id])
    </div>
@endsection
