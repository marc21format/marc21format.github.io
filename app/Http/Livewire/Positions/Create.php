<?php

namespace App\Http\Livewire\Positions;

use Livewire\Component;
use App\Models\Position;
use App\Models\Committee;
use Illuminate\Support\Facades\DB;

class Create extends Component
{
    public $position_name = '';
    public $committee_ids = [];
    public $committees = [];

    protected $rules = [
        'position_name' => 'required|string',
        'committee_ids' => 'array',
    ];

    public function mount()
    {
        $this->committees = Committee::orderBy('committee_name')->get();
    }

    public function toggleCommittee($committeeId)
    {
        if (in_array($committeeId, $this->committee_ids)) {
            $this->committee_ids = array_diff($this->committee_ids, [$committeeId]);
        } else {
            $this->committee_ids[] = $committeeId;
        }
    }

    public function render()
    {
        return view('livewire.positions.create');
    }
}

function SchemaExists($table)
{
    return \Illuminate\Support\Facades\Schema::hasTable($table);
}
