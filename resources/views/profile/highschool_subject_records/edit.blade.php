@extends('layouts.app')

@section('content')
<div class="px-8 py-6 pb-12">
    {{-- Mount the Livewire edit component which contains the form and behavior --}}
    <livewire:profile.student.highschool-subject-records.edit :record-id="$record->record_id" />
</div>
@endsection
