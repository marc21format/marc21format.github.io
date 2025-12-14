<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjects;

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
        // Resolve auth user as the Eloquent User model
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
        $subjects = HighschoolSubjectRecord::where('user_id', $this->userId)
            ->orderBy('record_id', 'desc')
            ->paginate(10);

        return view('livewire.profile.student.highschool-subject-records.all', [
            'subjects' => $subjects,
            'userId' => $this->userId,
        ]);
    }
}
