<?php
namespace App\Http\Livewire\Room;

use Livewire\Component;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Edit extends Component
{
    public $roomId;
    public $room;
    public $fields = [];
    public $staffUsers;
    public $studentUsers;

    protected $staffRoles = ['Admin','Administrator','Executive','Exec','Instructor'];

    public function mount($roomId = null)
    {
        $user = Auth::user();
        /** @var User|null $user */
        if (! ($user instanceof User) || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
        }

        $this->roomId = $roomId;
        $this->room = Room::where('room_id', $roomId)->first();
        if ($this->room) {
            $this->fields = [
                'group' => $this->room->group,
                'adviser_id' => $this->room->adviser_id,
                'co_adviser_id' => $this->room->co_adviser_id,
                'president_id' => $this->room->president_id,
                'secretary_id' => $this->room->secretary_id,
            ];
        }
        $this->staffUsers = User::whereHas('role', function($q) {
            $q->whereIn('role_title', $this->staffRoles);
        })->orderBy('name')->get();

        $this->studentUsers = User::whereHas('role', function($q) {
            $q->where('role_title', 'Student');
        })->orderBy('name')->get();
    }

    public function save()
    {
        $this->validate([
            'fields.group' => 'required|string|max:255',
            'fields.adviser_id' => 'nullable|exists:users,id',
            'fields.co_adviser_id' => 'nullable|exists:users,id',
            'fields.president_id' => 'nullable|exists:users,id',
            'fields.secretary_id' => 'nullable|exists:users,id',
        ]);

        // Role-specific checks
        if ($this->fields['president_id']) {
            $p = User::find($this->fields['president_id']);
            if (! $p || optional($p->role)->role_title !== 'Student') {
                $this->addError('fields.president_id', 'President must be a student.');
                return;
            }
        }
        if ($this->fields['secretary_id']) {
            $s = User::find($this->fields['secretary_id']);
            if (! $s || optional($s->role)->role_title !== 'Student') {
                $this->addError('fields.secretary_id', 'Secretary must be a student.');
                return;
            }
        }
        foreach (['adviser_id','co_adviser_id'] as $staffKey) {
            if ($this->fields[$staffKey]) {
                $u = User::find($this->fields[$staffKey]);
                if (! $u || ! in_array(optional($u->role)->role_title, $this->staffRoles, true)) {
                    $this->addError("fields.{$staffKey}", 'Selected user must be Admin/Executive/Instructor.');
                    return;
                }
            }
        }

        if (! $this->room) {
            session()->flash('error', 'Room not found');
            return redirect()->route('rooms.index');
        }

        $this->room->update([
            'group' => $this->fields['group'],
            'adviser_id' => $this->fields['adviser_id'] ?: null,
            'co_adviser_id' => $this->fields['co_adviser_id'] ?: null,
            'president_id' => $this->fields['president_id'] ?: null,
            'secretary_id' => $this->fields['secretary_id'] ?: null,
        ]);

        return redirect()->route('rooms.index');
    }

    public function render()
    {
        return view('livewire.room.edit', ['room' => $this->room]);
    }
}

