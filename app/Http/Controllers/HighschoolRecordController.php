<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class HighschoolRecordController extends Controller
{
    /**
     * Store a highschool record for a given user.
     */
    public function storeHighschoolRecord(Request $request, User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        // Allow adding a record for yourself, or allow admins/executives to add for others.
        if ($actor->id !== $user->id && ! ($actor->isAdmin() || $actor->isExecutive())) {
            abort(403);
        }

        $data = $request->validate([
            'highschool_id' => ['nullable','integer','exists:highschools,highschool_id'],
            'year_start' => ['nullable','digits:4'],
            'level' => ['nullable','in:junior,senior'],
        ]);

        $data['user_id'] = $user->id;

        $rec = \App\Models\HighschoolRecord::create($data);

    // Redirect to role-specific profile page
    $roleId = $user->role_id ?? ($user->role->role_id ?? null);
    if (in_array($roleId, [1,2,3], true)) {
        return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status', 'Highschool record added');
    }
    return redirect()->route('profile.student.show', ['user' => $user->id])->with('status', 'Highschool record added');
    }

    /**
     * Show edit form for a highschool record.
     */
    public function editHighschoolRecord(User $user, \App\Models\HighschoolRecord $record)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->isAdmin() && ! $actor->isExecutive() && $actor->id !== $user->id) {
            abort(403);
        }

        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $highschools = \App\Models\Highschool::orderBy('highschool_name')->get();

        return view('profile.highschool_records.edit', compact('user','record','highschools'));
    }

    /**
     * Update the highschool record.
     */
    public function updateHighschoolRecord(Request $request, User $user, \App\Models\HighschoolRecord $record)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->isAdmin() && ! $actor->isExecutive() && $actor->id !== $user->id) {
            abort(403);
        }

        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $data = $request->validate([
            'highschool_id' => ['nullable','integer','exists:highschools,highschool_id'],
                'year_start' => ['nullable','digits:4'],
                'level' => ['nullable','in:junior,senior'],
                'year_graduated' => ['nullable','digits:4'],
        ]);

        $record->fill($data);
        $record->save();

    $roleId = $user->role_id ?? ($user->role->role_id ?? null);
    if (in_array($roleId, [1,2,3], true)) {
        return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status','Highschool record updated');
    }
    return redirect()->route('profile.student.show', ['user' => $user->id])->with('status','Highschool record updated');
    }

    /**
     * Destroy a highschool record.
     */
    public function destroyHighschoolRecord(User $user, \App\Models\HighschoolRecord $record)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->isAdmin() && ! $actor->isExecutive() && $actor->id !== $user->id) {
            abort(403);
        }

        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $record->delete();

    $roleId = $user->role_id ?? ($user->role->role_id ?? null);
    if (in_array($roleId, [1,2,3], true)) {
        return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status','Highschool record deleted');
    }
    return redirect()->route('profile.student.show', ['user' => $user->id])->with('status','Highschool record deleted');
    }

    /**
     * Show the create form for a highschool record for the given user.
     * Only Admin or Executive can access this.
     */
    public function createHighschoolRecord(User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        // Allow creating a record for yourself, or admins/executives for others
        if ($actor->id !== $user->id && ! ($actor->isAdmin() || $actor->isExecutive())) {
            abort(403);
        }

        $highschools = \App\Models\Highschool::orderBy('highschool_name')->get();

        return view('profile.highschool_records.create', compact('user', 'highschools'));
    }
}