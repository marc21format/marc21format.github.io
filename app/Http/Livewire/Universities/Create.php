<?php
namespace App\Http\Livewire\Universities;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\University;
use App\Http\Livewire\Traits\HandlesTitleAbbreviation;

class Create extends Component
{
    use HandlesTitleAbbreviation;

    public $university_name;
    public $abbreviation;
    public $abbreviationTouched = false;

    protected $rules = [
        'university_name' => 'required|string|max:255',
        'abbreviation' => 'nullable|string|max:45',
    ];

    public function mount()
    {
        if (! Auth::check()) abort(403);
    }

    public function save()
    {
        $this->validate();
        // fallback compute abbreviation if empty
        if (! $this->abbreviation || trim((string)$this->abbreviation) === '') {
            $this->abbreviation = $this->computeAbbr($this->university_name);
        }
        University::create([
            'university_name' => $this->university_name,
            'abbreviation' => $this->abbreviation,
        ]);

        $this->reset(['university_name','abbreviation']);
        $this->dispatch('universitySaved');

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
        return view('livewire.universities.create');
    }
}
