@extends('layouts.app')

@section('content')
<div class="px-8 py-6 pb-12">
    {{-- Livewire-based create form (uses Livewire + Alpine, server-side auth) --}}
    <livewire:profile.student.highschool-subject-records.create :userId="$user->id" />
</div>
@endsection