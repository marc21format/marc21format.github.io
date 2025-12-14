<?php

namespace App\Http\Livewire\StudentExcuseLetters;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\StudentExcuseLetter;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Storage;

class Create extends Component
{
    use WithFileUploads;

    public $user;
    public $reason;
    public $date_attendance;
    public $letter_file;
    public $session_choice;

    protected $rules = [
        'reason' => 'required|string|max:255',
        'date_attendance' => 'required|date',
        'letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        'session_choice' => 'required|in:am,pm,both',
    ];

    public function mount($user_id, $date = null, $session = 'am', $attendance_id = null)
    {
        $this->user = User::findOrFail($user_id);

        // Default session_choice to passed session or 'am'
        $this->session_choice = $session;

        if ($attendance_id) {
            $attendance = Attendance::find($attendance_id);
            if ($attendance) {
                $this->date_attendance = $attendance->date;
                $this->session_choice = $attendance->session;
            }
        } elseif ($date) {
            $this->date_attendance = $date;
        }
    }

    public function save()
    {
        $this->validate();

        $path = null;
        if ($this->letter_file) {
            $path = $this->letter_file->store('letters', 'public');
        }

        // Find or create attendance records based on session_choice
        $attendance_am = null;
        $attendance_pm = null;

        if (in_array($this->session_choice, ['am', 'both'])) {
            $attendance_am = Attendance::firstOrCreate(
                [
                    'user_id' => $this->user->id,
                    'date' => $this->date_attendance,
                    'session' => 'am',
                ],
                [
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        if (in_array($this->session_choice, ['pm', 'both'])) {
            $attendance_pm = Attendance::firstOrCreate(
                [
                    'user_id' => $this->user->id,
                    'date' => $this->date_attendance,
                    'session' => 'pm',
                ],
                [
                    'recorded_by' => auth()->id(),
                ]
            );
        }

        StudentExcuseLetter::create([
            'user_id' => $this->user->id,
            'attendance_id_am' => $attendance_am ? $attendance_am->attendance_id : null,
            'attendance_id_pm' => $attendance_pm ? $attendance_pm->attendance_id : null,
            'reason' => $this->reason,
            'date_attendance' => $this->date_attendance,
            'status' => 'pending',
            'letter_link' => $path,
        ]);

        session()->flash('message', 'Excuse letter created successfully.');

        return redirect()->route('student.excuse-letters.index', $this->user->id);
    }

    public function render()
    {
        return view('livewire.student-excuse-letters.create');
    }
}