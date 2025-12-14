<div class="profile-component-card max-w-4xl mx-auto">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">{{ $university->university_name }}</p>
            <p class="profile-card-subtitle">{{ $university->abbreviation ?? '' }}</p>
        </div>
        <div class="profile-card-actions">
            <a href="{{ route('universities.index') }}" class="gear-button text-slate-800" title="Back to list">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
            </a>
            @if(Route::has('universities.edit'))
                <a href="{{ route('universities.edit', ['id' => $university->university_id]) }}" class="gear-button text-slate-800" title="Edit">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <div class="grid grid-cols-1 gap-4">
            <div class="bg-white rounded border border-slate-100 p-4">
                <h3 class="font-semibold text-slate-900">Volunteers ({{ $volunteers->count() }})</h3>
                <div class="mt-3 space-y-2">
                    @foreach($volunteers as $v)
                        @php
                            $profile = $v->userProfile ?? null;
                            $display = $profile ? trim(($profile->f_name ?? '') . ' ' . ($profile->m_name ?? '') . ' ' . ($profile->s_name ?? '')) : $v->name;
                            $display = $display ?: $v->name;
                        @endphp
                        <div class="list-row">
                            <div class="avatar-sm">{{ strtoupper(substr($v->name ?? ($v->email ?? 'U'), 0, 1)) }}</div>
                            <div class="flex-1">
                                <div class="text-slate-900 font-semibold">@if(Route::has('profile.volunteer.show'))<a href="{{ route('profile.volunteer.show', ['user' => $v->id]) }}" class="profile-link">{{ $display }}</a>@else{{ $display }}@endif</div>
                                <div class="text-xs text-slate-400 mt-1">{{ optional($v->committeeMemberships->first())->committee->committee_name ?? '' }} {{ optional($v->committeeMemberships->first())->position->position_title ? '· ' . optional($v->committeeMemberships->first())->position->position_title : '' }}</div>
                            </div>
                            <div class="text-sm text-slate-400">{{ $v->fceerProfile->volunteer_number ?? '' ? '#'.$v->fceerProfile->volunteer_number : '' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded border border-slate-100 p-4">
                <h3 class="font-semibold text-slate-900">Degree Programs</h3>
                <ul class="mt-3 space-y-2">
                    @foreach($programCounts as $pc)
                        @if($pc['program'])
                            <li class="course-row">
                                <div>
                                    @php $p = $pc['program']; @endphp
                                    @if(Route::has('degree-programs.show'))
                                        <a href="{{ route('degree-programs.show', $p->degreeprogram_id) }}" class="text-sm font-semibold text-slate-900">{{ $p->full_degree_program_name }}</a>
                                    @else
                                        <div class="text-sm font-semibold text-slate-900">{{ $p->full_degree_program_name }}</div>
                                    @endif
                                    <div class="text-xs text-slate-500">{{ $p->program_abbreviation ?? '-' }}</div>
                                </div>
                                <div class="text-xs text-slate-500">{{ $pc['count'] }} volunteers</div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded border border-slate-100 p-4">
                <h3 class="font-semibold text-slate-900">Details</h3>
                <p class="text-sm text-slate-500 mt-2">Abbreviation: <span class="text-slate-700">{{ $university->abbreviation ?? '-' }}</span></p>
                <p class="text-sm text-slate-500 mt-1">Total volunteers: <span class="text-slate-700">{{ $volunteers->count() }}</span></p>
            </div>
        </div>
    </div>
</div>
