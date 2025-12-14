<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DegreeTypeController extends Controller
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
        return view('degree-types.index');
    }

    public function create()
    {
        return view('degree-types.create');
    }

    public function edit($id)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        return view('degree-types.edit', ['id' => $id]);
    }
}
