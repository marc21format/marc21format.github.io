<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\UserRole;

class AccountInformationEdit extends Component
{
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role_id;
    public $roles = [];

    public function mount($userId = null)
    {
        $actor = Auth::user();
        if (! $actor) {
            abort(403);
        }
        // Help static analyzers know $actor is an App\Models\User so methods
        // like canEditUserProfile() are recognized.
        /** @var User $actor */

        $this->roles = UserRole::orderBy('role_title')->get();

        $user = $userId ? User::findOrFail($userId) : $actor;

        // Authorization: owner or staff who can edit profiles
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;
    }

    protected function rules()
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($this->userId)],
            'password' => ['nullable','string','min:8','confirmed'],
            'role_id' => ['nullable','integer','exists:user_roles,role_id'],
        ];
    }

    public function saveAccount()
    {
        $data = $this->validate();

        $user = User::findOrFail($this->userId);

        // Hash password only when provided
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Prevent accidentally nulling role if not present
        if (empty($data['role_id'])) {
            unset($data['role_id']);
        }

        $user->update($data);

        // Notify parent listeners or fallback to emit
        if (method_exists($this, 'dispatch')) {
            try { $this->dispatch('accountUpdated', ['userId' => $user->id]); } catch(\Throwable $e) {}
        } else {
            $this->emit('accountUpdated', $user->id);
        }

        session()->flash('status', 'Account information updated');

        // Redirect to the appropriate profile view depending on the saved user's role.
        // Some installs use numeric role ids for role groups; treat role ids 1,2,3 as Volunteer.
        $roleId = $user->role_id ?? ($user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id]);
        }

        return redirect()->route('profile.student.show', ['user' => $user->id]);
    }

    public function render()
    {
        return view('livewire.profile.account-information-edit', [
            'roles' => $this->roles,
        ]);
    }
}
