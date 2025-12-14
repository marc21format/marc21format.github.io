<?php
namespace App\Http\Livewire\Room;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $queryString = ['search'];

    public function mount()
    {
        $user = Auth::user();
        /** @var User|null $user */
        if (! ($user instanceof User) || (! $user->isAdmin() && ! $user->isExecutive())) {
            abort(403);
        }
    }

    public function render()
    {
        $qb = Room::with(['adviser','coAdviser','president','secretary'])
            ->orderBy('group', 'asc')
            ->when($this->search, function($q) {
                $q->where('group', 'like', "%{$this->search}%");
            });

        $rooms = $qb->paginate($this->perPage);
        return view('livewire.room.index', ['rooms' => $rooms]);
    }
}

