<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\CommitteePosition;
use App\Models\Position;
use App\Models\Committee;

class CommitteePositionController extends Controller
{
    // Web: list page for positions
    public function index()
    {
    if (! Auth::check()) abort(403);
        if (Schema::hasTable('positions')) {
            $positions = \Illuminate\Support\Facades\DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->leftJoin('committees', 'committee_positions.committee_id', '=', 'committees.committee_id')
                ->select(\Illuminate\Support\Facades\DB::raw("MIN(positions.position_id) as position_id"), 'positions.position_name', \Illuminate\Support\Facades\DB::raw("GROUP_CONCAT(DISTINCT committees.committee_name SEPARATOR ', ') as committee_names"))
                ->groupBy('positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
        } elseif (Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::orderBy('position_name')->get();
        } else {
            $positions = CommitteePosition::orderBy('position_id')->get();
        }
        $committees = Committee::orderBy('committee_name')->get();
        return response()->json(['positions' => $positions, 'committees' => $committees]);
    }

    public function createForm(Request $request)
    {
    if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        $committees = Committee::orderBy('committee_name')->get();
        $prefillCommittee = $request->query('committee_id');
        return response()->json(['committees' => $committees, 'prefillCommittee' => $prefillCommittee]);
    }

    public function store(Request $request)
    {
    if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $committeeIds = $data['committee_ids'] ?? [];

        if (Schema::hasTable('positions')) {
            $pos = Position::create(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
            if (Schema::hasTable('committee_positions')) {
                foreach ($committeeIds as $cid) {
                    \Illuminate\Support\Facades\DB::table('committee_positions')->insert([
                        'position_id' => $pos->position_id,
                        'committee_id' => $cid,
                    ]);
                }
            }
            return response()->json($pos,201);
        }

        $p = CommitteePosition::create([
            'position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8'),
            'committee_id' => $committeeIds[0] ?? null,
        ]);
        return response()->json($p,201);
    }

    public function editForm($id)
    {
    if (! Auth::check()) abort(403);
        if (Schema::hasTable('positions')) {
            $position = Position::findOrFail($id);
        } else {
            $position = CommitteePosition::findOrFail($id);
        }
        $committees = Committee::orderBy('committee_name')->get();
        $mapped = [];
        if (Schema::hasTable('committee_positions')) {
            $mapped = \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $id)->pluck('committee_id')->toArray();
        } else {
            if (isset($position->committee_id)) {
                $mapped = [$position->committee_id];
            }
        }
        return response()->json(['position' => $position, 'committees' => $committees, 'mapped' => $mapped]);
    }

    public function update(Request $request, $id)
    {
    if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $committeeIds = $data['committee_ids'] ?? [];

        if (Schema::hasTable('positions')) {
            $p = Position::findOrFail($id);
            $p->update(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
            if (Schema::hasTable('committee_positions')) {
                \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $p->position_id)->delete();
                foreach ($committeeIds as $cid) {
                    \Illuminate\Support\Facades\DB::table('committee_positions')->insert([
                        'position_id' => $p->position_id,
                        'committee_id' => $cid,
                    ]);
                }
            }
            return response()->json($p);
        }

        $p = CommitteePosition::findOrFail($id);
        $p->update([
            'position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8'),
            'committee_id' => $committeeIds[0] ?? null,
        ]);
        return response()->json($p);
    }

    public function destroy($id)
    {
        if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        if (Schema::hasTable('positions')) {
            $position = Position::findOrFail($id);
            $sameIds = Position::where('position_name', $position->position_name)->pluck('position_id')->toArray();
            if (Schema::hasTable('committee_positions')) {
                \Illuminate\Support\Facades\DB::table('committee_positions')->whereIn('position_id', $sameIds)->delete();
            }
            $position->delete();
            return response()->json(null,204);
        }

        $p = CommitteePosition::findOrFail($id);
        if (Schema::hasTable('committee_positions')) {
            \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $id)->delete();
            return response()->json(null,204);
        }
        $p->delete();
        return response()->json(null,204);
    }
}
