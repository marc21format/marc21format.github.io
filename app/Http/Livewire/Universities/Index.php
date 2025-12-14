<?php
namespace App\Http\Livewire\Universities;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\University;
use App\Models\EducationalRecord;
use App\Models\User;
use App\Models\DegreeProgram;

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

    public function render()
    {
        $query = University::orderBy('university_name');

        if ($this->search) {
            $query = $query->where('university_name', 'like', '%' . $this->search . '%');
        }

        $items = $query->paginate(15);

        $univIds = $items->pluck('university_id')->filter()->values()->all();

        // Load records for these universities (only records with degreeprogram) to build program/course aggregates
        $records = EducationalRecord::whereIn('university_id', $univIds)
            ->whereNotNull('degreeprogram_id')
            ->get(['university_id','user_id','degreeprogram_id']);

        // volunteer counts per university (distinct user_id)
        $volCounts = $records->groupBy('university_id')->map(function($g){
            return $g->pluck('user_id')->unique()->count();
        })->toArray();

        // program counts per university (distinct degreeprogram_id)
        $programCounts = $records->groupBy('university_id')->map(function($g){
            return $g->pluck('degreeprogram_id')->unique()->count();
        })->toArray();

        // collect volunteer user ids per university (limit 50 each)
        $volUserIdsByUniv = $records->groupBy('university_id')->map(function($g){
            return $g->pluck('user_id')->unique()->take(50)->values();
        });

        $allUserIds = $volUserIdsByUniv->flatten()->unique()->filter()->values()->all();

        // preload users and related profiles/credentials/committee membership
        $users = collect();
        if (!empty($allUserIds)) {
            $users = User::whereIn('id', $allUserIds)
                ->with(['userProfile','fceerProfile','professionalCredentials.prefix','professionalCredentials.suffix','committeeMemberships.committee','committeeMemberships.position'])
                ->get()->keyBy('id');
        }

        // map vol users per university in same order
        $volUsersByUniv = [];
        foreach ($volUserIdsByUniv as $univId => $ids) {
            $volUsersByUniv[$univId] = collect($ids)->map(function($id) use ($users){ return $users->get($id); })->filter();
        }

        // preload degree programs used on this page
        $degreeProgramIds = $records->pluck('degreeprogram_id')->unique()->filter()->values()->all();
        $programs = collect();
        if (!empty($degreeProgramIds)) {
            $programs = DegreeProgram::whereIn('degreeprogram_id', $degreeProgramIds)->get()->keyBy('degreeprogram_id');
        }

        return view('livewire.universities.index', [
            'universities' => $items,
            'volCounts' => $volCounts,
            'programCounts' => $programCounts,
            'volUsersByUniv' => $volUsersByUniv,
            'programsById' => $programs,
        ]);
    }
}
