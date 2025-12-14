<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="px-5 pt-5 pb-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-1000">Degree Levels</h1>
                <p class="text-sm text-slate-500">Manage degree levels</p>
            </div>
            <div class="flex items-center gap-3">
                @if(\Illuminate\Support\Facades\Route::has('degree-levels.create'))
                    <a href="{{ route('degree-levels.create') }}" class="text-gray-600 hover:text-gray-800" title="Add degree level">
                        <i class="fa fa-plus"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="px-5 pb-3">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white-50 p-2">
            <input type="text" wire:model.live="search" placeholder="Search degree levels..." class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200" />
        </div>
    </div>

    <div class="flex justify-end gap-6 px-5 border-b border-slate-100 pb-3">
        <button type="button" data-view="all" class="degree-levels-global-tab text-sm transition text-slate-400 hover:text-slate-700">All</button>
        <button type="button" data-view="volunteers" class="degree-levels-global-tab text-sm transition text-slate-400 hover:text-slate-700">Volunteers</button>
    </div>

    <div class="px-5">
        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">

        <div class="grid grid-cols-1 gap-4">
            @foreach($levels as $l)
                @php
                    $volCount = $volCounts[$l->degreelevel_id] ?? 0;
                    $programCount = $l->program_count ?? 0;
                @endphp

                <div class="bg-white rounded border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-3">
                                    <p class="text-lg font-semibold text-slate-900">{{ $l->level_name }}</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs">{{ $volCount }} volunteers</span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-slate-500">{{ $l->abbreviation ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 degree-levels-actions">
                            @if(\Illuminate\Support\Facades\Route::has('degree-levels.show'))
                                <a href="{{ route('degree-levels.show', ['level' => $l->degreelevel_id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Show">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </a>
                            @endif
                            @if(\Illuminate\Support\Facades\Route::has('degree-levels.edit'))
                                <a href="{{ route('degree-levels.edit', ['degreelevel_id' => $l->degreelevel_id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Edit">
                                    <i class="fa fa-edit" aria-hidden="true"></i>
                                </a>
                            @endif

                            @php
                                $deleteUrl = \Illuminate\Support\Facades\Route::has('degree-levels.destroy') ? route('degree-levels.destroy', ['level' => $l->degreelevel_id]) : url('degree-levels/'.$l->degreelevel_id);
                            @endphp
                            <form method="POST" action="{{ $deleteUrl }}" style="display:inline" onsubmit="return confirm('Delete this degree level?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-500 hover:bg-red-50" title="Delete">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="degree-levels-view degree-levels-volunteers" data-level="{{ $l->degreelevel_id }}">
                        <div class="mt-4 bg-slate-50 rounded border border-slate-100 p-4">
                            <div class="mt-3 space-y-2">
                                @php
                                    $volUsers = collect();
                                    foreach ($l->degreePrograms as $p) {
                                        foreach ($p->educationalRecords as $er) {
                                            if ($er->user) {
                                                $volUsers->push($er->user);
                                            }
                                        }
                                    }
                                    $volUsers = $volUsers->unique('id');
                                @endphp
                                @foreach($volUsers as $vu)
                                    @php
                                        $profile = $vu->userProfile ?? null;
                                        $f_name = $profile ? ($profile->f_name ?? null) : null;
                                        $m_name = $profile ? ($profile->m_name ?? null) : null;
                                        $s_name = $profile ? ($profile->s_name ?? null) : null;
                                        $gen = $profile ? ($profile->generational_suffix ?? null) : null;
                                        $credential = null;
                                        if (isset($vu->professionalCredentials) && $vu->professionalCredentials->isNotEmpty()) {
                                            $credential = $vu->professionalCredentials->last();
                                        }
                                        $prefix = $credential && $credential->prefix ? ($credential->prefix->abbreviation ?? $credential->prefix->title) : null;
                                        $suffix = $credential && $credential->suffix ? ($credential->suffix->abbreviation ?? $credential->suffix->title) : null;
                                        $displayName = trim(($prefix ? $prefix.' ' : '') . ($f_name ?? $vu->name) . ($m_name ? ' '.$m_name : '') . ($s_name ? ' '.$s_name : '') . ($gen ? ' '.$gen : '') . ($suffix ? ', '.$suffix : ''));
                                        $fceerNo = $vu->fceerProfile ? ($vu->fceerProfile->volunteer_number ?? null) : null;
                                    @endphp
                                    <div class="bg-white rounded border border-slate-100 p-4">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="avatar-sm">{{ strtoupper(substr($vu->name ?? ($vu->email ?? 'U'), 0, 1)) }}</div>
                                            <div>
                                                @if(\Illuminate\Support\Facades\Route::has('profile.volunteer.show'))
                                                    <a href="{{ route('profile.volunteer.show', ['user' => $vu->id]) }}" class="text-slate-900 font-semibold text-base text-left">{{ $displayName }}</a>
                                                @else
                                                    <span class="text-slate-900 font-semibold text-base text-left">{{ $displayName }}</span>
                                                @endif
                                                <p class="text-sm text-slate-400 text-left">{{ $fceerNo ? '#'.$fceerNo : '' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        </div>

                <div class="mt-4">
            {{ $levels->links() }}
        </div>
    </div>
</div>

<script>
    (function(){
        var tabs = document.querySelectorAll('.degree-levels-global-tab');
        function setView(view) {
            document.querySelectorAll('.degree-levels-view').forEach(function(el){
                if (view === 'all') {
                    el.classList.add('hidden');
                } else if (view === 'volunteers') {
                    el.classList.remove('hidden');
                }
            });
            document.querySelectorAll('.degree-levels-actions').forEach(function(el){
                if (view === 'all') {
                    el.classList.remove('hidden');
                } else if (view === 'volunteers') {
                    el.classList.add('hidden');
                }
            });
        }
        tabs.forEach(function(t){
            t.addEventListener('click', function(){ setView(t.getAttribute('data-view')); });
        });
        // init
        setView('all');
    })();
</script>

<style>
.list-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f1f5f9;
}
.list-row:last-child {
    border-bottom: none;
}
.avatar-sm {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background-color: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}
</style>

</div>
