<?php
namespace App\Http\Livewire\Profile\Student;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Show extends Component
{
    public $user;

    public function mount($user = null)
    {
        // Resolve auth user as Eloquent model
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        // $user may be an id or a User model (route model binding or Livewire routing)
        if ($user instanceof User) {
            $target = $user;
        } elseif ($user) {
            $target = User::findOrFail($user);
        } else {
            $target = $authUser;
        }
        // Only allow viewing own profile unless admin/exec
        if (! $authUser || ($authUser->id !== $target->id && ! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized to view this profile');
        }
        $this->user = $target;
    }

    public function render()
    {
        return view('livewire.profile.student.show', [
            'user' => $this->user,
        ]);
    }
}