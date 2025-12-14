<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UniversityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function isAdminOrExecOrInstructor()
    {
        $u = Auth::user();
        return $u && in_array($u->role_id, [1,2,3]);
    }

    public function index()
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        return view('universities.index');
    }

    public function create()
    {
        return view('universities.create');
    }

    public function edit($id)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        return view('universities.edit', ['id' => $id]);
    }

    public function destroy($university)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        // Accept either model or id
        $id = is_object($university) ? ($university->university_id ?? null) : $university;
        if (! $id) abort(404);
        $u = \App\Models\University::where('university_id', $id)->first();
        if (! $u) abort(404);
        // soft-delete if model uses soft deletes, otherwise delete
        try {
            $u->delete();
        } catch (\Exception $e) {
            return redirect()->route('universities.index')->with('error', 'Could not delete university.');
        }

        return redirect()->route('universities.index')->with('success', 'University deleted.');
    }
}
