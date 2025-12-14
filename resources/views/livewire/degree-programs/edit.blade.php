<div class="profile-form-card">
    <h3 class="profile-card-title">Edit Degree Program</h3>

    <form wire:submit.prevent="save">
        <div class="form-group">
            <label class="form-label">Program name</label>
            <input id="dp-name-input" type="text" wire:model="full_degree_program_name" class="form-input" />
            <p class="text-sm text-gray-500 mt-1">Program abbreviation will be auto-generated from selected level, type, and field</p>
            @error('full_degree_program_name') <x-input-error>{{ $message }}</x-input-error> @enderror
        </div>

        <div class="form-row">
            <div class="form-col">
                <label class="form-label">Degree level</label>
                <select wire:model="degreelevel_id" class="form-select" id="dp-level-select">
                    <option value="">--</option>
                    @foreach(\App\Models\DegreeLevel::orderBy('level_name')->get() as $l)
                        <option value="{{ $l->degreelevel_id }}" data-abbr="{{ $l->abbreviation ?? '' }}" data-name="{{ $l->level_name }}">{{ $l->level_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-col">
                <label class="form-label">Degree type</label>
                <select wire:model="degreetype_id" class="form-select" id="dp-type-select">
                    <option value="">--</option>
                    @foreach(\App\Models\DegreeType::orderBy('type_name')->get() as $t)
                        <option value="{{ $t->degreetype_id }}" data-abbr="{{ $t->abbreviation ?? '' }}">{{ $t->type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-col">
                <label class="form-label">Degree field</label>
                <select wire:model="degreefield_id" class="form-select" id="dp-field-select">
                    <option value="">--</option>
                    @foreach(\App\Models\DegreeField::orderBy('field_name')->get() as $f)
                        <option value="{{ $f->degreefield_id }}" data-abbr="{{ $f->abbreviation ?? '' }}">{{ $f->field_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
        </div>
    </form>
</div>

<script>
    // Abbreviation is auto-generated server-side via Livewire
</script>
