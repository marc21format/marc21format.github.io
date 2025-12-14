<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjectRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolSubjectRecord;
use App\Models\HighschoolSubject;
use App\Models\User;

class Create extends Component
{
    public $userId;
    public $highschoolsubject_id;
    public $grade;
    public $highschoolSubjects = [];

    protected $rules = [
        'highschoolsubject_id' => 'required|integer|exists:highschool_subjects,highschoolsubject_id',
        'grade' => 'nullable|string|max:20',
    ];

    public function mount($userId = null)
    {
        $authUser = Auth::user(); // ← FIXED
        $user = $userId ? User::findOrFail($userId) : $authUser;

        if (! $authUser || ($authUser->id !== $user->id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }

        $this->userId = $user->id;
        $this->highschoolSubjects = HighschoolSubject::orderBy('subject_name')->get();
    }


    protected $listeners = [
        'highschoolSubjectOptionCreated' => 'reloadOptions',
    ];

    public function reloadOptions($id = null, $name = null)
    {
        $this->highschoolSubjects = HighschoolSubject::orderBy('subject_name')->get();
        if ($id && empty($this->highschoolsubject_id)) {
            $this->highschoolsubject_id = $id;
        }
    }

    public function store()
    {
        $this->validate();

        HighschoolSubjectRecord::create([
            'user_id' => $this->userId,
            'highschoolsubject_id' => $this->highschoolsubject_id,
            'grade' => $this->grade,
        ]);

        $this->reset(['highschoolsubject_id','grade']);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolSubjectCreated');
        }

        // After creating via the standalone create page, redirect back to the
        // user's profile so the UI matches the non-Livewire controller flow.
        if (method_exists($this, 'redirectRoute')) {
            $target = \App\Models\User::find($this->userId);
            $roleId = $target->role_id ?? ($target->role->role_id ?? null);
            if (in_array($roleId, [1,2,3], true)) {
                $this->redirectRoute('profile.volunteer.show', ['user' => $this->userId]);
            } else {
                $this->redirectRoute('profile.student.show', ['user' => $this->userId]);
            }
        }
    }

    public function render()
    {
        return view('livewire.profile.student.highschool-subject-records.create', [
            'highschoolSubjects' => $this->highschoolSubjects,
            'userId' => $this->userId,
        ]);
    }
}
