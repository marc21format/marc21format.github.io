<?php
namespace App\Http\Livewire\Admin\Highschools;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Highschool;
use App\Models\User;

class Index extends Component
{
    use WithPagination;

    protected $listeners = ['highschoolOptionCreated' => '$refresh', 'highschoolOptionUpdated' => '$refresh'];

    public function mount()
    {
        // Resolve to the Eloquent User model so static analysis knows available helpers
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        if (! $authUser || (! ($authUser->isAdmin() || $authUser->isExecutive()))) {
            abort(403, 'Unauthorized');
        }
    }

    public function render()
    {
        $highschools = Highschool::orderBy('highschool_name')->paginate(20);
        return view('livewire.admin.highschools.index', ['highschools' => $highschools]);
    }
}
