<?php

namespace App\Http\Controllers\HighschoolSubjectRecord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HighschoolSubject;
use App\Models\HighschoolSubjectRecord;
use App\Models\User;

class HighschoolSubjectRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createHighschoolSubjectRecord(User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        $highschoolSubjects = HighschoolSubject::orderBy('subject_name')->get();

        return view('profile.highschool_subject_records.create', compact('user', 'highschoolSubjects'));
    }

    public function storeHighschoolSubjectRecord(Request $request, User $user)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }
        // Accept either "highschoolsubject_id" or "highschool_subject_id" from the form
        $input = $request->all();
        if ($request->has('highschool_subject_id') && ! $request->has('highschoolsubject_id')) {
            $input['highschoolsubject_id'] = $request->input('highschool_subject_id');
        }

        $data = validator($input, [
            'highschoolsubject_id' => ['required','integer','exists:highschool_subjects,highschoolsubject_id'],
            'grade' => ['nullable','string','max:10'],
        ])->validate();

        $data['user_id'] = $user->id;

        HighschoolSubjectRecord::create($data);

        $roleId = $user->role_id ?? ($user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status', 'Highschool subject added');
        }
        return redirect()->route('profile.student.show', ['user' => $user->id])->with('status', 'Highschool subject added');
    }

    public function editHighschoolSubjectRecord(User $user, HighschoolSubjectRecord $record)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }
        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $highschoolSubjects = HighschoolSubject::orderBy('subject_name')->get();

        return view('profile.highschool_subject_records.edit', compact('user', 'record', 'highschoolSubjects'));
    }

    public function updateHighschoolSubjectRecord(Request $request, User $user, HighschoolSubjectRecord $record)
    {
        $actor = Auth::user();
        /** @var \App\Models\User $actor */
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $data = $request->validate([
            'highschoolsubject_id' => ['required','integer','exists:highschool_subjects,highschoolsubject_id'],
            'grade' => ['nullable','string','max:10'],
        ]);

        $record->fill($data);
        $record->save();

        $roleId = $user->role_id ?? ($user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status', 'Highschool subject updated');
        }
        return redirect()->route('profile.student.show', ['user' => $user->id])->with('status', 'Highschool subject updated');
    }

    public function destroyHighschoolSubjectRecord(User $user, HighschoolSubjectRecord $record)
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        if (! $actor->canEditUserProfile($user)) {
            abort(403);
        }

        if ($record->user_id !== $user->id) {
            abort(404);
        }

        $record->delete();

        $roleId = $user->role_id ?? ($user->role->role_id ?? null);
        if (in_array($roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id])->with('status', 'Highschool subject deleted');
        }
        return redirect()->route('profile.student.show', ['user' => $user->id])->with('status', 'Highschool subject deleted');
    }

}
