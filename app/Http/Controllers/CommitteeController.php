<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteePosition;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Committee::class);
        return response()->json(Committee::with('members')->paginate(20));
    }

    public function store(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate(['committee_name'=>'required|string']);
        $c = Committee::create($data);
        return response()->json($c,201);
    }

    public function storePosition(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate(['position_name'=>'required|string']);
        $p = CommitteePosition::create($data);
        return response()->json($p,201);
    }

    public function storeMember(Request $request)
    {
        $this->authorize('create', CommitteeMember::class);
        $data = $request->validate(['user_id'=>'required|exists:users,id','committee_id'=>'required|exists:committees,committee_id','position_id'=>'required|exists:committee_positions,position_id']);
        $m = CommitteeMember::create($data);
        return response()->json($m,201);
    }
}
