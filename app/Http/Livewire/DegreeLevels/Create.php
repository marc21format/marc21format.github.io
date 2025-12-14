<?php
namespace App\Http\Livewire\DegreeLevels;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeLevel;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Create extends Component
{
    use HandlesTitleAbbreviation;
    public $level_name;
    public $degree_level;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'level_name' => 'required|string|max:255',
        'degree_level' => 'required|string|max:45',
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
            $this->abbreviation = $this->computeAbbr($this->level_name);
        }
        DegreeLevel::create([
            'level_name' => $this->level_name,
            'degree_level' => $this->degree_level,
            'abbreviation' => $this->abbreviation,
        ]);
        $this->reset(['level_name', 'degree_level', 'abbreviation']);
    $this->dispatch('degreeLevelSaved');

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
        return view('livewire.degree-levels.create');
    }
}
