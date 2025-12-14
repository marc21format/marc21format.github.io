<?php
namespace App\Http\Livewire\Profile\HighschoolRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolRecord;
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
        $this->record = HighschoolRecord::find($this->recordId);
    }

    public function delete()
    {
        $rec = HighschoolRecord::findOrFail($this->recordId);

        // Resolve to Eloquent user so helper methods exist
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        if (! $authUser || ($authUser->id !== $rec->user_id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }

        $rec->delete();
       if (method_exists($this, 'dispatch')) {
            $this->dispatch('highschoolRecordDeleted');
        }
    }

    public function render()
    {
        return view('livewire.profile.highschool-records.row');
    }
}
