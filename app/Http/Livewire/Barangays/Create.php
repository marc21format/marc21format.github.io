<?php
namespace App\Http\Livewire\Barangays;

use Livewire\Component;
use App\Models\Barangay;
use App\Models\City;

class Create extends Component
{
    public $city_id;
    public $barangay_name;
    public $cities = [];

    protected function rules()
    {
        return [
            'city_id' => ['required','exists:cities,city_id'],
            'barangay_name' => ['required','string','max:255'],
        ];
    }

    public function mount()
    {
        $this->cities = City::orderBy('city_name')->get();
    }

    public function save()
    {
        $this->validate();
        Barangay::create([
            'city_id' => $this->city_id,
            'barangay_name' => mb_convert_case(trim($this->barangay_name), MB_CASE_TITLE, 'UTF-8'),
        ]);
        session()->flash('status', 'Barangay created');
        return redirect()->route('barangays.index');
    }

    public function render()
    {
        return view('livewire.barangays.create');
    }
}
