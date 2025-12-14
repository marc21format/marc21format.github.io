<?php

namespace App\Http\Livewire\Profile\Volunteer\CommitteeMembers;

use Livewire\Component;
use App\Models\Committee;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class Create extends Component
{
    public $userId;
    public $user;
    public $committees = [];
    public $positions = [];
    public $prefillCommittee = null;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->committees = Committee::orderBy('committee_name')->get();

        // Build positions list with committee_ids (CSV) for client-side filtering
        if (Schema::hasTable('positions')) {
            $positions = DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->select(DB::raw('positions.position_id'), 'positions.position_name', DB::raw("GROUP_CONCAT(DISTINCT committee_positions.committee_id SEPARATOR ',') as committee_ids"))
                ->groupBy('positions.position_id','positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
            $this->positions = $positions;
        } else {
            // legacy fallback
            $this->positions = collect();
        }

        $this->prefillCommittee = request()->query('committee_id');
    }

    public function render()
    {
        return view('livewire.profile.volunteer.committee_members..create', [
            'user' => $this->user,
            'committees' => $this->committees,
            'positions' => $this->positions,
            'prefillCommittee' => $this->prefillCommittee,
        ]);
    }
}
