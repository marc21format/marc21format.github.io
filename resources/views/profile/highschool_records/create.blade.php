@extends('layouts.app')

@section('content')
<div class="profile-page">
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

  <div class="profile-grid-wrapper">
    <div class="grid gap-6 sm:grid-cols-1">
      <div class="max-w-3xl mx-auto w-full">
        {{-- Main form rendered by Livewire component so UI is shared with other Livewire-driven flows --}}
        <livewire:profile.highschool-records.create :userId="$user->id" />
      </div>
    </div>
  </div>
</div>
@endsection
