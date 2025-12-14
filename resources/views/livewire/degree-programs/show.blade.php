<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="px-5 pt-5 pb-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-1000">{{ $program->full_degree_program_name }}</h1>
                <p class="text-sm text-slate-500">{{ $program->program_abbreviation ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if(\Illuminate\Support\Facades\Route::has('degree-programs.edit'))
                    <a href="{{ route('degree-programs.edit', ['degreeprogram_id' => $program->degreeprogram_id]) }}" class="text-gray-600 hover:text-gray-800" title="Edit">
                        <i class="fa fa-edit"></i>
                    </a>
                @endif
                <a href="{{ route('degree-programs.index') }}" class="text-gray-600 hover:text-gray-800" title="Back">
                    <i class="fa fa-arrow-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="px-5 pb-5">
        <div class="space-y-6">

            <!-- Program Details -->
            <div class="bg-white rounded border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Program Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500">Level</p>
                        <p class="text-base font-medium text-slate-900">
                            @if ($program->degreeLevel)
                                <a href="{{ route('degree-levels.show', ['level' => $program->degreeLevel->degreelevel_id]) }}" class="text-blue-600 hover:underline">
                                    {{ $program->degreeLevel->level_name }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Type</p>
                        <p class="text-base font-medium text-slate-900">
                            @if ($program->degreeType)
                                <a href="{{ route('degree-types.show', ['type' => $program->degreeType->degreetype_id]) }}" class="text-blue-600 hover:underline">
                                    {{ $program->degreeType->type_name }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Field</p>
                        <p class="text-base font-medium text-slate-900">
                            @if ($program->degreeField)
                                <a href="{{ route('degree-fields.show', ['field' => $program->degreeField->degreefield_id]) }}" class="text-blue-600 hover:underline">
                                    {{ $program->degreeField->field_name }}
                                </a>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Abbreviation</p>
                        <p class="text-base font-medium text-slate-900">{{ $program->program_abbreviation ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white rounded border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Usage Statistics</h2>
                <div class="mb-6">
                    <div class="text-3xl font-bold text-slate-900">{{ $usersCount }}</div>
                    <p class="text-sm text-slate-500">Users enrolled in this program</p>
                </div>

                <!-- Top Universities -->
                <div class="mt-6">
                    <h3 class="text-base font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fa fa-university text-slate-600"></i>
                        Top 5 Universities
                    </h3>
                    @if ($topUniversities->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left p-3 font-medium text-slate-700">University</th>
                                        <th class="text-right p-3 font-medium text-slate-700">Records</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topUniversities as $record)
                                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                                            <td class="p-3 text-slate-900">
                                                @if ($record->university)
                                                    {{ $record->university->university_name }}
                                                @else
                                                    <span class="text-slate-400">Unknown</span>
                                                @endif
                                            </td>
                                            <td class="text-right p-3">
                                                <span class="inline-block bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-sm font-medium">
                                                    {{ $record->record_count }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No university data available</p>
                    @endif
                </div>

                <!-- All Users -->
                <div class="mt-8">
                    <h3 class="text-base font-semibold text-slate-900 mb-3 flex items-center gap-2">
                        <i class="fa fa-users text-slate-600"></i>
                        All Users ({{ count($users) }})
                    </h3>
                    @if ($users->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full border-collapse">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200">
                                        <th class="text-left p-3 font-medium text-slate-700">#</th>
                                        <th class="text-left p-3 font-medium text-slate-700">User Name</th>
                                        <th class="text-left p-3 font-medium text-slate-700">Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $record)
                                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                                            <td class="p-3 text-slate-900">{{ $loop->iteration }}</td>
                                            <td class="p-3 text-slate-900">
                                                @if ($record->user)
                                                    {{ $record->user->name }}
                                                @else
                                                    <span class="text-slate-400">Unknown</span>
                                                @endif
                                            </td>
                                            <td class="p-3 text-slate-900">
                                                @if ($record->user)
                                                    {{ $record->user->email }}
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No users found</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
