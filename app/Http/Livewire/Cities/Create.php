<?php
namespace App\Http\Livewire\Cities;

use Livewire\Component;
use App\Models\City;
use App\Models\Province;

class Create extends Component
{
    public $city_name;
    public $province_id;
    public $provinces = [];

    protected $rules = [
        'city_name' => ['required','string','max:255']
    ];

    public function save()
    {
        $this->validate();
        City::create([
            'city_name' => mb_convert_case(trim($this->city_name), MB_CASE_TITLE, 'UTF-8'),
            'province_id' => $this->province_id ?? null,
        ]);
        session()->flash('status', 'City created');
        return redirect()->route('cities.index');
    }

    public function mount()
    {
        $this->provinces = Province::orderBy('province_name')->get();
    }

    public function render()
    {
        return view('livewire.cities.create');
    }
}
