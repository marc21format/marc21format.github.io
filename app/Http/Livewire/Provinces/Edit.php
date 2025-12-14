<?php
namespace App\Http\Livewire\Provinces;

use Livewire\Component;
use App\Models\Province;

class Edit extends Component
{
    public $province;
    public $province_name;

    protected function rules()
    {
        return ['province_name' => ['required','string','max:255']];
    }

    public function mount(Province $province)
    {
        $this->province = $province;
        $this->province_name = $province->province_name;
    }

    public function save()
    {
        $this->validate();
        $this->province->update(['province_name' => mb_convert_case(trim($this->province_name), MB_CASE_TITLE, 'UTF-8')]);
        session()->flash('status', 'Province updated');
        return redirect()->route('provinces.index');
    }

    public function render()
    {
        return view('livewire.provinces.edit');
    }
}
