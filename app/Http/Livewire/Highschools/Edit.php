<?php
namespace App\Http\Livewire\Highschools;

use Livewire\Component;
use App\Models\Highschool;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Edit extends Component
{
    use HandlesTitleAbbreviation;

    public $highschool;

    public $name = '';
    public $abbreviation = '';
    public $type = 'public';
    public $abbreviationTouched = false;

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'abbreviation' => ['nullable', 'string', 'max:20'],
        'type' => ['required', 'in:public,private'],
    ];

    public function mount($highschoolId = null)
    {
        if ($highschoolId) {
            $this->highschool = Highschool::find($highschoolId);
            if ($this->highschool) {
                $this->name = $this->highschool->highschool_name ?? '';
                $this->abbreviation = $this->highschool->abbreviation ?? '';
                $this->type = $this->highschool->type ?? 'public';
                // If the existing abbreviation differs from computed, mark as touched
                $computed = $this->computeAbbr($this->name);
                $this->abbreviationTouched = ($this->abbreviation !== '' && $this->abbreviation !== $computed);
            }
        }
    }

    public function updatedName($value)
    {
        $this->name = $this->titleCase($value);
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->name);
        }
        // updatedName runs and updates $abbreviation unless user touched it
    }

    public function updatedAbbreviation($value)
    {
        $provided = trim((string) $value);
        $computed = $this->computeAbbr($this->name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
    }

    public function updateHighschool()
    {
        $this->validate();

        if (! $this->highschool) {
            session()->flash('error', 'Highschool not found');
            return;
        }

        $this->highschool->update([
            'highschool_name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'type' => $this->type,
        ]);

        // updated

        session()->flash('success', 'Highschool updated.');
        $this->redirectRoute('highschools.index');
    }

    public function render()
    {
        return view('livewire.highschools.edit');
    }

    // computeAbbr() and titleCase() provided by HandlesTitleAbbreviation trait
}
