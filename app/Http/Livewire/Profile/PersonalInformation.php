<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ProfessionalCredential;

class PersonalInformation extends Component
{
    public $user;
    public $primaryProfessional;

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
            !( (method_exists($authUser, 'isAdmin') && $authUser->isAdmin()) || (method_exists($authUser, 'isExecutive') && $authUser->isExecutive()) )
        ) {
            abort(403, 'Unauthorized');
        }
        $this->user = $user;
        // Load the user's most recent professional credential (if any) to surface prefix/suffix
        $this->primaryProfessional = ProfessionalCredential::with(['prefix','suffix'])
            ->where('user_id', $this->user->id)
            ->orderBy('issued_on', 'desc')
            ->first();
    }

    public function render()
    {
        return view('livewire.profile.personal-information', [
            'user' => $this->user,
            'primaryProfessional' => $this->primaryProfessional,
        ]);
    }
}
