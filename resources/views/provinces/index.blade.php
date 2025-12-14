@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Provinces</h2>
        <a href="{{ route('provinces.create') }}" class="btn btn-primary">Add Province</a>
      </div>

      {{-- Livewire-powered provinces list --}}
      @livewire('provinces.index')
    </div>
  </div>
</div>
@endsection
