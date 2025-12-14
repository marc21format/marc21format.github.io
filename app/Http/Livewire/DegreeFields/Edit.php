<?php
namespace App\Http\Livewire\DegreeFields;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeField;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Edit extends Component
{
    use HandlesTitleAbbreviation;
    public $degreefield_id;
    public $field;
    public $field_name;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'field_name' => 'required|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount($degreefield_id)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role_id, [1,2,3])) abort(403);
        $this->field = DegreeField::findOrFail($degreefield_id);
        $this->degreefield_id = $this->field->degreefield_id;
        $this->field_name = $this->field->field_name;
        $this->abbreviation = $this->field->abbreviation;
        // detect whether abbreviation is manually different from computed
        $computed = $this->computeAbbr($this->field_name);
        $this->abbreviationTouched = ($this->abbreviation !== '' && $this->abbreviation !== $computed);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->field_name);
        }
        $this->field->update([
            'field_name' => $this->field_name,
            'abbreviation' => $this->abbreviation,
        ]);

        $this->dispatch('degreeFieldUpdated');

        return redirect()->route('degree-fields.index');
    }

    public function render()
    {
        return view('livewire.degree-fields.edit', ['field' => $this->field]);
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
}
