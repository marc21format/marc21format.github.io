<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DegreeProgramController extends Controller
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
        return view('degree-programs.index');
    }

    public function create()
    {
        // create allowed to volunteers
        return view('degree-programs.create');
    }

    public function edit($id)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        return view('degree-programs.edit', ['id' => $id]);
    }

    public function store(Request $request)
    {
        // handled by Livewire component
        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        // handled by Livewire component
        return redirect()->back();
    }

    public function destroy($id)
    {
        if (! $this->isAdminOrExecOrInstructor()) abort(403);
        // handled by controller action or Livewire
        return redirect()->back();
    }
}
