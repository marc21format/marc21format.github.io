<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjects;

use Livewire\Component;
use App\Models\HighschoolSubjectRecord;

class Edit extends Component
{
    public $recordId;
    public $subject_name;
    public $grade;

    protected $rules = [
        'subject_name' => 'required|string|max:255',
        'grade' => 'nullable|numeric',
    ];

    public function mount($recordId)
    {
        $this->recordId = $recordId;
        $rec = HighschoolSubjectRecord::findOrFail($recordId);
        $this->subject_name = $rec->subject_name;
        $this->grade = $rec->grade;
    }

    public function update()
    {
        $this->validate();
        $rec = HighschoolSubjectRecord::findOrFail($this->recordId);
        $rec->update([
            'subject_name' => $this->subject_name,
            'grade' => $this->grade,
        ]);

       if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolSubjectUpdated');
        }
    }

    public function render()
    {
        return view('livewire.highschool-subjects.edit');
    }
}
