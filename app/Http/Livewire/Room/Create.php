<?php
namespace App\Http\Livewire\Room;

use Livewire\Component;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Create extends Component
{
    public $fields = [
        'group' => '',
        'adviser_id' => null,
        'co_adviser_id' => null,
        'president_id' => null,
        'secretary_id' => null,
    ];

    public $staffUsers; // advisers/co-advisers (admin/exec/instructor)
    public $studentUsers; // president/secretary (students only)

    protected $staffRoles = ['Admin','Administrator','Executive','Exec','Instructor'];

    public function mount()
    {
        $user = Auth::user();
        /** @var User|null $user */
        if (! ($user instanceof User) || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
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

        // Additional role-specific validation
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

        $data = [
            'group' => $this->fields['group'],
            'adviser_id' => $this->fields['adviser_id'] ?: null,
            'co_adviser_id' => $this->fields['co_adviser_id'] ?: null,
            'president_id' => $this->fields['president_id'] ?: null,
            'secretary_id' => $this->fields['secretary_id'] ?: null,
        ];

        Room::create($data);
        session()->flash('success', 'Room created.');
        return redirect()->route('rooms.index');
    }

    public function render()
    {
        return view('livewire.room.create');
    }
}
