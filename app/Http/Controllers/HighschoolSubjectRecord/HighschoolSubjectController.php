<?php

namespace App\Http\Controllers\HighschoolSubjectRecord;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HighschoolSubject;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class HighschoolSubjectController extends Controller
{
    public function __construct()
    {
        // require auth for create/store; restrict index/edit/update/destroy to admin/executive
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            /** @var \App\Models\User $actor */
            $actor = Auth::user();
            if (! $actor || (! $actor->isAdmin() && ! $actor->isExecutive())) {
                abort(403);
            }
            return $next($request);
        })->only(['index','edit','update','destroy']);
    }
    // Index listing for master highschool subject options
    public function index()
    {
        $subjects = HighschoolSubject::orderBy('subject_name')->paginate(50);
        return view('highschool_subjects.index', compact('subjects'));
    }

    // Show create form for master subject
    public function create(Request $request)
    {
        // pass optional user_id so UI can redirect back to a mapping form
        $userId = $request->query('user_id');
        $user = null;
        if ($userId) {
            $user = User::find($userId);
        }
        return view('highschool_subjects.create', ['userId' => $userId, 'user' => $user]);
    }

    // Store a new master subject
    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => ['required','string','max:255','unique:highschool_subjects,subject_name'],
        ]);

        HighschoolSubject::create([
            'subject_name' => $request->input('subject_name'),
            'subject_code' => $request->input('subject_code'),
        ]);

        $userId = $request->input('user_id');
        if ($userId) {
            return redirect()->route('users.highschool_subject_records.create', ['user' => $userId])->with('status', 'Subject added');
        }

        return redirect()->back()->with('status', 'Subject added');
    }

    // Show edit form (admin/executive only)
    public function edit(HighschoolSubject $subject)
    {
        // constructor middleware already restricts access
        return view('highschool_subjects.edit', compact('subject'));
    }

    // Update a master subject
    public function update(Request $request, HighschoolSubject $subject)
    {
        $data = $request->validate([
            'subject_name' => ['required','string','max:255','unique:highschool_subjects,subject_name,'.$subject->highschoolsubject_id.',highschoolsubject_id'],
            'subject_code' => ['nullable','string','max:50'],
        ]);

        $subject->fill($data);
        $subject->save();

        return redirect()->route('highschool_subjects.index')->with('status', 'Subject updated');
    }

    // Destroy master subject
    public function destroy(HighschoolSubject $subject)
    {
        $subject->delete();
        return redirect()->route('highschool_subjects.index')->with('status', 'Subject deleted');
    }
}
