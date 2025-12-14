<?php
namespace App\Http\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PersonalInformationEdit extends Component
{
    protected $listeners = [
        'addressSaved' => 'handleAddressSaved',
    ];

    public function handleAddressSaved($addressId)
    {
        // After address saved, redirect to profile show so the user sees the
        // updated profile page (consistent with saveProfile behavior).
        // Choose target route based on the user's role id (1,2,3 -> Volunteer)
        $roleId = $this->user->role_id ?? ($this->user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->userId]);
        }

        return redirect()->route('profile.student.show', ['user' => $this->userId]);
    }
    public $userId;
    public $f_name;
    public $m_name;
    public $s_name;
    public $lived_name;
    public $generational_suffix;
    public $phone_number;
    public $birthday;
    public $sex;

    protected $rules = [
        'f_name' => ['required','string','max:255'],
        'm_name' => ['nullable','string','max:255'],
        's_name' => ['required','string','max:255'],
        'lived_name' => ['nullable','string','max:255'],
        'generational_suffix' => ['nullable','string','max:50'],
        'phone_number' => ['nullable','string','max:50'],
        'birthday' => ['nullable','date'],
        'sex' => ['nullable','in:male,female,other']
    ];

    public $user;

    public function mount($userId = null)
    {
        $actor = Auth::user();
        if (! $actor) abort(403);

        // Help static analyzers know $actor is an App\Models\User so calls
        // like isAdmin()/isExecutive() are not flagged as undefined.
        /** @var User $actor */

        $this->userId = $userId ?: $actor->id;
        $this->user = User::findOrFail($this->userId);

        // Only allow editing others' profiles for admin/executive
        if ($actor->id !== $this->user->id && !(method_exists($actor,'isAdmin') && $actor->isAdmin()) && !(method_exists($actor,'isExecutive') && $actor->isExecutive())) {
            abort(403);
        }

        $p = $this->user->userProfile;
        if ($p) {
            $this->f_name = $p->f_name;
            $this->m_name = $p->m_name;
            $this->s_name = $p->s_name;
            $this->lived_name = $p->lived_name;
            $this->generational_suffix = $p->generational_suffix;
            $this->phone_number = $p->phone_number;
            // birthday in DB may be a string or a Carbon instance — normalize to Y-m-d string
            if (! empty($p->birthday)) {
                try {
                    $this->birthday = \Carbon\Carbon::parse($p->birthday)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $this->birthday = null;
                }
            } else {
                $this->birthday = null;
            }
            $this->sex = $p->sex;
        }
    }

    public function saveProfile()
    {
        $this->validate();

        $p = $this->user->userProfile;
        $data = [
            'f_name' => $this->f_name,
            'm_name' => $this->m_name,
            's_name' => $this->s_name,
            'lived_name' => $this->lived_name,
            'generational_suffix' => $this->generational_suffix,
            'phone_number' => $this->phone_number,
            'birthday' => $this->birthday ?: null,
            'sex' => $this->sex,
        ];

        if ($p) {
            $p->fill($data);
            $p->save();
        } else {
            $data['user_id'] = $this->user->id;
            \App\Models\UserProfile::create($data);
        }

        session()->flash('success', 'Personal information updated.');

        // Dispatch an internal Livewire event so other components can react; fall back to emit
        if (method_exists($this, 'dispatch')) {
            try { $this->dispatch('profileUpdated', $this->user->id); } catch (\Throwable $e) { /* ignore */ }
        } elseif (method_exists($this, 'emit')) {
            $this->emit('profileUpdated', $this->user->id);
        }

        // After saving, choose route using role ids if available (1,2,3 => volunteer)
        $roleId = $this->user->role_id ?? ($this->user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->user->id]);
        }

        return redirect()->route('profile.student.show', ['user' => $this->user->id]);
    }

    public function render()
    {
        return view('livewire.profile.personal-information-edit', ['user' => $this->user]);
    }
}
