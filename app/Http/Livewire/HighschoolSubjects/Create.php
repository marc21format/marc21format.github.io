<?php
namespace App\Http\Livewire\Profile\Student\HighschoolSubjects;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\HighschoolSubject;
use App\Models\HighschoolSubjectRecord;

class Create extends Component
{
    // Create a master HighschoolSubject (not a per-user record)
    public $userId;
    public $subject_name;
    public $subject_subname;
    public $subject_code;

    protected $rules = [
        'subject_name' => 'required|string|max:255',
        'subject_subname' => 'nullable|string|max:255',
        'subject_code' => 'nullable|string|max:60',
    ];

    public function mount($userId = null)
    {
        // Keep userId optional — we don't block creation based on role or ownership.
        // But use it for redirecting to the per-user record creation if available.
        $this->userId = $userId ?: (Auth::check() ? Auth::id() : null);
    }

    public function store()
    {
        $this->validate();

        $subject = HighschoolSubject::create([
            'subject_name' => $this->subject_name,
            'subject_subname' => $this->subject_subname,
            'subject_code' => $this->subject_code,
        ]);

        // Notify other Livewire components (if any) that a new option exists
        // Prefer the newer dispatch technique; fall back to emit if dispatch is not available
        if (method_exists($this, 'dispatch')) {
            try { $this->dispatch('highschoolSubjectOptionCreated', $subject->getKey(), $subject->subject_name); } catch (\Throwable $e) { /* ignore */ }
        } elseif (method_exists($this, 'emit')) {
            $this->emit('highschoolSubjectOptionCreated', $subject->getKey(), $subject->subject_name);
        }

        // If we have a target user, redirect to the per-user student-highschool-subject create form
        if ($this->userId) {
            return redirect()->route('users.highschool_subject_records.create', ['user' => $this->userId]);
        }

        // Fallback: go back to master subjects index
        return redirect()->route('highschool_subjects.index');
    }

    public function render()
    {
        // render the Livewire partial (not the controller blade) so the mapping
        // form and component state render correctly. Pass userId as well.
        return view('livewire.highschool-subjects.create', [
            'userId' => $this->userId,
        ]);
    }
}

