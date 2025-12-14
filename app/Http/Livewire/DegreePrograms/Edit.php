<?php
namespace App\Http\Livewire\DegreePrograms;

use Livewire\Component;

class Edit extends Component
{
    public $degreeprogram_id;
    public $program;
    public $full_degree_program_name;
    public $program_abbreviation;
    public $degreelevel_id;
    public $degreetype_id;
    public $degreefield_id;

    protected $rules = [
        'full_degree_program_name' => 'required|string|max:255',
        'program_abbreviation' => 'nullable|string|max:45',
        'degreelevel_id' => 'required|exists:degree_levels,degreelevel_id',
        'degreetype_id' => 'nullable|exists:degree_types,degreetype_id',
        'degreefield_id' => 'nullable|exists:degree_fields,degreefield_id',
    ];

    public function mount($degreeprogram_id)
    {
        $this->degreeprogram_id = $degreeprogram_id;
        $this->program = \App\Models\DegreeProgram::findOrFail($degreeprogram_id);
        $this->full_degree_program_name = $this->program->full_degree_program_name;
        $this->program_abbreviation = $this->program->program_abbreviation;
        $this->degreelevel_id = $this->program->degreelevel_id;
        $this->degreetype_id = $this->program->degreetype_id;
        $this->degreefield_id = $this->program->degreefield_id;
    }

    public function updatedFullDegreeProgramName($value)
    {
        $this->updateAbbreviation();
    }

    public function updatedDegreelevelId($value)
    {
        $this->updateAbbreviation();
    }

    public function updatedDegreetypeId($value)
    {
        $this->updateAbbreviation();
    }

    public function updatedDegreefieldId($value)
    {
        $this->updateAbbreviation();
    }

    private function updateAbbreviation()
    {
        $levelAbbr = '';
        $typeAbbr = '';
        $fieldAbbr = '';
        $isDoctor = false;

        // get abbreviation from selected level
        if ($this->degreelevel_id) {
            $level = \App\Models\DegreeLevel::find($this->degreelevel_id);
            if ($level && $level->abbreviation) {
                $levelAbbr = $level->abbreviation;
                $isDoctor = stripos($level->level_name, 'doctor') !== false;
            }
        }

        // get abbreviation from selected type
        if ($this->degreetype_id) {
            $type = \App\Models\DegreeType::find($this->degreetype_id);
            if ($type && $type->abbreviation) {
                $typeAbbr = $type->abbreviation;
            }
        }

        // get abbreviation from selected field
        if ($this->degreefield_id) {
            $field = \App\Models\DegreeField::find($this->degreefield_id);
            if ($field && $field->abbreviation) {
                $fieldAbbr = $field->abbreviation;
            }
        }

        // concatenate: if doctor, put level at end; otherwise level first
        if ($isDoctor) {
            $this->program_abbreviation = $typeAbbr . $fieldAbbr . $levelAbbr;
        } else {
            $this->program_abbreviation = $levelAbbr . $typeAbbr . $fieldAbbr;
        }
    }

    public function save()
    {
        $this->validate();
        $this->program->update([
            'full_degree_program_name' => $this->full_degree_program_name,
            'program_abbreviation' => $this->program_abbreviation,
            'degreelevel_id' => $this->degreelevel_id,
            'degreetype_id' => $this->degreetype_id ?: null,
            'degreefield_id' => $this->degreefield_id ?: null,
        ]);
        $this->dispatch('degreeProgramUpdated');

        return redirect()->route('degree-programs.index');
    }

    public function render()
    {
        return view('livewire.degree-programs.edit', ['program' => $this->program]);
    }
}
