<?php
namespace App\Http\Livewire\Provinces;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Province;

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
        $provinces = Province::orderBy('province_name')
            ->when($this->search, fn($q) => $q->where('province_name', 'like', "%{$this->search}%"))
            ->paginate(15);

        return view('livewire.provinces.index', compact('provinces'));
    }
}
