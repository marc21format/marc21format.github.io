<?php

namespace App\Http\Livewire\Roster;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Users extends Component
{
    public $users;

    public function mount()
    {
        $actor = Auth::user();
        if (! $actor || !($actor->isAdmin() || $actor->isExecutive())) {
            abort(403);
        }
        $this->users = User::with('role')->orderBy('id')->get();
    }

    public function render()
    {
        return view('livewire.roster.users.all', [
            'users' => $this->users,
        ]);
    }
}
