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

    public function save()
    {
        $this->validate();
        $pos = Position::findOrFail($this->position_id);
        $pos->update(['position_name' => mb_convert_case(trim($this->position_name), MB_CASE_TITLE, 'UTF-8')]);
        if (SchemaExists('committee_positions')) {
            DB::table('committee_positions')->where('position_id', $this->position_id)->delete();
            foreach ($this->committee_ids as $cid) {
                DB::table('committee_positions')->insert([
                    'position_id' => $this->position_id,
                    'committee_id' => $cid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        session()->flash('success', 'Position updated');
        return redirect()->route('positions.index');
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
