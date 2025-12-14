<?php
namespace App\Http\Livewire\DegreeFields;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeField;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Create extends Component
{
    use HandlesTitleAbbreviation;
    public $field_name;
    public $abbreviation;
    public $abbreviationTouched = false;
    public $degreelevel_ids = [];
    public $degreetype_ids = [];

    protected $rules = [
        'field_name' => 'required|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
        'degreelevel_ids' => 'nullable|array',
        'degreelevel_ids.*' => 'nullable|exists:degree_levels,degreelevel_id',
        'degreetype_ids' => 'nullable|array',
        'degreetype_ids.*' => 'nullable|exists:degree_types,degreetype_id',
    ];

    public function mount()
    {
        if (! Auth::check()) abort(403);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->field_name);
        }
        $field = DegreeField::create([
            'field_name' => $this->field_name,
            'abbreviation' => $this->abbreviation,
        ]);

        // create mappings for each selected combination of level and type
        if (! empty($this->degreelevel_ids)) {
            if (! empty($this->degreetype_ids)) {
                foreach ($this->degreelevel_ids as $lvl) {
                    foreach ($this->degreetype_ids as $typ) {
                        \App\Models\DegreeFieldMapping::firstOrCreate([
                            'degreefield_id' => $field->degreefield_id,
                            'degreelevel_id' => $lvl,
                            'degreetype_id' => $typ,
                        ]);
                    }
                }
            } else {
                // only levels selected: create mapping rows with null degreetype_id
                foreach ($this->degreelevel_ids as $lvl) {
                    \App\Models\DegreeFieldMapping::firstOrCreate([
                        'degreefield_id' => $field->degreefield_id,
                        'degreelevel_id' => $lvl,
                        'degreetype_id' => null,
                    ]);
                }
            }
        }

        $this->reset(['field_name', 'abbreviation', 'degreelevel_ids', 'degreetype_ids']);
    $this->dispatch('degreeFieldSaved');

        return redirect()->route('degree-fields.index');
    }

    public function updatedFieldName($value)
    {
        $this->field_name = $this->titleCase($value);
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->field_name);
        }
    }

    public function updatedAbbreviation($value)
    {
        $provided = trim((string)$value);
        $computed = $this->computeAbbr($this->field_name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
        $this->abbreviation = mb_strtoupper($provided ?: '', 'UTF-8');
    }

    public function render()
    {
        return view('livewire.degree-fields.create');
    }
}
