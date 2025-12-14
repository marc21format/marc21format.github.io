@extends('layouts.app')

@section('content')
<div class="profile-page">
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

  <div class="profile-grid-wrapper">
    <div class="grid gap-6 sm:grid-cols-1">
      <div class="max-w-3xl mx-auto w-full">
        {{-- Main edit form rendered by Livewire component to keep UI consistent with create/edit flows --}}
        <livewire:profile.highschool-records.edit :record-id="$record->record_id" />
      </div>
    </div>
  </div>
</div>
@endsection
