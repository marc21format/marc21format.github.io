<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance as AttendanceModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $date = null;
    public $session = null; // 'am'|'pm'

    protected $listeners = ['attendanceUpdated' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDate()
    {
        $this->resetPage();
    }

    public function updatingSession()
    {
        $this->resetPage();
    }

    public function updatedSession($value)
    {
        $this->session = $value ? strtolower($value) : null;
        $this->resetPage();
    }

    public function setSession($s)
    {
        $this->session = $s ? strtolower($s) : null;
        $this->resetPage();
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    public function setDate()
    {
        $this->date = $this->date ?: null;
        $this->resetPage();
        try { $this->emit('attendanceUpdated'); } catch (\Throwable $_) { }
    }

    public function render()
    {
        // Show users (students and volunteers) and attach any attendance for the selected date/session.
        $sessionNormalized = $this->session ? strtolower($this->session) : null;

        $query = User::with([
            'attendanceRecords' => function ($q) use ($sessionNormalized) {
            if ($this->date) {
                $q->whereDate('date', $this->date);
            }
            if ($sessionNormalized) {
                $q->whereRaw('LOWER(session) = ?', [$sessionNormalized]);
            }
        }, 'attendanceRecords.recordedBy', 'fceerProfile.group']);

        // Only show volunteers and students
        $query->whereHas('role', function ($qr) {
            $qr->whereIn('role_title', ['Volunteer', 'Student']);
        });

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        return view('livewire.attendance.index', [
            'users' => $users,
            'actor' => Auth::user(),
            'date' => $this->date,
            'session' => $this->session,
        ]);
    }
}
