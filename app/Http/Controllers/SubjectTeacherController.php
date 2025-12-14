<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VolunteerSubject;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SubjectTeacherController extends Controller
{
    // Show the form to add a subject-teacher mapping for a user
    public function create(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive() || $currentUser->hasRole('Instructor'))) {
            abort(403);
        }

        $subjects = VolunteerSubject::orderBy('subject_name')->get();
        return view('subject_teachers.create', compact('user', 'subjects'));
    }

    public function store(Request $request, User $user)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive() || $currentUser->hasRole('Instructor'))) {
            abort(403);
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:volunteer_subjects,subject_id',
            'subject_proficiency' => 'required|in:beginner,competent,proficient',
        ]);

        if ($currentUser->hasRole('Instructor') && Auth::id() !== $user->id) {
            abort(403);
        }

        SubjectTeacher::create([
            'user_id' => $user->id,
            'subject_id' => $data['subject_id'],
            'subject_proficiency' => $data['subject_proficiency'],
        ]);

        return redirect()->route('users.profile.show', $user->id)->with('success', 'Subject added.');
    }

    public function edit(Request $request, $teacher)
    {
        /** @var SubjectTeacher $st */
        $st = SubjectTeacher::findOrFail($teacher);
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! (Auth::id() === $st->user_id || $currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $subjects = VolunteerSubject::orderBy('subject_name')->get();
        return view('subject_teachers.edit', ['teacher' => $st, 'subjects' => $subjects]);
    }

    public function update(Request $request, $teacher)
    {
        /** @var SubjectTeacher $st */
        $st = SubjectTeacher::findOrFail($teacher);
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! (Auth::id() === $st->user_id || $currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $data = $request->validate([
            'subject_id' => 'required|exists:volunteer_subjects,subject_id',
            'subject_proficiency' => 'required|in:beginner,competent,proficient',
        ]);

        $st->update($data);

        return redirect()->route('users.profile.show', $st->user_id)->with('success', 'Subject mapping updated.');
    }

    public function destroy(Request $request, $teacher)
    {
        /** @var SubjectTeacher $st */
        $st = SubjectTeacher::findOrFail($teacher);
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! (Auth::id() === $st->user_id || $currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $userId = $st->user_id;
        $st->delete();

        return redirect()->route('users.profile.show', $userId)->with('success', 'Subject removed.');
    }
}
