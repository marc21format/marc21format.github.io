@php
    $degreePrograms = $degreePrograms ?? \App\Models\DegreeProgram::with('degreeLevel')->orderBy('full_degree_program_name')->get();
    $universities = $universities ?? \App\Models\University::orderBy('university_name')->get();
    $userId = $userId ?? ($user->id ?? request()->route('user') ?? auth()->id());
@endphp

<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Edit Educational Record</p>
            @php
                $prefix = optional(optional(optional($user)->professionalCredentials->last())->prefix)->title ?? '';
                $f = optional(optional($user)->userProfile)->f_name ?? optional($user)->name ?? '';
                $m = optional(optional($user)->userProfile)->m_name ?? '';
                $l = optional(optional($user)->userProfile)->l_name ?? '';
                $gen = optional(optional($user)->userProfile)->generational_suffix ?? optional(optional(optional($user)->professionalCredentials->last())->suffix)->title ?? '';
                $creds = optional($user)->professionalCredentials ? optional($user)->professionalCredentials->pluck('credential_title')->filter()->join(', ') : '';
                $displayName = trim(trim($prefix).' '.trim($f).' '.trim($m).' '.trim($l));
            @endphp
            <p class="profile-card-subtitle">{{ $displayName ?: (optional($user)->email ?? 'User') }}@if($gen) , {{ $gen }}@endif@if($creds) — {{ $creds }}@endif</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            <a href="{{ in_array((int) (optional($user)->role_id ?? optional(optional($user)->role)->role_id), [1,2,3]) ? route('profile.volunteer.show', ['user' => $userId]) : route('profile.student.show', ['user' => $userId]) }}" class="gear-button text-slate-800" title="Cancel">
                <i class="fa fa-times" aria-hidden="true"></i>
            </a>
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            @csrf

            <div class="form-group">
                <div class="flex items-center justify-between">
                    <label for="degreeprogram_id" class="form-label">Degree Program</label>
                    <div class="flex items-center gap-2">
                        @php
                            $degreeListRoute = null;
                            $degreeCreateRoute = null;
                            $candidatesIndex = ['degreeprograms.index','degree_programs.index','degree-programs.index','degreeprogram.index','degree_program.index'];
                            $candidatesCreate = ['degreeprograms.create','degree_programs.create','degree-programs.create','degreeprogram.create','degree_program.create'];
                            foreach ($candidatesIndex as $r) { if (\Illuminate\Support\Facades\Route::has($r)) { $degreeListRoute = $r; break; } }
                            foreach ($candidatesCreate as $r) { if (\Illuminate\Support\Facades\Route::has($r)) { $degreeCreateRoute = $r; break; } }
                        @endphp

                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()) && $degreeListRoute)
                            <a href="{{ route($degreeListRoute) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Degree programs list">
                                <i class="fa fa-list" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($degreeCreateRoute)
                            <a href="{{ route($degreeCreateRoute, ['user_id' => $userId]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Add degree program">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <select wire:model="degreeprogram_id" id="degreeprogram_id" class="form-select">
                    <option value="" class="text-slate-400">Select a Degree Program...</option>
                    @foreach($degreePrograms as $p)
                        <option value="{{ $p->degreeprogram_id }}">{{ $p->full_degree_program_name }}</option>
                    @endforeach
                </select>
                @error('degreeprogram_id') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="form-group">
                <div class="flex items-center justify-between">
                    <label class="form-label">University</label>
                    <div class="flex items-center gap-2">
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isExecutive()) && \Illuminate\Support\Facades\Route::has('universities.index'))
                            <a href="{{ route('universities.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="University list">
                                <i class="fa fa-list" aria-hidden="true"></i>
                            </a>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('universities.create'))
                            <a href="{{ route('universities.create', ['user_id' => $userId]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-slate-500 hover:bg-slate-50" title="Add university">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <select wire:model.defer="university_id" class="form-select">
                    <option value="">Select a University...</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->university_id }}">{{ $u->university_name }}</option>
                    @endforeach
                </select>
                @error('university_id') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="form-row flex items-center gap-4">
                <div class="form-col w-1/2 min-w-0">
                    <label class="form-label">Year start</label>
                    <input type="number" wire:model.defer="year_start" class="form-input w-full h-10" />
                    @error('year_start') <x-input-error>{{ $message }}</x-input-error> @enderror
                </div>
                <div class="form-col w-1/2 min-w-0">
                    <label class="form-label">Year graduated (end)</label>
                    <input type="number" wire:model.defer="year_graduated" class="form-input w-full h-10" />
                    @error('year_graduated') <x-input-error>{{ $message }}</x-input-error> @enderror
                </div>
            </div>

            @if($isBachelor)
                <div class="form-row flex items-center gap-4 mt-3">
                    <div class="form-col w-1/2 min-w-0">
                        <label class="form-label">DOST Scholarship</label>
                        <div class="flex gap-3">
                            <button type="button" wire:click.prevent="$set('DOST_Scholarship', 1)" class="{{ ($DOST_Scholarship ?? 0) == 1 ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">Yes</button>
                            <button type="button" wire:click.prevent="$set('DOST_Scholarship', 0)" class="{{ ($DOST_Scholarship ?? 0) == 0 ? 'h-10 px-4 rounded-md bg-slate-900 text-white hover:bg-slate-800 flex items-center justify-center' : 'h-10 px-4 rounded-md bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 flex items-center justify-center' }}">No</button>
                        </div>
                        @error('DOST_Scholarship') <x-input-error>{{ $message }}</x-input-error> @enderror
                    </div>

                    <div class="form-col w-1/2 min-w-0">
                        <label class="form-label">Latin honor</label>
                        <select wire:model.defer="latin_honor" class="form-select">
                            <option value="" class="text-slate-400">-- none --</option>
                            <option value="Cum Laude">Cum Laude</option>
                            <option value="Magna Cum Laude">Magna Cum Laude</option>
                            <option value="Summa Cum Laude">Summa Cum Laude</option>
                        </select>
                        @error('latin_honor') <x-input-error>{{ $message }}</x-input-error> @enderror
                    </div>
                </div>
            @endif

            {{-- actions moved to header --}}
        </form>
    </div>
</div>
