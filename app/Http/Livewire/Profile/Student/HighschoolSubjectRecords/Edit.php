<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjectRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolSubjectRecord;
use App\Models\HighschoolSubject;
use App\Models\User;

class Edit extends Component
{
    public $recordId;
    public $record;
    public $highschoolsubject_id;
    public $grade;
    public $highschoolSubjects = [];

    protected $listeners = [
        'refreshRow' => '$refresh',
    ];

    protected $rules = [
        'highschoolsubject_id' => 'required|integer|exists:highschool_subjects,highschoolsubject_id',
        'grade' => 'nullable|string|max:20',
    ];

    public function mount($recordId)
    {
        $this->recordId = $recordId;
        $this->loadRecord();
        $this->highschoolSubjects = HighschoolSubject::orderBy('subject_name')->get();
    }

    public function loadRecord()
    {
        $this->record = HighschoolSubjectRecord::find($this->recordId);
        if ($this->record) {
            $this->highschoolsubject_id = $this->record->highschoolsubject_id;
            $this->grade = $this->record->grade;
        }
    }

    public function save()
    {
        $this->validate();

        // Authorization: ensure actor can edit
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        if (! $authUser || ($authUser->id !== $this->record->user_id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }

        // Apply changes and persist
        $rec = HighschoolSubjectRecord::find($this->recordId);
        if (! $rec) {
            abort(404, 'Record not found');
        }

        $rec->highschoolsubject_id = $this->highschoolsubject_id;
        $rec->grade = $this->grade;
        $rec->save();

        // refresh local copy
        $this->loadRecord();

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolSubjectUpdated');
        }

        // If this edit was rendered on a standalone page, redirect back to the
        // profile to match the controller behavior. Use the saved record's user id.
        if (method_exists($this, 'redirectRoute')) {
            $target = \App\Models\User::find($rec->user_id);
            $roleId = $target->role_id ?? ($target->role->role_id ?? null);
            if (in_array($roleId, [1,2,3], true)) {
                $this->redirectRoute('profile.volunteer.show', ['user' => $rec->user_id]);
            } else {
                $this->redirectRoute('profile.student.show', ['user' => $rec->user_id]);
            }
        }
    }

    public function render()
    {
        return view('livewire.profile.student.highschool-subject-records.edit', [
            'record' => $this->record,
            'highschoolSubjects' => $this->highschoolSubjects,
        ]);
    }
}
