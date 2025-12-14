<?php

namespace App\Http\Livewire\Universities;

use Livewire\Component;
use App\Models\University;
use App\Models\EducationalRecord;
use App\Models\User;
use App\Models\DegreeProgram;
use Illuminate\Support\Facades\DB;

class Show extends Component
{
    public $universityId;
    public $university;
    public $volunteers; // collection of User
    public $programCounts; // array of ['program' => DegreeProgram, 'count' => int]

    public function mount($universityId)
    {
        $this->universityId = $universityId;
        $this->loadData();
    }

    protected function loadData()
    {
        $this->university = University::where('university_id', $this->universityId)->first();
        if (! $this->university) {
            abort(404);
        }

        $volUserIds = EducationalRecord::where('university_id', $this->universityId)
            ->pluck('user_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        $this->volunteers = User::with(['userProfile','fceerProfile','professionalCredentials.prefix','professionalCredentials.suffix','committeeMemberships.committee','committeeMemberships.position'])
            ->whereIn('id', $volUserIds)
            ->get();

        $counts = EducationalRecord::select('degreeprogram_id', DB::raw('count(*) as cnt'))
            ->where('university_id', $this->universityId)
            ->whereNotNull('degreeprogram_id')
            ->groupBy('degreeprogram_id')
            ->orderByDesc('cnt')
            ->get();

        $programIds = $counts->pluck('degreeprogram_id')->unique()->filter()->values()->all();
        $programs = DegreeProgram::whereIn('degreeprogram_id', $programIds)->get()->keyBy('degreeprogram_id');

        $this->programCounts = [];
        foreach ($counts as $c) {
            $p = $programs[$c->degreeprogram_id] ?? null;
            $this->programCounts[] = ['program' => $p, 'count' => $c->cnt];
        }
    }

    public function render()
    {
        return view('livewire.universities.show', [
            'university' => $this->university,
            'volunteers' => $this->volunteers,
            'programCounts' => $this->programCounts,
        ]);
    }
}
