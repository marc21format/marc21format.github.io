<?php
namespace App\Http\Livewire\Committee;

use Livewire\Component;
use App\Models\Committee;

class Index extends Component
{
    public $search = null;

    public function mount()
    {
        // placeholder
    }

    public function delete($id)
    {
        $c = Committee::find($id);
        if ($c) {
            $c->delete();
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('committeeDeleted', ['id' => $id]);
            } elseif (method_exists($this, 'dispatchBrowserEvent')) {
                $this->dispatchBrowserEvent('committeeDeleted', ['id' => $id]);
            } elseif (method_exists($this, 'emit')) {
                $this->emit('committeeDeleted', ['id' => $id]);
            }
            $this->emitSelf('$refresh');
        }
    }

    public function render()
    {
        // view not created yet
        return view('livewire.committee.index', [
            'committees' => Committee::orderBy('committee_name')->get(),
        ]);
    }
}
