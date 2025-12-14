<?php

namespace App\Http\Livewire\Positions;

use Livewire\Component;
use App\Models\Position;
use App\Models\Committee;
use Illuminate\Support\Facades\DB;

class Edit extends Component
{
    public $position_id;
    public $position_name = '';
    public $committee_ids = [];
    public $committees = [];

    protected $rules = [
        'position_name' => 'required|string',
        'committee_ids' => 'array',
    ];

    public function mount($id)
    {
        $this->committees = Committee::orderBy('committee_name')->get();
        $pos = Position::findOrFail($id);
        $this->position_id = $pos->position_id;
        $this->position_name = $pos->position_name;
        if (SchemaExists('committee_positions')) {
            $this->committee_ids = DB::table('committee_positions')->where('position_id', $this->position_id)->pluck('committee_id')->toArray();
        }
    }

    public function toggleCommittee($committeeId)
    {
        if (in_array($committeeId, $this->committee_ids)) {
            $this->committee_ids = array_diff($this->committee_ids, [$committeeId]);
        } else {
            $this->committee_ids[] = $committeeId;
        }
    }
    }

    public function render()
    {
        return view('livewire.positions.edit');
    }
}

function SchemaExists($table)
{
    return \Illuminate\Support\Facades\Schema::hasTable($table);
}
