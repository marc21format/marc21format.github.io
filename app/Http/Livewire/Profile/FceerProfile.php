<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/** @var User $actor */
class FceerProfile extends Component
{
    public $mode = 'show'; // show|edit
    public $fields = [
        'user_id' => '',
        'volunteer_number' => '',
        'student_number' => '',
        'fceer_batch' => '',
        'student_group' => '',
    ];
    public $rooms = [];
    public $users = [];

    public function mount($userId = null, $role = null)
    {
        $actor = Auth::user();
        /** @var User $actor */
        $this->users = User::all();
        $this->rooms = Room::all();
        // Support being passed either a user id or a User model via $userId
        if ($userId instanceof User) {
            $uid = $userId->id;
        } else {
            $uid = $userId ?? $actor->id;
        }
        $profile = \App\Models\FceerProfile::where('user_id', $uid)->first();
        if ($profile) {
            $this->fields = $profile->toArray();
        } else {
            $this->fields['user_id'] = $uid;
        }
        // role param may be passed as second attribute; we keep mode default 'show'
        $this->mode = 'show';
    }


    public function edit()
    {
        $actor = Auth::user();
        /** @var User $actor */
        if (!($actor && ($actor->isAdmin() || $actor->isExecutive()))) abort(403);
        $this->mode = 'edit';
    }

    public function saveFceerProfile()
    {
        $actor = Auth::user();
        /** @var User $actor */
        if (!($actor && ($actor->isAdmin() || $actor->isExecutive()))) abort(403);
        $data = $this->validate([
            'fields.user_id' => 'required|exists:users,id',
            'fields.volunteer_number' => 'nullable|string|max:45',
            'fields.student_number' => 'nullable|string|max:45',
            'fields.fceer_batch' => 'required|digits:4',
            'fields.student_group' => 'nullable|exists:rooms,room_id',
        ])['fields'];
        $profile = \App\Models\FceerProfile::where('user_id', $this->fields['user_id'])->first();
        if ($profile) {
            $profile->update($data);
        } else {
            \App\Models\FceerProfile::create($data);
        }
        $this->mode = 'show';
        session()->flash('status', 'Profile saved.');
    }

    public function render()
    {
        $actor = Auth::user();
        /** @var User $actor */
        $view = $this->mode === 'edit'
            ? 'livewire.profile.fceer-profile-edit'
            : 'livewire.profile.fceer-profile-show';
        return view($view, [
            'actor' => $actor,
            'fields' => $this->fields,
            'rooms' => $this->rooms,
            'users' => $this->users,
            'mode' => $this->mode,
            'user' => User::find($this->fields['user_id'])
        ]);
    }
}
