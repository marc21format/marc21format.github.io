<?php
namespace App\Http\Livewire\Profile\Volunteer\SubjectTeachers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SubjectTeacher;
use App\Models\User;

class All extends Component
{
    use WithPagination;

    public $userId;

    protected $listeners = [
        'subjectTeacherCreated' => '$refresh',
        'subjectTeacherUpdated' => '$refresh',
        'subjectTeacherDeleted' => '$refresh',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function render()
    {
        $query = SubjectTeacher::with('subject')->where('user_id', $this->userId)->orderBy('teacher_id', 'desc');
        $teachers = $query->paginate(10);

        return view('livewire.profile.volunteer.subject-teachers.all', [
            'teachers' => $teachers,
        ]);
    }

    public function delete($teacherId)
    {
        $t = SubjectTeacher::findOrFail($teacherId);
        $t->delete();
        $this->dispatchBrowserEvent('toast', ['message' => 'Subject mapping removed']);
        $this->emit('subjectTeacherDeleted');
    }
}
