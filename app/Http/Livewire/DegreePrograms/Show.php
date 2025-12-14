<?php
namespace App\Http\Livewire\DegreePrograms;

use Livewire\Component;
use App\Models\DegreeProgram;
use App\Models\EducationalRecord;
use App\Models\University;

class Show extends Component
{
    public $program;
    public $usersCount;
    public $topUniversities;
    public $users;

    public function mount()
    {
        // Count users with this degree program via educational records
        $this->usersCount = EducationalRecord::where('degreeprogram_id', $program->degreeprogram_id)
            ->distinct('user_id')
            ->count('user_id');
        
        // Get distinct users with educational records for this program
        $this->users = EducationalRecord::where('degreeprogram_id', $program->degreeprogram_id)
            ->select('user_id')
            ->distinct()
            ->with('user')
            ->orderBy('user_id')
            ->get();
        
        // Top 5 universities with most records for this degree program
        $this->topUniversities = EducationalRecord::where('degreeprogram_id', $program->degreeprogram_id)
            ->groupBy('university_id')
            ->selectRaw('university_id, COUNT(*) as record_count')
            ->orderByDesc('record_count')
            ->limit(5)
            ->with('university')
            ->get();
    }

    public function render()
    {
        return view('livewire.degree-programs.show');
    }
}
