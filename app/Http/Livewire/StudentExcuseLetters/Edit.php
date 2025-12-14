<?php

namespace App\Http\Livewire\StudentExcuseLetters;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\StudentExcuseLetter;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use WithFileUploads;

    public $user;
    public $letter;
    public $reason;
    public $date_attendance;
    public $status;
    public $letter_link;
    public $new_letter_file;
    public $session_choice;

    protected function rules()
    {
        $rules = [
            'reason' => 'required|string|max:255',
            'date_attendance' => 'required|date',
            'session_choice' => 'required|in:am,pm,both',
            'new_letter_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ];

        if (auth()->user()->role_id != 4) { // Not student
            $rules['status'] = 'required|in:pending,approved,rejected';
        }

        return $rules;
    }

    public function mount($user_id, $letter_id)
    {
        $this->user = User::findOrFail($user_id);
        $this->letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);

        $this->reason = $this->letter->reason;
        $this->date_attendance = $this->letter->date_attendance->format('Y-m-d');
        $this->status = $this->letter->status;
        $this->letter_link = $this->letter->letter_link;

        // Determine session_choice
        $this->session_choice = 'am';
        if ($this->letter->attendance_id_am && $this->letter->attendance_id_pm) {
            $this->session_choice = 'both';
        } elseif ($this->letter->attendance_id_pm) {
            $this->session_choice = 'pm';
        }
    }

    public function save()
    {
        $this->validate();

        $path = $this->letter->letter_link;
        if ($this->new_letter_file) {
            if ($this->letter->letter_link) {
                if (str_starts_with($this->letter->letter_link, '/storage/')) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $this->letter->letter_link));
                } else {
                    Storage::disk('public')->delete($this->letter->letter_link);
                }
            }
            $newPath = $this->new_letter_file->store('letters', 'public');
            $path = $newPath;
        }

        $updateData = [
            'reason/note' => $this->reason,
            'date_attendance' => $this->date_attendance,
            'letter_link' => $path,
        ];

        if (auth()->user()->role_id != 4) {
            $updateData['status'] = $this->status;
        }

        $this->letter->update($updateData);

        session()->flash('message', 'Excuse letter updated successfully.');

        return redirect()->route('student.excuse-letters.index', $this->user->id);
    }

    public function render()
    {
        return view('livewire.student-excuse-letters.edit');
    }
}