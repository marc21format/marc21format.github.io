<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjectRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolSubjectRecord;
use App\Models\User;

class Row extends Component
{
    public $recordId;
    public $record;

    protected $listeners = [
        'refreshRow' => '$refresh',
    ];

    public function mount($recordId)
    {
        $this->recordId = $recordId;
        $this->loadRecord();
    }

    public function loadRecord()
    {
        $this->record = HighschoolSubjectRecord::find($this->recordId);
    }

    public function delete()
    {
        $rec = HighschoolSubjectRecord::findOrFail($this->recordId);

        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        if (! $authUser || ($authUser->id !== $rec->user_id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }

        $rec->delete();
       if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolSubjectDeleted');
        }
    }

    public function render()
    {
        return view('livewire.profile.student.highschool-subject-records.row');
    }
}
