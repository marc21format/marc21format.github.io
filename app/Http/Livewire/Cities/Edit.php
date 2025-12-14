<?php
namespace App\Http\Livewire\Cities;

use Livewire\Component;
use App\Models\City;
use App\Models\Province;

class Edit extends Component
{
    public $city;
    public $city_name;
    public $province_id;
    public $provinces = [];

    protected function rules()
    {
        return ['city_name' => ['required','string','max:255']];
    }

    public function mount(City $city)
    {
        $this->city = $city;
        $this->city_name = $city->city_name;
        $this->province_id = $city->province_id ?? null;
        $this->provinces = Province::orderBy('province_name')->get();
    }

    public function save()
    {
        $this->validate();
        $this->city->update([
            'city_name' => mb_convert_case(trim($this->city_name), MB_CASE_TITLE, 'UTF-8'),
            'province_id' => $this->province_id ?? null,
        ]);
        session()->flash('status', 'City updated');
        return redirect()->route('cities.index');
    }

    public function render()
    {
        return view('livewire.cities.edit');
    }
}
