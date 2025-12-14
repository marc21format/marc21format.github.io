<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Position;
use App\Models\Committee;

class PositionController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Position::class);
        // load positions and aggregate mapped committee names from committee_positions
        if (Schema::hasTable('committee_positions')) {
            $positions = \Illuminate\Support\Facades\DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->leftJoin('committees', 'committee_positions.committee_id', '=', 'committees.committee_id')
                ->select(\Illuminate\Support\Facades\DB::raw("positions.position_id"), 'positions.position_name', \Illuminate\Support\Facades\DB::raw("GROUP_CONCAT(DISTINCT committees.committee_name SEPARATOR ', ') as committee_names"))
                ->groupBy('positions.position_id','positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
        } else {
            $positions = Position::orderBy('position_name')->get();
        }
        return response()->json($positions);
    }

    public function createForm()
    {
        $this->authorize('create', Position::class);
        $committees = Committee::orderBy('committee_name')->get();
        return response()->json(['committees' => $committees]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Position::class);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $position = Position::create(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
        $committeeIds = $data['committee_ids'] ?? [];
        if (! empty($committeeIds) && Schema::hasTable('committee_positions')) {
            foreach ($committeeIds as $cid) {
                \Illuminate\Support\Facades\DB::table('committee_positions')->insert([
                    'position_id' => $position->position_id,
                    'committee_id' => $cid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        return response()->json($position, 201);
    }

    public function editForm($id)
    {
        $this->authorize('update', Position::class);
        $position = Position::findOrFail($id);
        $committees = Committee::orderBy('committee_name')->get();
        $mapped = [];
        if (Schema::hasTable('committee_positions')) {
            $mapped = \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $id)->pluck('committee_id')->toArray();
        }
        return response()->json(['position' => $position, 'committees' => $committees, 'mapped' => $mapped]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('update', Position::class);
        $position = Position::findOrFail($id);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $position->update(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
        $committeeIds = $data['committee_ids'] ?? [];
        if (Schema::hasTable('committee_positions')) {
            \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $position->position_id)->delete();
            foreach ($committeeIds as $cid) {
                \Illuminate\Support\Facades\DB::table('committee_positions')->insert([
                    'position_id' => $position->position_id,
                    'committee_id' => $cid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        return response()->json($position);
    }

    public function destroy($id)
    {
        $this->authorize('delete', Position::class);
        $position = Position::findOrFail($id);
        if (Schema::hasTable('committee_positions')) {
            \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $position->position_id)->delete();
        }
        $position->delete();
        return response()->json(null, 204);
    }
}
