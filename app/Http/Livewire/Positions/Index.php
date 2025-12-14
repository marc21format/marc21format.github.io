<?php

namespace App\Http\Livewire\Positions;

use Livewire\Component;
use App\Models\Position;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $positions = [];

    public function mount()
    {
        if (SchemaExists('committee_positions')) {
            $this->positions = DB::table('positions')
                ->leftJoin('committee_positions', 'positions.position_id', '=', 'committee_positions.position_id')
                ->leftJoin('committees', 'committee_positions.committee_id', '=', 'committees.committee_id')
                ->select(DB::raw("positions.position_id"), 'positions.position_name', DB::raw("GROUP_CONCAT(DISTINCT committees.committee_name SEPARATOR ', ') as committee_names"))
                ->groupBy('positions.position_id', 'positions.position_name')
                ->orderBy('positions.position_name')
                ->get();
        } else {
            $this->positions = Position::orderBy('position_name')->get();
        }
    }

    public function delete($id)
    {
        $pos = Position::find($id);
        if (! $pos) {
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('toast', ['type' => 'error', 'message' => 'Position not found.']);
            } elseif (method_exists($this, 'dispatchBrowserEvent')) {
                $this->dispatchBrowserEvent('toast', ['type' => 'error', 'message' => 'Position not found.']);
            } elseif (method_exists($this, 'emit')) {
                $this->emit('toast', ['type' => 'error', 'message' => 'Position not found.']);
            }
            return;
        }
        // remove mappings
        if (SchemaExists('committee_positions')) {
            DB::table('committee_positions')->where('position_id', $pos->position_id)->delete();
        }
        $pos->delete();
        $this->mount();
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Position deleted']);
        } elseif (method_exists($this, 'dispatchBrowserEvent')) {
            $this->dispatchBrowserEvent('toast', ['type' => 'success', 'message' => 'Position deleted']);
        } elseif (method_exists($this, 'emit')) {
            $this->emit('toast', ['type' => 'success', 'message' => 'Position deleted']);
        }
    }

    public function render()
    {
        return view('livewire.positions.index');
    }
}

function SchemaExists($table)
{
    return \Illuminate\Support\Facades\Schema::hasTable($table);
}
