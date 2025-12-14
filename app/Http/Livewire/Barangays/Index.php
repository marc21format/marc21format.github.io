<?php
namespace App\Http\Livewire\Barangays;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Barangay;

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
        $barangays = Barangay::with('city')
            ->when($this->search, fn($q) => $q->where('barangay_name', 'like', "%{$this->search}%"))
            ->orderBy('barangay_name')
            ->paginate(15);

        return view('livewire.barangays.index', compact('barangays'));
    }
}
