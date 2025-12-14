<?php
namespace App\Http\Livewire\Profile\Volunteer\EducationalRecords;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalRecord;
use App\Models\User;

class All extends Component
{
    use WithPagination;

    public $userId;

    protected $listeners = [
        'educationalRecordCreated' => '$refresh',
        'educationalRecordDeleted' => '$refresh',
        'educationalRecordUpdated' => '$refresh',
    ];

    public function mount($userId = null)
    {
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $user = $userId ? User::findOrFail($userId) : $authUser;
        if (! $authUser) {
            abort(403, 'Unauthorized');
        }

        // allow owner or staff (role 1,2,3)
        if ($authUser->id !== ($user->id ?? null) && ! in_array($authUser->role_id, [1,2,3])) {
            abort(403, 'Unauthorized');
        }
        $this->userId = $user->id;
    }

    public function render()
    {
        $records = EducationalRecord::where('user_id', $this->userId)
            ->orderBy('year_graduated', 'desc')
            ->paginate(10);

        return view('livewire.profile.volunteer.educational-records.all', [
            'records' => $records,
            'userId' => $this->userId,
        ]);
    }
}
