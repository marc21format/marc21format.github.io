<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form for the authenticated user.
     */
    public function edit()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $profile = $user->userProfile ?? null;

        return view('profile.edit', compact('user', 'profile'));
    }

    /**
     * Update the authenticated user's account and profile.
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Only allow updating username, password and (conditionally) role via this form.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['sometimes', 'nullable', 'integer', 'exists:user_roles,role_id'],
        ]);

        // Update name (unique check only when changed)
        if (($data['name'] ?? null) !== $user->name) {
            $request->validate(['name' => 'unique:users,name']);
            $user->name = $data['name'];
        }

        // Update password if provided
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Only allow role changes if the current actor is an Admin or Executive
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (isset($data['role_id']) && ($actor->isAdmin() || $actor->isExecutive())) {
            $user->role_id = $data['role_id'];
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated');
    }
}
