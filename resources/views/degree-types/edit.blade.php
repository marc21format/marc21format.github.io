@extends('layouts.app')

@section('content')
    <div class="px-8 py-6 pb-12">
        @livewire('degree-types.edit', ['degreetype_id' => $id])
    </div>
@endsection
