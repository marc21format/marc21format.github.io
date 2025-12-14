<?php
namespace App\Http\Livewire\DegreeLevels;

use Livewire\Component;
use App\Models\DegreeLevel;
use App\Models\EducationalRecord;
use App\Models\University;

class Show extends Component
{
    public $level;
    public $usersCount;
    public $topUniversities;
    public $users;

    public function mount()
    {
        // Count users via degree programs with this level
        $this->usersCount = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreelevel_id', $this->level->degreelevel_id);
        })
        ->distinct('user_id')
        ->count('user_id');
        
        // Get distinct users with educational records for this level
        $this->users = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreelevel_id', $this->level->degreelevel_id);
        })
        ->select('user_id')
        ->distinct()
        ->with('user')
        ->orderBy('user_id')
        ->get();
        
        // Top 5 universities with most records for degree programs with this level
        $this->topUniversities = EducationalRecord::whereHas('degreeProgram', function($query) {
            $query->where('degreelevel_id', $this->level->degreelevel_id);
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
        return view('livewire.degree-levels.show');
    }
}
