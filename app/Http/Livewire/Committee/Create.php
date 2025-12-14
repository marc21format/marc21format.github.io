<?php
namespace App\Http\Livewire\Committee;

use Livewire\Component;
use App\Models\Committee;

class Create extends Component
{
    public $committee_name;

    public function save()
    {
        $this->validate(['committee_name' => 'required|string']);
        $c = Committee::create(['committee_name' => mb_convert_case(trim($this->committee_name), MB_CASE_TITLE, 'UTF-8')]);
        session()->flash('status', 'Committee created');
        return redirect()->route('committees.index');
    }

    public function render()
    {
        return view('livewire.committee.create');
    }
}
