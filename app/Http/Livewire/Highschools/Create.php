<?php
namespace App\Http\Livewire\Highschools;

use Livewire\Component;
use App\Models\Highschool;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Create extends Component
{
    use HandlesTitleAbbreviation;
    // public properties bound to the form
    public $name;
    public $abbreviation = '';
    public $type = 'public';
    // when true, user has manually edited the abbreviation and we should not
    // override it from the computed value on name updates
    public $abbreviationTouched = false;

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
        'abbreviation' => ['nullable', 'string', 'max:20'],
        'type' => ['required', 'in:public,private'],
    ];

    /**
     * Called by Livewire from the create view.
     * Matches the action name Livewire reports missing: storeHighschool
     */
    public function storeHighschool()
    {
        $this->validate();
        // Fallback: if abbreviation is empty (Livewire/JS not running), compute server-side
        if (! $this->abbreviation || trim((string) $this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->name);
        }

        // Use the DB column your app uses: 'highschool_name'
        Highschool::create([
            'highschool_name' => $this->name,
            // only save abbreviation/type if columns exist; harmless if extra columns are ignored
            'abbreviation' => $this->abbreviation,
            'type' => $this->type,
        ]);

        // saved

        // flash a success message so users without JS see it on fallback
        session()->flash('success', 'Highschool created.');

        // Use Livewire's redirect helper to ensure a client-side redirect when
        // this component is invoked via an XHR request.
        $this->redirectRoute('highschools.index');
    }

    /**
     * Livewire hook called whenever $name is updated.
     * We'll compute a simple abbreviation server-side so the abbreviation field
     * stays in sync without relying on Alpine on the view.
     */
    public function updatedName($value)
    {
        // Normalize to title case (capitalize first letter of each significant word,
        // leave common articles/conjunctions lowercase except when they are the first word)
        $this->name = $this->titleCase($value);

        // Only auto-compute abbreviation when the user hasn't manually edited it.
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->name);
        }

        // updatedName runs and updates $abbreviation unless user touched it
    }

    /**
     * When abbreviation input is updated we track whether the user has
     * manually entered something. If it's empty again, allow auto-compute.
     */
    public function updatedAbbreviation($value)
    {
        $provided = trim((string) $value);
        // If the provided abbreviation equals the server-computed abbreviation for
        // the current name, treat it as auto-generated (not manually touched).
        $computed = $this->computeAbbr($this->name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
    }

    // computeAbbr() and titleCase() provided by HandlesTitleAbbreviation trait

    public function render()
    {
        return view('livewire.highschools.create');
    }

    // computeAbbr() and titleCase() provided by HandlesTitleAbbreviation trait
}
