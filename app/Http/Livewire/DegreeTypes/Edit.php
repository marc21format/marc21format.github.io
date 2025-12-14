<?php
namespace App\Http\Livewire\DegreeTypes;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeType;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Edit extends Component
{
    use HandlesTitleAbbreviation;
    public $degreetype_id;
    public $type;
    public $type_name;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'type_name' => 'required|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount($degreetype_id)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role_id, [1,2,3])) abort(403);
        $this->type = DegreeType::findOrFail($degreetype_id);
        $this->degreetype_id = $this->type->degreetype_id;
        $this->type_name = $this->type->type_name;
        $this->abbreviation = $this->type->abbreviation;
        $computed = $this->computeAbbr($this->type_name);
        $this->abbreviationTouched = ($this->abbreviation !== '' && $this->abbreviation !== $computed);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->type_name);
        }
        $this->type->update([
            'type_name' => $this->type_name,
            'abbreviation' => $this->abbreviation,
        ]);

        $this->dispatch('degreeTypeUpdated');

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
        return view('livewire.degree-types.edit', ['type' => $this->type]);
    }
}
