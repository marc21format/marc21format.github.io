@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
            <div class="profile-card-header">
                <div>
                    <p class="profile-card-title">Edit Volunteer Subject</p>
                    <p class="profile-card-subtitle">Update subject details</p>
                </div>
                <div class="profile-card-actions">
                    <button type="submit" form="volunteer-subject-form" class="gear-button text-slate-800" title="Save">
                        <i class="fa fa-check" aria-hidden="true"></i>
                    </button>
                    <a href="{{ route('volunteer_subjects.index') }}" class="gear-button text-slate-800" title="Cancel">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </a>
                    <button type="submit" form="delete-form" class="gear-button text-red-600" onclick="return confirm('Delete this subject?');" title="Delete">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <form id="volunteer-subject-form" method="POST" action="{{ route('volunteer_subjects.update', ['subject' => $subject->subject_id]) }}">
                    @csrf
                    @method('PATCH')

                    <div class="form-group">
                        <label class="form-label">Subject name</label>
                        <input type="text" name="subject_name" class="form-input w-full h-10" value="{{ old('subject_name', $subject->subject_name) }}" />
                        @error('subject_name') <x-input-error>{{ $message }}</x-input-error> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Subject code (optional)</label>
                        <input type="text" name="subject_code" class="form-input w-full h-10" value="{{ old('subject_code', $subject->subject_code) }}" />
                        @error('subject_code') <x-input-error>{{ $message }}</x-input-error> @enderror
                    </div>

                    {{-- actions moved to header --}}
                </form>

                <form id="delete-form" method="POST" action="{{ route('volunteer_subjects.destroy', $subject->subject_id) }}" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
