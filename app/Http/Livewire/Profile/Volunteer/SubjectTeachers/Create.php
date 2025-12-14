<?php
namespace App\Http\Livewire\Profile\Volunteer\SubjectTeachers;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SubjectTeacher;

class Create extends Component
{
    public $userId;
    public $subject_id;
    public $subject_proficiency = 'beginner';
    public $subjects = [];

    protected $rules = [
        'subject_id' => 'required|exists:volunteer_subjects,subject_id',
        'subject_proficiency' => 'required|in:beginner,competent,proficient',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?? Auth::id();
        $this->subjects = \App\Models\VolunteerSubject::orderBy('subject_name')->get();
    }

    public function store()
    {
        $this->validate();

        SubjectTeacher::create([
            'user_id' => $this->userId,
            'subject_id' => $this->subject_id,
            'subject_proficiency' => $this->subject_proficiency,
        ]);

        $this->reset(['subject_id','subject_proficiency']);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('subjectTeacherCreated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('subjectTeacherCreated');
        }
    }

    public function render()
    {
        return view('livewire.profile.volunteer.subject-teachers.create', [
            'subjects' => $this->subjects,
            'userId' => $this->userId,
        ]);
    }
}
