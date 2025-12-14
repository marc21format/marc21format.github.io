<?php
namespace App\Http\Livewire\DegreeFields;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\DegreeField;

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
        $field = DegreeField::find($id);
        if ($field) {
            $field->delete();
            session()->flash('message', 'Degree field deleted successfully.');
        }
    }

    public function render()
    {
        $fields = DegreeField::with(['degreePrograms.educationalRecords.user'])
            ->withCount(['degreePrograms as program_count'])
            ->when($this->search, function($q){
                $q->where('field_name', 'like', '%'.$this->search.'%')
                  ->orWhere('abbreviation', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        $volCounts = [];
        foreach ($fields as $f) {
            $volCount = 0;
            foreach ($f->degreePrograms as $p) {
                $volCount += $p->educationalRecords->count();
            }
            $volCounts[$f->degreefield_id] = $volCount;
        }

        return view('livewire.degree-fields.index', [
            'fields' => $fields,
            'volCounts' => $volCounts
        ]);
    }
}
