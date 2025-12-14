<?php
namespace App\Http\Livewire\Profile\Volunteer\EducationalRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalRecord;
use App\Models\User;

class Row extends Component
{
    public $record;

    protected $listeners = ['refreshRow' => '$refresh'];

    public function mount(EducationalRecord $record)
    {
        $this->record = $record;
    }

    public function delete()
    {
        $authUser = Auth::check() ? User::find(Auth::id()) : null;

        // allow owner or staff
        if (! $authUser) {
            abort(403);
        }

        if ($authUser->id !== $this->record->user_id && ! in_array($authUser->role_id, [1,2,3])) {
            abort(403);
        }

        $this->record->delete();
        $this->emitUp('educationalRecordDeleted');
    $this->dispatch('educationalRecordDeleted', ['id' => $this->record->record_id]);
    }

    public function render()
    {
        return view('livewire.profile.volunteer.educational-records.row', ['record' => $this->record]);
    }
}
