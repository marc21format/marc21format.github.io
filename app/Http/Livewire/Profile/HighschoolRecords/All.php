<?php
namespace App\Http\Livewire\Profile\HighschoolRecords;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HighschoolRecord;

class All extends Component
{
    use WithPagination;

    public $userId;

    protected $listeners = [
        'highschoolRecordCreated' => '$refresh',
        'highschoolRecordDeleted' => '$refresh',
        'highschoolRecordUpdated' => '$refresh',
    ];

    public function mount($userId = null)
    {
        // Ensure we have the Eloquent User instance for authorization helpers
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $user = $userId ? User::findOrFail($userId) : $authUser;
        if (! $authUser || ($authUser->id !== $user->id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }
        $this->userId = $user->id;
    }

    public function render()
    {
        $records = HighschoolRecord::where('user_id', $this->userId)
            ->orderBy('year_end', 'desc')
            ->paginate(10);

        return view('livewire.profile.highschool-records.all', [
            'records' => $records,
            'userId' => $this->userId,
        ]);
    }
}
