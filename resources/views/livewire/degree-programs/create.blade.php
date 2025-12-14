<div class="profile-component-card">
    <div class="profile-card-header">
        <div>
            <p class="profile-card-title">Create Degree Program</p>
            <p class="profile-card-subtitle">New program details</p>
        </div>
        <div class="profile-card-actions">
            <button type="button" class="gear-button text-slate-800" wire:click.prevent="save" title="Save">
                <i class="fa fa-check" aria-hidden="true"></i>
            </button>
            @if(\Illuminate\Support\Facades\Route::has('degree-programs.index'))
                <a href="{{ route('degree-programs.index') }}" class="gear-button text-slate-800" title="Cancel">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    <div class="border-t border-slate-200 pt-4">
        <form wire:submit.prevent="save">
            @csrf
            <div class="form-group">
                <label class="form-label">Program name</label>
                <input id="dp-name-input" type="text" wire:model="full_degree_program_name" class="form-input w-full h-10" />
                <p class="text-sm text-gray-500 mt-1">Program abbreviation will be auto-generated from selected level, type, and field</p>
                @error('full_degree_program_name') <x-input-error>{{ $message }}</x-input-error> @enderror
            </div>

            <div class="form-row flex items-center gap-4">
                <div class="form-col w-1/3 min-w-0">
                    <label class="form-label">Degree level</label>
                    <select wire:model="degreelevel_id" class="form-select" id="dp-level-select">
                        <option value="">--</option>
                        @foreach(\App\Models\DegreeLevel::orderBy('level_name')->get() as $l)
                            <option value="{{ $l->degreelevel_id }}" data-abbr="{{ $l->abbreviation ?? '' }}" data-name="{{ $l->level_name }}">{{ $l->level_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-col w-1/3 min-w-0">
                    <label class="form-label">Degree type</label>
                    <select wire:model="degreetype_id" class="form-select" id="dp-type-select">
                        <option value="">--</option>
                        @foreach(\App\Models\DegreeType::orderBy('type_name')->get() as $t)
                            <option value="{{ $t->degreetype_id }}" data-abbr="{{ $t->abbreviation ?? '' }}">{{ $t->type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-col w-1/3 min-w-0">
                    <label class="form-label">Degree field</label>
                    <select wire:model="degreefield_id" class="form-select" id="dp-field-select">
                        <option value="">--</option>
                        @foreach(\App\Models\DegreeField::orderBy('field_name')->get() as $f)
                            <option value="{{ $f->degreefield_id }}" data-abbr="{{ $f->abbreviation ?? '' }}">{{ $f->field_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <div class="mt-3 text-sm text-gray-600">
            <a href="{{ route('degree-levels.create') }}">Create degree level</a> ·
            <a href="{{ route('degree-types.create') }}">Create degree type</a> ·
            <a href="{{ route('degree-fields.create') }}">Create degree field</a>
        </div>
    </div>
</div>

<script>
    // Abbreviation is auto-generated server-side via Livewire
</script>