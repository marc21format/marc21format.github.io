<?php
namespace App\Http\Livewire\Profile\Volunteer\SubjectTeachers;

use Livewire\Component;
use App\Models\SubjectTeacher;

class Edit extends Component
{
    public $teacherId;
    public $teacher;
    public $credential; // not used here; keep naming concise

    public $subject_id;
    public $subject_proficiency;
    public $subjects = [];

    protected $rules = [
        'subject_id' => 'required|exists:volunteer_subjects,subject_id',
        'subject_proficiency' => 'required|in:beginner,competent,proficient',
    ];

    public function mount($teacherId = null)
    {
        $this->teacherId = $teacherId;
        $this->subjects = \App\Models\VolunteerSubject::orderBy('subject_name')->get();
        $t = $teacherId ? SubjectTeacher::find($teacherId) : null;
        if ($t) {
            $this->subject_id = $t->subject_id;
            $this->subject_proficiency = $t->subject_proficiency;
            $this->teacher = $t;
        }
    }

    public function save()
    {
        $this->validate();
        $t = SubjectTeacher::findOrFail($this->teacherId);
        $t->update([
            'subject_id' => $this->subject_id,
            'subject_proficiency' => $this->subject_proficiency,
        ]);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('subjectTeacherUpdated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('subjectTeacherUpdated');
        }

        return redirect()->route('profile.volunteer.show', ['user' => $t->user_id]);
    }

    public function render()
    {
        return view('livewire.profile.volunteer.subject-teachers.edit', [
            'subjects' => $this->subjects,
            'teacher' => $this->teacher ?? null,
        ]);
    }
}
