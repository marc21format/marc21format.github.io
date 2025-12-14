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

    public function save()
    {
        $this->validate();
        $position = Position::create(['position_name' => mb_convert_case(trim($this->position_name), MB_CASE_TITLE, 'UTF-8')]);
        if (! empty($this->committee_ids) && SchemaExists('committee_positions')) {
            foreach ($this->committee_ids as $cid) {
                DB::table('committee_positions')->insert([
                    'position_id' => $position->position_id,
                    'committee_id' => $cid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        session()->flash('success', 'Position created');
        return redirect()->route('positions.index');
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
