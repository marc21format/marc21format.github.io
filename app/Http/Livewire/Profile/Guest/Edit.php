<?php

namespace App\Http\Livewire\Profile\Guest;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class Edit extends Component
{
    public $userId;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $role_id;
    public $roles = [];

    public function mount($user)
    {
        $actor = Auth::user();
        if (! $actor) {
            abort(403);
        }

        if ($user instanceof User) {
            $target = $user;
        } else {
            $target = User::findOrFail($user);
        }

        if (! $actor->canEditUserProfile($target)) {
            abort(403);
        }

        $guestRoleId = (int) config('roles.guest_id', 5);
        $targetRoleId = (int) ($target->role_id ?? 0);
        $isGuest = $targetRoleId === $guestRoleId || optional($target->role)->role_title === 'Guest';
        if (! $isGuest) {
            abort(403, 'Only guest accounts can use this page.');
        }

        $this->userId = $target->id;
        $this->name = $target->name;
        $this->email = $target->email;
        $this->role_id = $target->role_id;
        // Only admins/execs can change role
        if ($actor->isAdmin() || $actor->isExecutive()) {
            $this->roles = \App\Models\UserRole::orderBy('role_title')->get();
        }
    }

    protected function rules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
        if (!empty($this->roles)) {
            $rules['role_id'] = ['required', 'integer', 'exists:user_roles,role_id'];
        }
        return $rules;
    }

    public function save()
    {
        $data = $this->validate();

        $user = User::findOrFail($this->userId);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Only admins/execs can change role
        if (!empty($this->roles) && isset($data['role_id'])) {
            $payload['role_id'] = $data['role_id'];
        }

        $user->update($payload);

        session()->flash('status', 'Guest account updated.');

        return redirect()->route('profile.guest.show', ['user' => $user->id]);
    }

    public function render()
    {
        return view('livewire.profile.guest.edit', [
            'roles' => $this->roles,
        ])->layout('layouts.app');
    }
}
