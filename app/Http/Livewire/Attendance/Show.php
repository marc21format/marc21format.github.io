<?php

namespace App\Http\Livewire\Attendance;

use Livewire\Component;
use App\Models\Attendance as AttendanceModel;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public $attendance;

    public function mount($attendanceId)
    {
        $this->attendance = AttendanceModel::with(['user','letter','recordedBy','updatedBy'])->find($attendanceId);
    }

    public function render()
    {
        return view('livewire.attendance.show', ['attendance' => $this->attendance, 'actor' => Auth::user()]);
    }
}
