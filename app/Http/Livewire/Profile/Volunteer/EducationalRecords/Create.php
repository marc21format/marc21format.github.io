<?php
namespace App\Http\Livewire\Profile\Volunteer\EducationalRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalRecord;
use App\Models\User;
use App\Models\DegreeProgram;

class Create extends Component
{
    public $userId;

    public $degreeprogram_id;
    public $year_start;
    public $year_graduated;
    public $university_id;
    public $DOST_Scholarship = 0;
    public $latin_honor;

    public $isBachelor = false;

    protected $rules = [
        'degreeprogram_id' => 'required|exists:degree_programs,degreeprogram_id',
        'year_start' => 'nullable|integer|min:1900|max:2100',
    'year_graduated' => 'nullable|integer|min:1900|max:2100',
        'university_id' => 'nullable|exists:universities,university_id',
        'DOST_Scholarship' => 'nullable|boolean',
        'latin_honor' => 'nullable|in:Cum Laude,Magna Cum Laude,Summa Cum Laude',
    ];

    public function mount($userId = null)
    {
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $user = $userId ? User::findOrFail($userId) : $authUser;

        // Allow if creating for self or if staff
        if (! $authUser) {
            abort(403, 'Unauthorized');
        }

        if ($authUser->id !== ($user->id ?? null) && ! in_array($authUser->role_id, [1,2,3])) {
            abort(403, 'Unauthorized');
        }

        $this->userId = $user->id ?? $authUser->id;
    }

    public function save()
    {
        $this->validate();

        $record = EducationalRecord::create([
            'user_id' => $this->userId,
            'degreeprogram_id' => $this->degreeprogram_id,
            'year_start' => $this->year_start,
            'year_graduated' => $this->year_graduated,
            'university_id' => $this->university_id,
            'DOST_Scholarship' => $this->DOST_Scholarship,
            'latin_honor' => $this->latin_honor,
        ]);
        // After creating, redirect back to the appropriate profile page
        $user = User::find($this->userId);
        $roleId = $user->role_id ?? optional($user->role)->role_id ?? null;

        if (in_array((int) $roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $this->userId]);
        }

        return redirect()->route('profile.student.show', ['user' => $this->userId]);
    }

    public function render()
    {
        $degreePrograms = \App\Models\DegreeProgram::with('degreeLevel')->orderBy('full_degree_program_name')->get();
        $universities = \App\Models\University::orderBy('university_name')->get();
        $user = User::find($this->userId);

        return view('livewire.profile.volunteer.educational-records.create', [
            'degreePrograms' => $degreePrograms,
            'universities' => $universities,
            'user' => $user,
        ]);
    }

    public function updatedDegreeprogramId($value)
    {
        $this->isBachelor = false;
        if ($value) {
            $p = DegreeProgram::with('degreeLevel')->find($value);
            if ($p && $p->degreeLevel && stripos($p->degreeLevel->level_name, 'bachelor') !== false) {
                $this->isBachelor = true;
            }
        }
    }

    // Helper to force-check from the frontend (useful for reliably responding to
    // native select change events or edge cases where the updated* hook may not
    // trigger immediately). Accepts a program id and applies same logic.
    public function checkDegreeProgram($value)
    {
        $this->updatedDegreeprogramId($value);
    }
}
