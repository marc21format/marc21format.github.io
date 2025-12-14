<?php
namespace App\Http\Livewire\Cities;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\City;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $cities = City::orderBy('city_name')
            ->when($this->search, fn($q) => $q->where('city_name', 'like', "%{$this->search}%"))
            ->paginate(15);

        return view('livewire.cities.index', compact('cities'));
    }
}
