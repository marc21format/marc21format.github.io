<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjectRecords;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HighschoolSubjectRecord;

class All extends Component
{
    use WithPagination;

    public $userId;

    protected $listeners = [
        'highschoolSubjectCreated' => '$refresh',
        'highschoolSubjectUpdated' => '$refresh',
        'highschoolSubjectDeleted' => '$refresh',
    ];

    public function mount($userId = null)
    {
        // Resolve Eloquent user for authorization checks
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $user = $userId ? User::findOrFail($userId) : $authUser;

        $canEditOthers = $authUser && ($authUser->isAdmin() || $authUser->isExecutive());
        if (! $authUser || ($authUser->id !== $user->id && ! $canEditOthers)) {
            abort(403, 'Unauthorized');
        }

        $this->userId = $user->id;
    }

    public function render()
    {
        $records = HighschoolSubjectRecord::where('user_id', $this->userId)
            ->with('subject')
            ->orderBy('record_id', 'desc')
            ->paginate(10);

        return view('livewire.profile.student.highschool-subject-records.all', [
            'records' => $records,
            'userId' => $this->userId,
        ]);
    }
}
