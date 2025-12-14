@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
            <div class="px-5 pt-5 pb-2">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div class="flex flex-col">
                        <h1 class="text-xl font-bold text-slate-1000">Volunteer Subjects</h1>
                        <p class="text-sm text-slate-500">Manage volunteer subjects</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if(\Illuminate\Support\Facades\Route::has('volunteer_subjects.create'))
                            <a href="{{ route('volunteer_subjects.create') }}" class="text-gray-600 hover:text-gray-800" title="Add subject">
                                <i class="fa fa-plus"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="px-5 pb-3">
                <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white-50 p-2">
                    <input type="text" name="search" placeholder="Search volunteer subjects..." class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200" />
                </div>
            </div>

            <div class="px-5 pb-5">
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="text-left p-3 font-medium text-slate-700 flex items-center gap-2">
                                    <i class="fa fa-book text-slate-600"></i>
                                    Subject Code
                                </th>
                                <th class="text-left p-3 font-medium text-slate-700 flex items-center gap-2">
                                    <i class="fa fa-tag text-slate-600"></i>
                                    Subject Name
                                </th>
                                <th class="text-left p-3 font-medium text-slate-700">Subname</th>
                                <th class="text-left p-3 font-medium text-slate-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subjects as $s)
                                <tr class="border-b border-slate-100 hover:bg-slate-50">
                                    <td class="p-3 text-slate-900">{{ $s->subject_code }}</td>
                                    <td class="p-3 text-slate-900">{{ $s->subject_name }}</td>
                                    <td class="p-3 text-slate-900">{{ $s->subject_subname ?? '-' }}</td>
                                    <td class="p-3">
                                        @if(\Illuminate\Support\Facades\Route::has('volunteer_subjects.edit'))
                                            <a href="{{ route('volunteer_subjects.edit', ['subject' => $s->subject_id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('volunteer_subjects.destroy', ['subject' => $s->subject_id]) }}" style="display:inline" onsubmit="return confirm('Delete this subject?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-500 hover:bg-red-50" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($subjects, 'hasPages') && $subjects->hasPages())
                    <div class="mt-4">
                        {{ $subjects->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
