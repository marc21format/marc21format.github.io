@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <h2 class="mb-3">Edit City</h2>

      {{-- Livewire edit component for this city --}}
      @livewire('cities.edit', ['city' => $city])

      <div class="mt-4 d-flex justify-content-end">
        <form method="POST" action="{{ route('cities.destroy', $city->city_id) }}" onsubmit="return confirm('Delete this city?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Delete city</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
