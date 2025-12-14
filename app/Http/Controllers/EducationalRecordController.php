<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationalRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function isStaff()
    {
        $u = Auth::user();
        return $u && in_array($u->role_id, [1,2,3]);
    }

    public function index($userId = null)
    {
        // Only staff can view index of records
        if (! $this->isStaff()) abort(403);
        return view('profile.volunteer.educational-records.index', ['userId' => $userId]);
    }

    public function create($userId = null)
    {
        // volunteers (non-students) can create
        return view('profile.volunteer.educational-records.create', ['userId' => $userId]);
    }

    public function edit($id = null)
    {
        if (! $this->isStaff()) abort(403);
        $record = \App\Models\EducationalRecord::find($id);
        return view('profile.volunteer.educational-records.edit', ['record' => $record]);
    }
}
