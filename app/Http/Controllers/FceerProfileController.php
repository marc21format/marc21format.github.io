<?php
namespace App\Http\Controllers;

use App\Models\FceerProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FceerProfileController extends Controller
{
    // Only admin/exec can create or edit
    public function create()
    {
    $user = Auth::user();
    /** @var \App\Models\User $user */
        if (!($user && ($user->isAdmin() || $user->isExecutive()))) {
            abort(403);
        }
        return view('fceer_profiles.create');
    }

    public function store(Request $request)
    {
    $user = Auth::user();
    /** @var \App\Models\User $user */
        if (!($user && ($user->isAdmin() || $user->isExecutive()))) {
            abort(403);
        }
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'volunteer_number' => 'nullable|string|max:45',
            'student_number' => 'nullable|string|max:45',
            'fceer_batch' => 'required|digits:4',
            'student_group' => 'nullable|exists:rooms,room_id',
        ]);
        FceerProfile::create($data);
        return redirect()->route('fceer_profiles.index')->with('success', 'Profile created.');
    }

    public function edit(FceerProfile $fceerProfile)
    {
    $user = Auth::user();
    /** @var \App\Models\User $user */
        if (!($user && ($user->isAdmin() || $user->isExecutive()))) {
            abort(403);
        }
        return view('fceer_profiles.edit', compact('fceerProfile'));
    }

    public function update(Request $request, FceerProfile $fceerProfile)
    {
    $user = Auth::user();
    /** @var \App\Models\User $user */
        if (!($user && ($user->isAdmin() || $user->isExecutive()))) {
            abort(403);
        }
        $data = $request->validate([
            'volunteer_number' => 'nullable|string|max:45',
            'student_number' => 'nullable|string|max:45',
            'fceer_batch' => 'required|digits:4',
            'student_group' => 'nullable|exists:rooms,room_id',
        ]);
        $fceerProfile->update($data);
        return redirect()->route('fceer_profiles.index')->with('success', 'Profile updated.');
    }

    // View for all roles
    public function show(FceerProfile $fceerProfile)
    {
        return view('fceer_profiles.show', compact('fceerProfile'));
    }
}
