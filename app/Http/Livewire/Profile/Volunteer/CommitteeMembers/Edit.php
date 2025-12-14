<?php

namespace App\Http\Livewire\Profile\Volunteer\CommitteeMembers;

use Livewire\Component;
use App\Models\CommitteeMember;
use App\Models\Committee;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    public $id;
    public $membership;
    public $membership_id;
    public $committees = [];
    public $positions = [];
    public $availablePositions = [];
    public $committee_id;
    public $position_id;

    public function mount($id)
    {
        $this->id = $id;
        $this->membership = CommitteeMember::findOrFail($id);
        $this->membership_id = $this->membership->member_id;
        $this->committee_id = $this->membership->committee_id;
        $this->position_id = $this->membership->position_id;
        $this->committees = Committee::orderBy('committee_name')->get();

        if (Schema::hasTable('positions')) {
            $positions = DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->select(DB::raw('positions.position_id'), 'positions.position_name', DB::raw("GROUP_CONCAT(DISTINCT committee_positions.committee_id SEPARATOR ',') as committee_ids"))
                ->groupBy('positions.position_id','positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
            $this->positions = $positions;
        } else {
            $this->positions = collect();
        }

        // Initialize available positions for the current membership's committee
        $this->loadAvailablePositions($this->committee_id);
    }

    protected function loadAvailablePositions($committeeId)
    {
        if (! $committeeId) {
            $this->availablePositions = collect();
            return;
        }
        if (Schema::hasTable('positions')) {
            $positions = DB::table('positions')
                ->join('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->where('committee_positions.committee_id', $committeeId)
                ->select('positions.position_id', 'positions.position_name')
                ->distinct()
                ->orderBy('positions.position_name')
                ->get();
            $this->availablePositions = $positions;
            return;
        }
        if (Schema::hasColumn('committee_positions', 'position_name')) {
            $positions = \App\Models\CommitteePosition::where('committee_id', $committeeId)->select('position_id', 'position_name')->orderBy('position_name')->get();
            $this->availablePositions = $positions;
            return;
        }
        $positions = \App\Models\CommitteePosition::where('committee_id', $committeeId)->select('position_id')->get();
        $this->availablePositions = $positions;
    }

    public function updatedCommitteeId($value)
    {
        // reload available positions when committee changes and clear selected position
        $this->loadAvailablePositions($value);
        $this->position_id = null;
    }

    public function render()
    {
        return view('livewire.profile.volunteer.committee_members.edit', [
            'membership' => $this->membership,
            'committees' => $this->committees,
            'positions' => $this->positions,
        ]);
    }

    public function save()
    {
        $rules = [
            'committee_id' => 'required|exists:committees,committee_id',
        ];
        if (Schema::hasTable('positions')) {
            $rules['position_id'] = 'required|exists:positions,position_id';
        } else {
            $rules['position_id'] = 'required|exists:committee_positions,position_id';
        }

        $data = $this->validate($rules);

        // Update membership model using scalars to avoid Livewire model hydration issues
        $m = CommitteeMember::findOrFail($this->membership_id);
        $m->update([
            'committee_id' => $this->committee_id,
            'position_id' => $this->position_id,
        ]);

        session()->flash('status', 'Membership updated');
        return redirect()->route('profile.volunteer.show', ['user' => $m->user_id]);
    }
}
