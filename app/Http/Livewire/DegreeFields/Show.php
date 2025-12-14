<?php
namespace App\Http\Livewire\DegreeFields;

use Livewire\Component;
use App\Models\DegreeField;
use App\Models\EducationalRecord;
use App\Models\University;

class Show extends Component
{
    public $field;
    public $usersCount;
    public $topUniversities;
    public $users;

    public function mount()
    {
        // Count users via degree programs with this field
        $this->usersCount = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreefield_id', $this->field->degreefield_id);
        })
        ->distinct('user_id')
        ->count('user_id');
        
        // Get distinct users with educational records for this field
        $this->users = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreefield_id', $this->field->degreefield_id);
        })
        ->select('user_id')
        ->distinct()
        ->with('user')
        ->orderBy('user_id')
        ->get();
        
        // Top 5 universities with most records for degree programs with this field
        $this->topUniversities = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreefield_id', $this->field->degreefield_id);
        })
        ->groupBy('university_id')
        ->selectRaw('university_id, COUNT(*) as record_count')
        ->orderByDesc('record_count')
        ->limit(5)
        ->with('university')
        ->get();
    }

    public function render()
    {
        return view('livewire.degree-fields.show');
    }
}
