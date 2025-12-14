<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVolunteerSubjectRequest;
use App\Http\Requests\StoreSubjectTeacherRequest;
use App\Models\VolunteerSubject;
use App\Models\SubjectTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class VolunteerSubjectController extends Controller
{
    // Web UI methods (admin/executive only)
    public function index()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // Only admin/executive may manage master subjects
        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

    $subjects = VolunteerSubject::orderBy('subject_name')->paginate(20);
    return view('volunteer_subjects.index', compact('subjects'));
    }

    public function create()
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        return view('volunteer_subjects.create');
    }

    public function store(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'nullable|string|max:50',
        ]);

        VolunteerSubject::create($data);

        return redirect()->route('volunteer_subjects.index')->with('success', 'Subject created.');
    }

    public function edit(VolunteerSubject $subject)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        return view('volunteer_subjects.edit', compact('subject'));
    }

    public function update(Request $request, VolunteerSubject $subject)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $data = $request->validate([
            'subject_name' => 'required|string|max:191',
            'subject_code' => 'nullable|string|max:50',
        ]);

        $subject->update($data);

        return redirect()->route('volunteer_subjects.index')->with('success', 'Subject updated.');
    }

    public function destroy(VolunteerSubject $subject)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (! Auth::check() || ! ($currentUser->isAdmin() || $currentUser->isExecutive())) {
            abort(403);
        }

        $subject->delete();

        return redirect()->route('volunteer_subjects.index')->with('success', 'Subject deleted.');
    }

    // JSON/API endpoints (authorized via policies)
    public function indexSubjects()
    {
        $this->authorize('viewAny', VolunteerSubject::class);
        return response()->json(VolunteerSubject::with('teachers')->paginate(20));
    }

    public function storeSubject(StoreVolunteerSubjectRequest $request)
    {
        $this->authorize('create', VolunteerSubject::class);
        $s = VolunteerSubject::create($request->validated());
        return response()->json($s, 201);
    }

    public function indexTeachers()
    {
        $this->authorize('viewAny', SubjectTeacher::class);
        return response()->json(SubjectTeacher::with('user','subject')->paginate(20));
    }

    public function storeTeacher(StoreSubjectTeacherRequest $request)
    {
        $this->authorize('create', SubjectTeacher::class);
        $data = $request->validated();
        $t = SubjectTeacher::create($data);
        return response()->json($t, 201);
    }
}
