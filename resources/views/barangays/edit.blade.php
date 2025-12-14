@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <h2 class="mb-3">Edit Barangay</h2>

      {{-- Livewire edit component for this barangay --}}
      @livewire('barangays.edit', ['barangay' => $barangay])
    </div>
  </div>
</div>
@endsection
