<?php
namespace App\Http\Livewire\DegreeLevels;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeLevel;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Edit extends Component
{
    use HandlesTitleAbbreviation;
    public $degreelevel_id;
    public $level;
    public $level_name;
    public $degree_level;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'level_name' => 'required|string|max:255',
        'degree_level' => 'nullable|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount($degreelevel_id)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role_id, [1,2,3])) abort(403);
        $this->level = DegreeLevel::findOrFail($degreelevel_id);
        $this->degreelevel_id = $this->level->degreelevel_id;
        $this->level_name = $this->level->level_name;
        $this->degree_level = $this->level->degree_level;
        $this->abbreviation = $this->level->abbreviation;
        $computed = $this->computeAbbr($this->level_name);
        $this->abbreviationTouched = ($this->abbreviation !== '' && $this->abbreviation !== $computed);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->level_name);
        }
        $this->level->update([
            'level_name' => $this->level_name,
            'degree_level' => $this->degree_level,
            'abbreviation' => $this->abbreviation,
        ]);
        $this->dispatch('degreeLevelUpdated');

        return redirect()->route('degree-levels.index');
    }

    public function updatedLevelName($value)
    {
        $this->level_name = $this->titleCase($value);
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->level_name);
        }
    }

    public function updatedAbbreviation($value)
    {
        $provided = trim((string)$value);
        $computed = $this->computeAbbr($this->level_name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
        $this->abbreviation = mb_strtoupper($provided ?: '', 'UTF-8');
    }

    public function render()
    {
        return view('livewire.degree-levels.edit', ['level' => $this->level]);
    }
}
