<?php
namespace App\Http\Livewire\Roster\Students;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Room;
use App\Models\Highschool;
use App\Models\HighschoolSubject;
use Illuminate\Support\Facades\Auth;
use App\Http\Livewire\Traits\RosterFilters;

class All extends Component
{
    use WithPagination;

    use RosterFilters;

    protected $paginationTheme = 'tailwind';
    public $search = '';
    public $perPage = 15;
    public $filterField = '';
    public $activeTab = 'all';
    
    // Add Student Modal
    public $showAddModal = false;
    public $newUsername = '';
    public $newEmail = '';
    public $newPassword = '';

    // lists for dropdowns
    public $rooms = [];
    public $highschools = [];
    public $highschoolSubjects = [];

    protected $listeners = [
        'studentCreated' => '$refresh',
        'studentUpdated' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Only Exec (Executive) and Admin can view the students index/list.
        $user = Auth::check() ? User::find(Auth::id()) : null;
        if (! $user || ! ($user->isAdmin() || $user->isExecutive())) {
            abort(403, 'Unauthorized to view students list');
        }

        // load helper lists defensively
        try { $this->rooms = Room::orderBy('group')->get(); } catch (\Throwable $_) { $this->rooms = collect(); }
        try { $this->highschools = Highschool::orderBy('name')->get(); } catch (\Throwable $_) { $this->highschools = collect(); }
        try { $this->highschoolSubjects = HighschoolSubject::orderBy('name')->get(); } catch (\Throwable $_) { $this->highschoolSubjects = collect(); }
    }

    public function applySearch()
    {
        // called on Enter key in the search box — reset pagination so user sees first page of results
        $this->resetPage();
    }

    /**
     * Triggered when user presses Enter in the search or filter input.
     * Resets pagination and lets Livewire re-run the query.
     */
    public function submitSearch()
    {
        $this->resetPage();
    }

    public function setTab(string $tab)
    {
        $allowed = ['all', 'groups', 'highschools'];
        if (! in_array($tab, $allowed, true)) {
            return;
        }

        if ($this->activeTab === $tab) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function delete($id)
    {
        $actor = Auth::check() ? User::find(Auth::id()) : null;
        // Only Exec and Admin may delete student records.
        if (! $actor || ! ($actor->isAdmin() || $actor->isExecutive())) {
            abort(403, 'Unauthorized to delete student');
        }

        $user = User::find($id);
        if ($user) {
            $user->delete();
            if (method_exists($this, 'dispatch')) {
                try { $this->dispatch('notify', ['message' => 'Student deleted']); } catch (\Throwable $e) {}
            } elseif (method_exists($this, 'emit')) {
                $this->emit('notify', ['message' => 'Student deleted']);
            }
        }
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
    
    public function saveNewStudent()
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
            'role_id' => 4, // Student role
        ]);
        
        $this->closeAddModal();
        $this->emit('studentCreated');
        session()->flash('message', 'Student created successfully');
    }

    public function render()
    {
        // Filter to students (role_id = 4 per project conventions)
        $query = User::where('role_id', 4);

        // If a specific field filter is selected, use the main search box as its value.
        if ($this->filterField && ($this->search !== null && $this->search !== '')) {
            $this->applyRosterFilter($query, $this->filterField, $this->search);
        } elseif ($this->search) {
            // No specific filter -> run a global search across many relations and columns
            $this->applyGlobalRosterSearch($query, $this->search);
        }

    $students = $query->with([
            'fceerProfile.group',
            'userProfile',
            'userProfile.address.city',
            'userProfile.address.barangay',
            'userProfile.address.province',
            'highschoolRecords.highschool',
        ])->orderBy('name')->paginate($this->perPage);

        return view('livewire.roster.students.all', [
            'students' => $students,
            'rooms' => $this->rooms,
            'highschools' => $this->highschools,
            'highschoolSubjects' => $this->highschoolSubjects,
        ]);
    }
}
