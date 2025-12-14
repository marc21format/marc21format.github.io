<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use App\Models\Attendance as AttendanceModel;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\FceerProfile;

class Create extends Component
{
    public $fields = [
        'date' => null,
        'attendance_time' => null,
        'session' => 'auto',
    ];

    public $targetUser; // \\App\\Models\\User instance being recorded for
    public $displayId = null;
    public $displayName = null;
    public $displayGroup = null;

    public function mount($userId = null)
    {
        $userId = $userId ?? Auth::id();
        $this->targetUser = User::with('fceerProfile.group','committeeMemberships.committee')->find($userId);

        if ($this->targetUser) {
            $this->displayName = $this->targetUser->name ?? ($this->targetUser->userProfile->f_name ?? '');
            // Prefer fceer profile numbers if present
            if ($this->targetUser->fceerProfile) {
                $this->displayId = $this->targetUser->fceerProfile->volunteer_number ?? $this->targetUser->fceerProfile->student_number ?? $this->targetUser->id;
                $this->displayGroup = $this->targetUser->fceerProfile->group->group ?? null;
            } else {
                $this->displayId = $this->targetUser->id;
            }
        }
    }

    public function save()
    {
        $actor = Auth::user();
        /** @var User|null $actor */
        // Allow staff to create for anyone, or allow a user to create their own attendance.
        if (! ($actor instanceof User && ($actor->isStaff() || ($this->targetUser && $actor->id === $this->targetUser->id)) )) {
            abort(403);
        }

        $data = $this->validate([
            'fields.date' => 'nullable|date',
            'fields.attendance_time' => 'nullable|date_format:H:i:s',
        ])['fields'];

        $userId = $this->targetUser ? $this->targetUser->id : Auth::id();

        // Determine time: use provided time or now
        $time = $data['attendance_time'] ?? now()->format('H:i:s');
        // Derive session from hour (simple rule: before 12 => am)
        $hour = (int)substr($time, 0, 2);
        $session = $hour < 12 ? 'am' : 'pm';

        $attendance = AttendanceModel::create([
            'user_id' => $userId,
            'date' => $data['date'] ?? now()->toDateString(),
            'attendance_time' => $time,
            'session' => $session,
            'recorded_by' => $actor->id,
        ]);

        // Prefer Livewire emit when available, otherwise dispatch a browser event as a fallback
        try {
            $this->emit('attendanceUpdated');
        } catch (\Throwable $_) {
            // emit should be available in Livewire components; nothing to do if it fails
        }
        session()->flash('success', 'Attendance recorded.');
    }

    public function render()
    {
        return view('livewire.attendance.form');
    }
}
