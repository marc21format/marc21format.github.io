<?php
namespace App\Http\Livewire\Universities;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\University;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Edit extends Component
{
    use HandlesTitleAbbreviation;
    public $university_id;
    public $university;
    public $university_name;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'university_name' => 'required|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount($university_id)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role_id, [1,2,3])) abort(403);
        $this->university = University::findOrFail($university_id);
        $this->university_id = $this->university->university_id;
        $this->university_name = $this->university->university_name;
        $this->abbreviation = $this->university->abbreviation;
        $computed = $this->computeAbbr($this->university_name);
        $this->abbreviationTouched = ($this->abbreviation !== '' && $this->abbreviation !== $computed);
    }

    public function save()
    {
        $this->validate();
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->university_name);
        }
        $this->university->update([
            'university_name' => $this->university_name,
            'abbreviation' => $this->abbreviation,
        ]);

        $this->dispatch('universityUpdated');

        return redirect()->route('universities.index');
    }

    public function updatedUniversityName($value)
    {
        $this->university_name = $this->titleCase($value);
        if (! $this->abbreviationTouched) {
            $this->abbreviation = $this->computeAbbr($this->university_name);
        }
    }

    public function updatedAbbreviation($value)
    {
        $provided = trim((string) $value);
        $computed = $this->computeAbbr($this->university_name);
        $this->abbreviationTouched = ($provided !== '' && $provided !== $computed);
        $this->abbreviation = mb_strtoupper($provided ?: '', 'UTF-8');
    }

    public function render()
    {
        return view('livewire.universities.edit', ['university' => $this->university]);
    }
}
