<?php
namespace App\Http\Livewire\Provinces;

use Livewire\Component;
use App\Models\Province;

class Create extends Component
{
    public $province_name;

    protected $rules = [
        'province_name' => ['required','string','max:255']
    ];

    public function save()
    {
        $this->validate();
        Province::create(['province_name' => mb_convert_case(trim($this->province_name), MB_CASE_TITLE, 'UTF-8')]);
        session()->flash('status', 'Province created');
        return redirect()->route('provinces.index');
    }

    public function render()
    {
        return view('livewire.provinces.create');
    }
}
