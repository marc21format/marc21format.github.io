<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommitteePositionMappingController extends Controller
{
    // Store mapping rows for a given position (position_id => [committee_ids])
    public function storeMappings(Request $request, $positionId)
    {
    if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        $data = $request->validate([
            'committee_ids' => 'required|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $committeeIds = $data['committee_ids'];
        // replace existing mappings
        \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $positionId)->delete();
        foreach ($committeeIds as $cid) {
            \Illuminate\Support\Facades\DB::table('committee_positions')->insert([
                'position_id' => $positionId,
                'committee_id' => $cid,
            ]);
        }
        return response()->json(['ok' => true]);
    }

    public function destroyMappings($positionId)
    {
    if (! Auth::check() || ! optional(Auth::user())->isExecutive()) abort(403);
        \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $positionId)->delete();
        return response()->json(null,204);
    }
}
