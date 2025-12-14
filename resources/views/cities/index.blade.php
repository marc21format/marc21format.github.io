@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Cities</h2>
        <a href="{{ route('cities.create') }}" class="btn btn-primary">Add City</a>
      </div>

      {{-- Livewire-powered cities list --}}
      @livewire('cities.index')
    </div>
  </div>
</div>
@endsection
