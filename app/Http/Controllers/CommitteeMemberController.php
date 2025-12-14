<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\CommitteePosition;
use App\Models\Position;
use Illuminate\Support\Facades\Schema;
use App\Models\CommitteeMember;
use Illuminate\Http\Request;

class CommitteeMemberController extends Controller
{
    // Committees CRUD (API-style)
    public function indexCommittees()
    {
        $this->authorize('viewAny', Committee::class);
        return response()->json(Committee::with('members')->orderBy('committee_name')->paginate(20));
    }

    // Members CRUD (API-style)
    public function indexMembers()
    {
        $this->authorize('viewAny', CommitteeMember::class);
        return response()->json(CommitteeMember::with(['user','committee','position'])->orderBy('id')->paginate(20));
    }

    // Web: list page for committees
    public function listCommitteesPage()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) abort(403);
        $committees = Committee::orderBy('committee_name')->get();
        // order by name if column exists, otherwise fallback to primary key
        if (\Illuminate\Support\Facades\Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::orderBy('position_name')->get();
        } else {
            $positions = CommitteePosition::orderBy('position_id')->get();
        }
        return view('committees.index', compact('committees','positions'));
    }

    public function createCommitteeForm()
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        return view('committees.create');
    }

    public function editCommitteeForm($id)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) abort(403);
        $committee = Committee::findOrFail($id);
        if (\Illuminate\Support\Facades\Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::orderBy('position_name')->get();
        } else {
            $positions = CommitteePosition::orderBy('position_id')->get();
        }
        return view('committees.edit', compact('committee','positions'));
    }

    public function storeCommittee(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate(['committee_name' => 'required|string']);
        $c = Committee::create(['committee_name' => mb_convert_case(trim($data['committee_name']), MB_CASE_TITLE, 'UTF-8')]);
        return response()->json($c, 201);
    }

    public function updateCommittee(Request $request, $id)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate(['committee_name' => 'required|string']);
        $c = Committee::findOrFail($id);
        $c->update(['committee_name' => mb_convert_case(trim($data['committee_name']), MB_CASE_TITLE, 'UTF-8')]);
        return response()->json($c);
    }

    public function destroyCommittee($id)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $c = Committee::findOrFail($id);
        $c->delete();
        return response()->json(null,204);
    }

    // Positions CRUD
    public function indexPositions()
    {
        $this->authorize('viewAny', CommitteePosition::class);
        if (\Illuminate\Support\Facades\Schema::hasColumn('committee_positions', 'position_name')) {
            return response()->json(CommitteePosition::orderBy('position_name')->paginate(20));
        }
        return response()->json(CommitteePosition::orderBy('position_id')->paginate(20));
    }

    // Web: list/create/edit pages for positions
    public function listPositionsPage()
    {
        if (! \Illuminate\Support\Facades\Auth::check()) abort(403);
        // Prefer master positions table when available
        if (Schema::hasTable('positions')) {
            // Load unique positions by name and aggregate their mapped committee names (comma-separated)
            // This prevents duplicate rows when the same position_name exists multiple times in `positions`.
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
        return view('committee_positions.index', compact('positions','committees'));
    }

    public function createPositionForm(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $committees = Committee::orderBy('committee_name')->get();
        $prefillCommittee = $request->query('committee_id');
        return view('committee_positions.create', compact('committees','prefillCommittee'));
    }

    public function editPositionForm($id)
    {
        if (! \Illuminate\Support\Facades\Auth::check()) abort(403);
        if (Schema::hasTable('positions')) {
            $position = Position::findOrFail($id);
        } else {
            $position = CommitteePosition::findOrFail($id);
        }
        $committees = Committee::orderBy('committee_name')->get();
        // load existing mappings for prefill
        $mapped = [];
        if (Schema::hasTable('committee_positions')) {
            // committee_positions is used as the mapping table (position_id, committee_id)
            $mapped = \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $id)->pluck('committee_id')->toArray();
        } else {
            // fallback: if legacy committee_positions stores committee_id directly on the position model
            if (isset($position->committee_id)) {
                $mapped = [$position->committee_id];
            }
        }
        return view('committee_positions.edit', compact('position','committees','mapped'));
    }
    public function storePosition(Request $request)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $committeeIds = $data['committee_ids'] ?? [];

        if (Schema::hasTable('positions')) {
            $pos = Position::create(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
            // persist mappings into the existing committee_positions table
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

        // Legacy single-table behavior (committee_positions contains position_name)
        $p = CommitteePosition::create([
            'position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8'),
            'committee_id' => $committeeIds[0] ?? null,
        ]);
        return response()->json($p,201);
    }

    public function updatePosition(Request $request, $id)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $data = $request->validate([
            'position_name' => 'required|string',
            'committee_ids' => 'nullable|array',
            'committee_ids.*' => 'exists:committees,committee_id',
        ]);
        $committeeIds = $data['committee_ids'] ?? [];

        if (Schema::hasTable('positions')) {
            $p = Position::findOrFail($id);
            $p->update(['position_name' => mb_convert_case(trim($data['position_name']), MB_CASE_TITLE, 'UTF-8')]);
            // replace existing mappings in committee_positions
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

    public function destroyPosition($id)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        // If we have a master `positions` table, ensure any mapping rows that reference this position
        // (and any other positions with the same name) are removed to avoid orphaned mappings.
        if (Schema::hasTable('positions')) {
            $position = Position::findOrFail($id);
            // collect all position_ids that share the same canonical name
            $sameIds = Position::where('position_name', $position->position_name)->pluck('position_id')->toArray();
            if (Schema::hasTable('committee_positions')) {
                \Illuminate\Support\Facades\DB::table('committee_positions')->whereIn('position_id', $sameIds)->delete();
            }
            // delete only the requested Position record
            $position->delete();
            return response()->json(null,204);
        }

        // Legacy behaviour: committee_positions contains the position rows themselves
        $p = CommitteePosition::findOrFail($id);
        // if this table is also used as mapping table, remove any mapping rows that reference the same position_id
        if (Schema::hasTable('committee_positions')) {
            \Illuminate\Support\Facades\DB::table('committee_positions')->where('position_id', $id)->delete();
            return response()->json(null,204);
        }
        $p->delete();
        return response()->json(null,204);
    }

    // Web: show form to add a committee membership for a specific user (used from profile page)
    public function createMemberForUser(Request $request, $userId)
    {
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $committees = Committee::orderBy('committee_name')->get();
        // When a master `positions` table exists, fetch positions and their mapping committee ids
        if (Schema::hasTable('positions')) {
                // fetch each position once and include CSV of mapped committee ids
                $positions = \Illuminate\Support\Facades\DB::table('positions')
                    ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                    ->select('positions.position_id', 'positions.position_name', \Illuminate\Support\Facades\DB::raw("GROUP_CONCAT(DISTINCT committee_positions.committee_id SEPARATOR ',') as committee_ids"))
                    ->groupBy('positions.position_id','positions.position_name')
                    ->orderBy('positions.position_name')
                    ->get();
        } elseif (Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::orderBy('position_name')->get();
        } else {
            $positions = CommitteePosition::orderBy('position_id')->get();
        }
        $prefillCommittee = $request->query('committee_id');
        // Normalize positions into a simple array of objects for client-side filtering
        $positions = collect($positions)->map(function ($p) {
            $posId = isset($p->position_id) ? $p->position_id : (isset($p->id) ? $p->id : null);
            $posName = isset($p->position_name) ? $p->position_name : (isset($p->position) ? $p->position : null);
            $committeeIds = '';
            if (isset($p->committee_ids)) {
                $committeeIds = $p->committee_ids;
            } elseif (isset($p->committee_id)) {
                $committeeIds = (string) $p->committee_id;
            }
            return (object) [
                'position_id' => $posId,
                'position_name' => $posName,
                'committee_ids' => $committeeIds,
            ];
        })->values();
        $user = \App\Models\User::findOrFail($userId);
        return view('profile.volunteer.committee_members.create', compact('committees','positions','prefillCommittee','user'));
    }

    // AJAX: return positions mapped to a committee (used by membership form)
    public function ajaxPositionsByCommittee(Request $request)
    {
        $cid = $request->query('committee_id');
        if (! $cid) {
            return response()->json([], 200);
        }
        // prefer master positions table when available
        if (Schema::hasTable('positions')) {
            $positions = \Illuminate\Support\Facades\DB::table('positions')
                ->join('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->where('committee_positions.committee_id', $cid)
                ->select('positions.position_id', 'positions.position_name')
                ->distinct()
                ->orderBy('positions.position_name')
                ->get();
            return response()->json($positions);
        }
        // legacy table where committee_positions contains position_name
        if (Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::where('committee_id', $cid)->select('position_id', 'position_name')->orderBy('position_name')->get();
            return response()->json($positions);
        }
        // fallback: return any mapping rows with the committee id
        $positions = CommitteePosition::where('committee_id', $cid)->select('position_id')->get();
        return response()->json($positions);
    }

    // Web: store a committee membership for a specific user
    public function storeMemberForUser(Request $request, $userId)
    {
        // Only executives may add memberships via profile UI
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) abort(403);
        $rules = ['committee_id' => 'required|exists:committees,committee_id'];
        if (Schema::hasTable('positions')) {
            $rules['position_id'] = 'required|exists:positions,position_id';
        } else {
            $rules['position_id'] = 'required|exists:committee_positions,position_id';
        }
        $data = $request->validate($rules);
        $data['user_id'] = $userId;
        $m = CommitteeMember::create($data);
    // After creating, redirect back to the volunteer profile page (explicit)
    return redirect()->route('profile.volunteer.show', ['user' => $userId])->with('status', 'Committee membership added');
    }

    // Web: show edit form for an existing committee membership
    public function editMemberForm($id)
    {
        $m = CommitteeMember::findOrFail($id);
        // only executives may edit memberships via this UI
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) {
            abort(403);
        }
        $committees = Committee::orderBy('committee_name')->get();

        // Provide positions similar to createMemberForUser so the edit form can filter client-side
        if (Schema::hasTable('positions')) {
            $positions = \Illuminate\Support\Facades\DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->select('positions.position_id', 'positions.position_name', \Illuminate\Support\Facades\DB::raw("GROUP_CONCAT(DISTINCT committee_positions.committee_id SEPARATOR ',') as committee_ids"))
                ->groupBy('positions.position_id','positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
        } elseif (Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = CommitteePosition::orderBy('position_name')->get();
        } else {
            $positions = CommitteePosition::orderBy('position_id')->get();
        }
        $positions = collect($positions)->map(function ($p) {
            $posId = isset($p->position_id) ? $p->position_id : (isset($p->id) ? $p->id : null);
            $posName = isset($p->position_name) ? $p->position_name : (isset($p->position) ? $p->position : null);
            $committeeIds = '';
            if (isset($p->committee_ids)) {
                $committeeIds = $p->committee_ids;
            } elseif (isset($p->committee_id)) {
                $committeeIds = (string) $p->committee_id;
            }
            return (object) [
                'position_id' => $posId,
                'position_name' => $posName,
                'committee_ids' => $committeeIds,
            ];
        })->values();

        $membership = $m;
        return view('profile.volunteer.committee_members.edit', compact('membership','committees','positions'));
    }

    public function updateMember(Request $request, $id)
    {
        $m = CommitteeMember::findOrFail($id);
        // only executives may update memberships via this UI
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) {
            abort(403);
        }
        $rules = [
            'user_id' => 'required|exists:users,id',
            'committee_id' => 'required|exists:committees,committee_id',
        ];
        if (Schema::hasTable('positions')) {
            $rules['position_id'] = 'required|exists:positions,position_id';
        } else {
            $rules['position_id'] = 'required|exists:committee_positions,position_id';
        }
        $data = $request->validate($rules);
    $m->update($data);
    return redirect()->route('profile.volunteer.show', ['user' => $m->user_id])->with('status', 'Membership updated');
    }

    public function destroyMember($id)
    {
        // only executives may delete memberships via this UI
        if (! \Illuminate\Support\Facades\Auth::check() || ! optional(\Illuminate\Support\Facades\Auth::user())->isExecutive()) {
            abort(403);
        }
        $m = CommitteeMember::findOrFail($id);
        $m->delete();
        return response()->json(null,204);
    }
}
