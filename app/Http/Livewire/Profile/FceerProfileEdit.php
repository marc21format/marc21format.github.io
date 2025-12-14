<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use App\Models\FceerProfile;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FceerProfileEdit extends Component
{
    public $fields = [
        'user_id' => '',
        'volunteer_number' => '',
        'student_number' => '',
        'fceer_batch' => '',
        'student_group' => '',
    ];
    public $rooms = [];
    public $users = [];

    public function mount($user = null)
    {
        $actor = Auth::user();
        $this->users = User::orderBy('name')->get();
        $this->rooms = Room::orderBy('group')->get();
        $userId = $user ?? $actor->id;
        $profile = FceerProfile::where('user_id', $userId)->first();
        if ($profile) {
            $this->fields = $profile->toArray();
        } else {
            $this->fields['user_id'] = $userId;
        }
    }

    public function saveFceerProfile()
    {
        $actor = Auth::user();
        /** @var User|null $actor */
        if (!($actor instanceof \App\Models\User) || !($actor->isAdmin() || $actor->isExecutive())) abort(403);
        $data = $this->validate([
            'fields.user_id' => 'required|exists:users,id',
            'fields.volunteer_number' => 'nullable|string|max:45',
            'fields.student_number' => 'nullable|string|max:45',
            'fields.fceer_batch' => 'required|digits:4',
            'fields.student_group' => 'nullable|exists:rooms,room_id',
        ])['fields'];
        // Ensure student_group is null if empty string
        if (empty($data['student_group'])) {
            $data['student_group'] = null;
        }
        $profile = FceerProfile::where('user_id', $this->fields['user_id'])->first();
        if ($profile) {
            $profile->update($data);
        } else {
            FceerProfile::create($data);
        }
        session()->flash('status', 'Profile saved.');
        return redirect()->route('fceer-profile.edit', ['user' => $this->fields['user_id']]);
    }

    public function render()
    {
        $actor = Auth::user();
        return view('livewire.profile.fceer-profile-edit', [
            'actor' => $actor,
            'fields' => $this->fields,
            'rooms' => $this->rooms,
            'users' => $this->users,
            'user' => User::find($this->fields['user_id'])
        ]);
    }
}
