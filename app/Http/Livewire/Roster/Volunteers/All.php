<?php
namespace App\Http\Livewire\Roster\Volunteers;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Committee;
use App\Models\CommitteePosition;
use App\Models\VolunteerSubject;
use App\Models\DegreeProgram;
use App\Models\FieldOfWork;
use App\Models\University;
use App\Http\Livewire\Traits\RosterFilters;
use Carbon\Carbon;

class All extends Component
{
    use WithPagination;

    use RosterFilters;

    protected $paginationTheme = 'tailwind';
    public $search = '';
    public $perPage = 15;
    public $filterField = '';
    public $sort = 'name';
    public $activeTab = 'all';
    
    // Add Volunteer Modal
    public $showAddModal = false;
    public $newUsername = '';
    public $newEmail = '';
    public $newPassword = '';

    public $committees = [];
    public $committeePositions = [];
    public $volunteerSubjects = [];
    public $degreePrograms = [];
    public $fieldsOfWork = [];
    public $universities = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Only Exec (Executive) and Admin can view the volunteers index/list.
        $user = Auth::check() ? User::find(Auth::id()) : null;
        if (! $user || ! ($user->isAdmin() || $user->isExecutive())) {
            abort(403, 'Unauthorized to view volunteers list');
        }

        // load helper lists defensively
        try { $this->committees = Committee::orderBy('committee_name')->get(); } catch (\Throwable $_) { $this->committees = collect(); }
        try { $this->committeePositions = CommitteePosition::orderBy('position_name')->get(); } catch (\Throwable $_) { $this->committeePositions = collect(); }
        try { $this->volunteerSubjects = VolunteerSubject::orderBy('subject_name')->get(); } catch (\Throwable $_) { $this->volunteerSubjects = collect(); }
        try { $this->degreePrograms = DegreeProgram::orderBy('name')->get(); } catch (\Throwable $_) { $this->degreePrograms = collect(); }
        try { $this->fieldsOfWork = FieldOfWork::orderBy('name')->get(); } catch (\Throwable $_) { $this->fieldsOfWork = collect(); }
        try { $this->universities = University::orderBy('name')->get(); } catch (\Throwable $_) { $this->universities = collect(); }
    }

    public function applySearch()
    {
        $this->resetPage();
    }

    /**
     * Triggered when user presses Enter in the search or filter input.
     */
    public function submitSearch()
    {
        $this->resetPage();
    }

    public function setTab(string $tab)
    {
        $allowed = ['all', 'committee', 'subjects'];
        if (! in_array($tab, $allowed, true)) {
            return;
        }

        if ($this->activeTab === $tab) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
    }
    
    public function openAddModal()
    {
        $this->showAddModal = true;
        $this->newUsername = '';
        $this->newEmail = '';
        $this->newPassword = '';
    }
    
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->newUsername = '';
        $this->newEmail = '';
        $this->newPassword = '';
    }
    
    public function saveNewVolunteer()
    {
        $this->validate([
            'newUsername' => 'required|string|max:255|unique:users,name',
            'newEmail' => 'required|email|max:255|unique:users,email',
            'newPassword' => 'required|string|min:6',
        ]);
        
        $user = User::create([
            'name' => $this->newUsername,
            'email' => $this->newEmail,
            'password' => bcrypt($this->newPassword),
            'role_id' => 3, // Volunteer role
        ]);
        
        $this->closeAddModal();
        session()->flash('message', 'Volunteer created successfully');
    }

    public function render()
    {
        // Filter to volunteer roles (here assumed role_id in [1,2,3])
        $query = User::whereIn('role_id', [1,2,3]);

        // If a specific field filter is selected, use the main search box as its value.
        if ($this->filterField && ($this->search !== null && $this->search !== '')) {
            $this->applyRosterFilter($query, $this->filterField, $this->search);
        } elseif ($this->search) {
            // No specific filter -> run a global search across many relations and columns
            $this->applyGlobalRosterSearch($query, $this->search);
        }

        $today = Carbon::today();

        $totalVolunteers = (clone $query)->count();

        $orderColumn = in_array($this->sort, ['name', 'volunteer_number'], true) ? $this->sort : 'name';

        $volunteers = $query->with([
                'fceerProfile.group',
                'committeeMemberships.committee',
                'committeeMemberships.position',
                'subjectTeachers.subject',
            'professionalCredentials.prefix',
            'professionalCredentials.suffix',
                'userProfile',
                'userProfile.address.city',
                'userProfile.address.barangay',
                'userProfile.address.province',
                'attendanceRecords' => function ($query) use ($today) {
                    $query->whereDate('date', $today);
                },
            ])
            ->orderBy($orderColumn)
            ->paginate($this->perPage);

        return view('livewire.roster.volunteers.all', [
            'volunteers' => $volunteers,
            'committees' => $this->committees,
            'committeePositions' => $this->committeePositions,
            'volunteerSubjects' => $this->volunteerSubjects,
            'degreePrograms' => $this->degreePrograms,
            'fieldsOfWork' => $this->fieldsOfWork,
            'universities' => $this->universities,
            'volunteerCount' => $totalVolunteers,
            'activeTab' => $this->activeTab,
            'todayLabel' => $today->format('F j, Y'),
        ]);
    }
}
