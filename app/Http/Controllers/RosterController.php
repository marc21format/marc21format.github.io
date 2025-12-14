<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class RosterController extends Controller
{
    /**
     * Show volunteers roster wrapper (Blade embedding Livewire component).
     */
    public function volunteersIndex()
    {
        $user = Auth::check() ? User::find(Auth::id()) : null;
        if (! $user || ! ($user->isAdmin() || $user->isExecutive())) {
            abort(403, 'Unauthorized to view volunteers roster');
        }
        return view('roster.volunteers.index');
    }

    /**
     * Show students roster wrapper (Blade embedding Livewire component).
     */
    public function studentsIndex()
    {
        $user = Auth::check() ? User::find(Auth::id()) : null;
        if (! $user || ! ($user->isAdmin() || $user->isExecutive())) {
            abort(403, 'Unauthorized to view students roster');
        }
        return view('roster.students.index');
    }

    /**
     * Keep a simple `index` alias for volunteers to match existing routes.
     */
    public function index()
    {
        return $this->volunteersIndex();
    }
}
