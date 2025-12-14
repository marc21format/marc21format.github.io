@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card mt-4 form-card form-card--wide">
    <div class="card-body">
      <h2 class="mb-3">Edit Province</h2>

      {{-- Livewire edit component for this province --}}
      @livewire('provinces.edit', ['province' => $province])

      <div class="mt-4 d-flex justify-content-end">
        <form method="POST" action="{{ route('provinces.destroy', $province->province_id) }}" onsubmit="return confirm('Delete this province?')">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">Delete province</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
