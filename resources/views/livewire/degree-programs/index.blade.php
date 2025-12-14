<div class="profile-component-card" style="font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;">
    <div class="px-5 pt-5 pb-2">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-1000">Degree Programs</h1>
                <p class="text-sm text-slate-500">Manage degree programs</p>
            </div>
            <div class="flex items-center gap-3">
                @if(\Illuminate\Support\Facades\Route::has('degree-programs.create'))
                    <a href="{{ route('degree-programs.create') }}" class="text-gray-600 hover:text-gray-800" title="Add degree program">
                        <i class="fa fa-plus"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="px-5 pb-3">
        <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white-50 p-2">
            <input type="text" wire:model.live="search" placeholder="Search degree programs..." class="flex-1 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-200" />
        </div>
    </div>

    <div class="flex justify-end gap-6 px-5 border-b border-slate-100 pb-3">
        <button type="button" data-view="all" class="degree-programs-global-tab text-sm transition text-slate-400 hover:text-slate-700">All</button>
        <button type="button" data-view="volunteers" class="degree-programs-global-tab text-sm transition text-slate-400 hover:text-slate-700">Volunteers</button>
    </div>

    <div class="px-5">
        <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">

        <div class="grid grid-cols-1 gap-4">
            @foreach($programs as $p)
                @php
                    $volUsers = collect();
                    foreach ($p->educationalRecords as $er) {
                        if ($er->user) {
                            $volUsers->push($er->user);
                        }
                    }
                    $volUsers = $volUsers->unique('id');
                    $volCount = $volUsers->count();
                @endphp

                <div class="bg-white rounded border border-slate-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-3">
                                    <p class="text-lg font-semibold text-slate-900">{{ $p->full_degree_program_name }}</p>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs">{{ $volCount }} volunteers</span>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-slate-500">{{ $p->program_abbreviation ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 program-header-actions">
                            <span class="program-header-showedit">
                                @if(\Illuminate\Support\Facades\Route::has('degree-programs.show'))
                                    <a href="{{ route('degree-programs.show', ['program' => $p->degreeprogram_id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Show">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                @endif
                                @if(\Illuminate\Support\Facades\Route::has('degree-programs.edit'))
                                    <a href="{{ route('degree-programs.edit', ['degreeprogram_id' => $p->degreeprogram_id]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Edit">
                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </span>

                            @php
                                $deleteUrl = \Illuminate\Support\Facades\Route::has('degree-programs.destroy') ? route('degree-programs.destroy', ['program' => $p->degreeprogram_id]) : url('degree-programs/'.$p->degreeprogram_id);
                            @endphp
                            <span class="program-header-delete hidden">
                                <button wire:click="delete({{ $p->degreeprogram_id }})" wire:confirm="Delete this degree program?" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-red-500 hover:bg-red-50" title="Delete">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 bg-slate-50 rounded border border-slate-100 p-4">
                        {{-- Volunteers view (default) --}}
                        <div class="program-view program-volunteers" data-program="{{ $p->degreeprogram_id }}">
                            <div class="mt-3 space-y-2">
                                @foreach($volUsers as $vu)
                                    @php
                                        $profile = $vu->userProfile ?? null;
                                        $f = $profile ? ($profile->f_name ?? null) : null;
                                        $m = $profile ? ($profile->m_name ?? null) : null;
                                        $s = $profile ? ($profile->s_name ?? null) : null;
                                        $gen = $profile ? ($profile->generational_suffix ?? null) : null;
                                        $credential = null;
                                        if (isset($vu->professionalCredentials) && $vu->professionalCredentials->isNotEmpty()) {
                                            $credential = $vu->professionalCredentials->last();
                                        }
                                        $prefix = $credential && $credential->prefix ? ($credential->prefix->abbreviation ?? $credential->prefix->title) : null;
                                        $suffix = $credential && $credential->suffix ? ($credential->suffix->abbreviation ?? $credential->suffix->title) : null;
                                        $displayName = trim(($prefix ? $prefix.' ' : '') . ($f ?? $vu->name) . ($m ? ' '.$m : '') . ($s ? ' '.$s : '') . ($gen ? ' '.$gen : '') . ($suffix ? ', '.$suffix : ''));
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
                                @if($volUsers->isEmpty())
                                    <div class="text-sm text-slate-400">No volunteers for this degree program.</div>
                                @endif
                            </div>
                        </div>

                        {{-- Details view --}}
                        <div class="program-view program-details hidden" data-program="{{ $p->degreeprogram_id }}">
                            <h6 class="text-sm font-medium text-slate-700">Details</h6>
                            <div class="mt-3 space-y-2">
                                <div class="text-sm">
                                    <strong>Degree Level:</strong> {{ $p->degreeLevel->degree_level_name ?? 'N/A' }}
                                </div>
                                <div class="text-sm">
                                    <strong>Degree Type:</strong> {{ $p->degreeType->degree_type_name ?? 'N/A' }}
                                </div>
                                <div class="text-sm">
                                    <strong>Degree Field:</strong> {{ $p->degreeField->degree_field_name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @endforeach
        </div>

                <div class="mt-4">
            {{ $programs->links() }}
        </div>
    </div>

<script>
    (function(){
        var tabs = document.querySelectorAll('.degree-programs-global-tab');
        function setView(view) {
            document.querySelectorAll('.program-view').forEach(function(el){
                if (view === 'all') {
                    el.classList.add('hidden');
                } else if (view === 'volunteers') {
                    el.classList.toggle('hidden', !el.classList.contains('program-volunteers'));
                }
            });
            tabs.forEach(function(t){
                var v = t.getAttribute('data-view');
                if (v === view) {
                    t.classList.remove('text-slate-400');
                    t.classList.add('text-slate-900','font-semibold');
                } else {
                    t.classList.remove('text-slate-900','font-semibold');
                    t.classList.add('text-slate-400');
                }
            });
            document.querySelectorAll('.bg-slate-50').forEach(function(block){
                if (view === 'all') block.classList.add('hidden'); else block.classList.remove('hidden');
            });
            document.querySelectorAll('.program-header-showedit').forEach(function(el){
                if (view === 'all') el.classList.remove('hidden'); else el.classList.add('hidden');
            });
            document.querySelectorAll('.program-header-delete').forEach(function(el){
                if (view === 'all') el.classList.remove('hidden'); else el.classList.add('hidden');
            });
        }
        tabs.forEach(function(t){
            t.addEventListener('click', function(){ setView(t.getAttribute('data-view')); });
        });
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
</div>


