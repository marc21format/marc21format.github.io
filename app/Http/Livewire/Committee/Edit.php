<?php
namespace App\Http\Livewire\Committee;

use Livewire\Component;
use App\Models\Committee;

class Edit extends Component
{
    public $committee;
    public $committee_name;

    public function mount($id = null)
    {
        $this->committee = $id ? Committee::find($id) : null;
        $this->committee_name = $this->committee->committee_name ?? null;
    }

    public function save()
    {
        $this->validate(['committee_name' => 'required|string']);
        if ($this->committee) {
            $this->committee->update(['committee_name' => mb_convert_case(trim($this->committee_name), MB_CASE_TITLE, 'UTF-8')]);
        }
        session()->flash('status', 'Committee updated');
        return redirect()->route('committees.index');
    }

    public function render()
    {
        return view('livewire.committee.edit');
    }
}
