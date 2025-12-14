@extends('layouts.app')

@section('content')
  <div class="px-8 py-6 pb-12">
    @livewire('highschools.edit', ['highschoolId' => $highschool->highschool_id])
  </div>
@endsection
