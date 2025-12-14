<?php
namespace App\Http\Livewire\DegreeLevels;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeLevel;

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
        $level = DegreeLevel::find($id);
        if ($level) {
            $level->delete();
            session()->flash('message', 'Degree level deleted successfully.');
        }
    }

    public function render()
    {
        $levels = DegreeLevel::with(['degreePrograms.educationalRecords.user'])
            ->withCount(['degreePrograms as program_count'])
            ->when($this->search, function($q){
                $q->where('level_name', 'like', '%'.$this->search.'%')
                  ->orWhere('abbreviation', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        $volCounts = [];
        foreach ($levels as $l) {
            $volCount = 0;
            foreach ($l->degreePrograms as $p) {
                $volCount += $p->educationalRecords->count();
            }
            $volCounts[$l->degreelevel_id] = $volCount;
        }

        return view('livewire.degree-levels.index', [
            'levels' => $levels,
            'volCounts' => $volCounts
        ]);
    }
}
