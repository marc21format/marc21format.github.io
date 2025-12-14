<?php
namespace App\Http\Livewire\Barangays;

use Livewire\Component;
use App\Models\Barangay;
use App\Models\City;

class Edit extends Component
{
    public $barangay;
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

    public function mount(Barangay $barangay)
    {
        $this->barangay = $barangay;
        $this->city_id = $barangay->city_id;
        $this->barangay_name = $barangay->barangay_name;
        $this->cities = City::orderBy('city_name')->get();
    }

    public function save()
    {
        $this->validate();
        $this->barangay->update([
            'city_id' => $this->city_id,
            'barangay_name' => mb_convert_case(trim($this->barangay_name), MB_CASE_TITLE, 'UTF-8'),
        ]);
        session()->flash('status', 'Barangay updated');
        return redirect()->route('barangays.index');
    }

    public function render()
    {
        return view('livewire.barangays.edit');
    }
}
