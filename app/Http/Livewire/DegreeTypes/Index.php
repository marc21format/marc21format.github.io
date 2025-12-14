<?php
namespace App\Http\Livewire\DegreeTypes;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeType;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public function mount()
    {
        if (! Auth::check() || ! in_array(Auth::user()->role_id, [1,2,3])) {
            abort(403);
        }
    }

    public function delete($id)
    {
        $type = DegreeType::find($id);
        if ($type) {
            $type->delete();
            session()->flash('message', 'Degree type deleted successfully.');
        }
    }

    public function render()
    {
        $types = DegreeType::with(['degreePrograms.educationalRecords.user'])
            ->withCount(['degreePrograms as program_count'])
            ->when($this->search, function($q){
                $q->where('type_name', 'like', '%'.$this->search.'%')
                  ->orWhere('abbreviation', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        $volCounts = [];
        foreach ($types as $t) {
            $volCount = 0;
            foreach ($t->degreePrograms as $p) {
                $volCount += $p->educationalRecords->count();
            }
            $volCounts[$t->degreetype_id] = $volCount;
        }

        return view('livewire.degree-types.index', [
            'types' => $types,
            'volCounts' => $volCounts
        ]);
    }
}
