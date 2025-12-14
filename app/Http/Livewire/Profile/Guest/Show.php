<?php

namespace App\Http\Livewire\Profile\Guest;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Show extends Component
{
    public $user;

    public function mount($user = null)
    {
        $actor = Auth::user();
        if (! $actor) {
            abort(403);
        }

        if ($user instanceof User) {
            $target = $user;
        } elseif ($user) {
            $target = User::findOrFail($user);
        } else {
            $target = $actor;
        }

        if (! $actor->canEditUserProfile($target)) {
            abort(403);
        }

        $guestRoleId = (int) config('roles.guest_id', 5);
        $targetRoleId = (int) ($target->role_id ?? 0);
        $isGuest = $targetRoleId === $guestRoleId || optional($target->role)->role_title === 'Guest';
        if (! $isGuest) {
            abort(403, 'This profile is not for a guest account.');
        }

        $this->user = $target;
    }

    public function render()
    {
        return view('livewire.profile.guest.show', [
            'user' => $this->user,
        ])->layout('layouts.app');
    }
}
