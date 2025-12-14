<?php
namespace App\Http\Livewire\Profile\Volunteer\ProfessionalCredentials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProfessionalCredential;
use Illuminate\Support\Facades\Auth;

class All extends Component
{
    use WithPagination;

    public $userId;

    protected $listeners = [
        'professionalCredentialCreated' => '$refresh',
        'professionalCredentialUpdated' => '$refresh',
        'professionalCredentialDeleted' => '$refresh',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function render()
    {
        $query = ProfessionalCredential::with(['fieldOfWork','prefix','suffix'])->where('user_id', $this->userId)->orderBy('issued_on','desc');
        $creds = $query->paginate(10);
        return view('livewire.profile.volunteer.professional-credentials.all', [
            'creds' => $creds,
            'userId' => $this->userId,
        ]);
    }

    public function delete($credentialId)
    {
        $c = ProfessionalCredential::findOrFail($credentialId);
        $me = Auth::user();
        if (! $me) abort(403);
        // allow if admin/executive (staff) or owner
        $roleTitle = $me->role ? $me->role->role_title : null;
        if (! (in_array($roleTitle, ['Admin','Administrator','Executive','Exec'], true) || $me->id === $c->user_id)) {
            abort(403);
        }
        $c->delete();
        $this->dispatchBrowserEvent('toast', ['message' => 'Professional credential removed']);
        $this->emit('professionalCredentialDeleted');
    }
}
