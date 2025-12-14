@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <h2 class="mb-3">Add Barangay</h2>

      {{-- Livewire create form for barangay --}}
      @livewire('barangays.create')
    </div>
  </div>
</div>
@endsection
