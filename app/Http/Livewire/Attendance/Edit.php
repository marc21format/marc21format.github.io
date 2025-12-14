<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use App\Models\Attendance as AttendanceModel;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Edit extends Component
{
    public $attendance;
    public $fields = [];
    public $targetUser;
    public $displayId;
    public $displayName;
    public $displayGroup;

    public function mount($attendanceId)
    {
        $this->attendance = AttendanceModel::find($attendanceId);
        if ($this->attendance) {
            $this->fields = $this->attendance->toArray();
            $this->targetUser = User::with('fceerProfile.group','committeeMemberships.committee')->find($this->attendance->user_id);
            if ($this->targetUser) {
                $this->displayName = $this->targetUser->name ?? ($this->targetUser->userProfile->f_name ?? '');
                if ($this->targetUser->fceerProfile) {
                    $this->displayId = $this->targetUser->fceerProfile->volunteer_number ?? $this->targetUser->fceerProfile->student_number ?? $this->targetUser->id;
                    $this->displayGroup = $this->targetUser->fceerProfile->group->group ?? null;
                } else {
                    $this->displayId = $this->targetUser->id;
                }
            }
        }
    }

    public function save()
    {
        $actor = Auth::user();
        if (! ($actor)) abort(403);

        $data = $this->validate([
            'fields.date' => 'nullable|date',
            'fields.attendance_time' => 'nullable|date_format:H:i:s',
            'fields.session' => 'nullable|in:am,pm',
        ])['fields'];

        $this->attendance->fill($data);
        $this->attendance->updated_by = $actor->id;
        $this->attendance->save();

        try {
            $this->emit('attendanceUpdated');
        } catch (\Throwable $_) {
            // emit should be available; ignore errors
        }
        session()->flash('success', 'Attendance updated.');
    }

    public function render()
    {
        return view('livewire.attendance.form', [
            'attendance' => $this->attendance,
        ]);
    }
}
