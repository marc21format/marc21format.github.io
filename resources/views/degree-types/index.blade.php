@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg">
            <!-- Header with title -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h1 class="text-3xl font-bold text-gray-900">Degree Types</h1>
            </div>

            <!-- Content -->
            <div class="p-6">
                @livewire('degree-types.index')
            </div>
        </div>
    </div>
</div>
@endsection
