<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DegreeFieldMappingController extends Controller
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
        return view('profile.volunteer.degree-field-mappings.index');
    }

    public function create()
    {
        return view('profile.volunteer.degree-field-mappings.create');
    }

    public function edit($id)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        return view('profile.volunteer.degree-field-mappings.edit', ['id' => $id]);
    }
}
