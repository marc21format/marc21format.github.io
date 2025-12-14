@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Barangays</h2>
        <a href="{{ route('barangays.create') }}" class="btn btn-primary">Add Barangay</a>
      </div>

      {{-- Livewire-powered barangays list --}}
      @livewire('barangays.index')
    </div>
  </div>
</div>
@endsection
