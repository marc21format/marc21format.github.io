<?php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Room as RoomModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/** @var User $actor */
class Room extends Component
{
    public $room;
    public $mode = 'view'; // view|edit|create
    public $fields = [
        'group' => '',
        'adviser_id' => '',
        'president_id' => '',
        'secretary_id' => '',
    ];
    public $users = [];

    public function mount($roomId = null)
    {
        $actor = Auth::user();
        /** @var User $actor */
        $this->users = User::all();
        if ($roomId) {
            $this->room = RoomModel::find($roomId);
            if ($this->room) {
                $this->fields = $this->room->toArray();
                $this->mode = 'view';
            } else {
                $this->mode = ($actor && ($actor->isAdmin() || $actor->isExecutive())) ? 'create' : 'view';
            }
        }
    }

    public function edit()
    {
        $actor = Auth::user();
        /** @var User $actor */
        if (!($actor && ($actor->isAdmin() || $actor->isExecutive()))) abort(403);
        $this->mode = 'edit';
    }

    public function save()
    {
        $actor = Auth::user();
        /** @var User $actor */
        if (!($actor && ($actor->isAdmin() || $actor->isExecutive()))) abort(403);
        $data = $this->validate([
            'fields.group' => 'required|string|max:255',
            'fields.adviser_id' => 'nullable|exists:users,id',
            'fields.president_id' => 'nullable|exists:users,id',
            'fields.secretary_id' => 'nullable|exists:users,id',
        ])['fields'];
        if ($this->room) {
            $this->room->update($data);
        } else {
            $this->room = RoomModel::create($data);
        }
        $this->mode = 'view';
        session()->flash('success', 'Room saved.');
    }

    public function render()
    {
        $actor = Auth::user();
        /** @var User $actor */
        return view('livewire.room', [
            'actor' => $actor,
            'room' => $this->room,
            'fields' => $this->fields,
            'users' => $this->users,
        ]);
    }
}
