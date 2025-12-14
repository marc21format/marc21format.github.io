@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <h2 class="mb-3">Add Province</h2>

      {{-- Livewire create form --}}
      @livewire('provinces.create')
    </div>
  </div>
</div>
@endsection
