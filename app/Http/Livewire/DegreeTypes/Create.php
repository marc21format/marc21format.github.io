<?php
namespace App\Http\Livewire\DegreeTypes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeType;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Create extends Component
{
    use HandlesTitleAbbreviation;
    public $type_name;
    public $degreelevel_ids = [];
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'type_name' => 'required|string|max:255',
        'degreelevel_ids' => 'nullable|array',
        'degreelevel_ids.*' => 'nullable|exists:degree_levels,degreelevel_id',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount()
    {
        if (! Auth::check()) abort(403);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->type_name);
        }
        // DB requires degreelevel_id (column is not nullable). Use the first selected level
        // as the primary degreelevel_id for the record. If none selected, fall back to the
        // first existing degree level in the database. If no degree levels exist, fail.
        $primaryLevel = null;
        if (! empty($this->degreelevel_ids)) {
            $primaryLevel = $this->degreelevel_ids[0];
        } else {
            $primaryLevel = \App\Models\DegreeLevel::orderBy('level_name')->value('degreelevel_id');
        }

        if (! $primaryLevel) {
            $this->addError('degreelevel_ids', 'No degree levels exist. Create a degree level first or select one.');
            return;
        }

        $type = DegreeType::create([
            'degreelevel_id' => $primaryLevel,
            'type_name' => $this->type_name,
            'abbreviation' => $this->abbreviation,
        ]);

        // sync degree level mappings if provided
        if (! empty($this->degreelevel_ids)) {
            $type->degreeLevels()->sync($this->degreelevel_ids);
        }

        $this->reset(['type_name', 'degreelevel_ids', 'abbreviation']);
    $this->dispatch('degreeTypeSaved');

        return redirect()->route('degree-types.index');
    }

    public function updatedTypeName($value)
    {
        $this->type_name = $this->titleCase($value);
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->type_name);
        }
    }

    public function updatedAbbreviation($value)
    {
        $provided = trim((string)$value);
        $computed = $this->computeAbbr($this->type_name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
        $this->abbreviation = mb_strtoupper($provided ?: '', 'UTF-8');
    }

    public function render()
    {
        return view('livewire.degree-types.create');
    }
}
