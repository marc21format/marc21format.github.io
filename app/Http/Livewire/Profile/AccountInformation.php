<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AccountInformation extends Component
{
    public $user;

    public function mount($userId = null)
    {
        $authUser = Auth::user();
        if (! $authUser) {
            abort(403, 'Unauthorized');
        }
        // Cast to App\Models\User to ensure custom methods are available
        $authUser = User::find($authUser->id);
        $user = $userId ? User::findOrFail($userId) : $authUser;
        if (
            $authUser->id !== $user->id &&
            !($authUser->isAdmin() || $authUser->isExecutive())
        ) {
            abort(403, 'Unauthorized');
        }
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.profile.account-information', [
            'user' => $this->user,
        ]);
    }
}
