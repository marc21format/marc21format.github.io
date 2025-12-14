<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjects;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolSubjectRecord;
use App\Models\User;

class Row extends Component
{
    public $recordId;
    public $record;

    public function mount($recordId)
    {
        $this->recordId = $recordId;
        $this->load();
    }

    public function load()
    {
        $this->record = HighschoolSubjectRecord::find($this->recordId);
    }

    public function delete()
    {
        $rec = HighschoolSubjectRecord::findOrFail($this->recordId);
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $canEditOthers = $authUser && ($authUser->isAdmin() || $authUser->isExecutive());
        if (! $authUser || ($authUser->id !== $rec->user_id && ! $canEditOthers)) {
            abort(403, 'Unauthorized');
        }

        $rec->delete();
       if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolSubjectDeleted');
        }
    }

    public function render()
    {
        return view('livewire.highschool-subjects.row');
    }
}
