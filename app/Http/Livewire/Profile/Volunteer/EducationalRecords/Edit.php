<?php
namespace App\Http\Livewire\Profile\Volunteer\EducationalRecords;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalRecord;
use App\Models\User;

class Edit extends Component
{
    public $recordId;
    public $record;

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

    public function mount($recordId)
    {
        $authUser = Auth::check() ? User::find(Auth::id()) : null;
        $record = EducationalRecord::findOrFail($recordId);

        // Only staff can edit
        if (! $authUser || ! in_array($authUser->role_id, [1,2,3])) {
            abort(403, 'Unauthorized');
        }

        $this->recordId = $record->record_id;
        $this->record = $record;
        $this->degreeprogram_id = $record->degreeprogram_id;
        $this->year_start = $record->year_start;
        $this->year_graduated = $record->year_graduated;
        $this->university_id = $record->university_id;
        $this->DOST_Scholarship = $record->DOST_Scholarship;
        $this->latin_honor = $record->latin_honor;

        // set bachelor flag based on program
        if ($this->degreeprogram_id) {
            $p = \App\Models\DegreeProgram::with('degreeLevel')->find($this->degreeprogram_id);
            if ($p && $p->degreeLevel && stripos($p->degreeLevel->level_name, 'bachelor') !== false) {
                $this->isBachelor = true;
            }
        }
    }

    public function save()
    {
        $this->validate();

        $this->record->update([
            'degreeprogram_id' => $this->degreeprogram_id,
            'year_start' => $this->year_start,
            'year_graduated' => $this->year_graduated,
            'university_id' => $this->university_id,
            'DOST_Scholarship' => $this->DOST_Scholarship,
            'latin_honor' => $this->latin_honor,
        ]);
        // After updating, redirect back to the appropriate profile page
        $user = User::find($this->record->user_id);
        $roleId = $user->role_id ?? optional($user->role)->role_id ?? null;

        if (in_array((int) $roleId, [1,2,3], true)) {
            return redirect()->route('profile.volunteer.show', ['user' => $user->id]);
        }

        return redirect()->route('profile.student.show', ['user' => $user->id]);
    }

    public function render()
    {
        $degreePrograms = \App\Models\DegreeProgram::with('degreeLevel')->orderBy('full_degree_program_name')->get();
        $universities = \App\Models\University::orderBy('university_name')->get();
        $user = User::find($this->record->user_id);

        return view('livewire.profile.volunteer.educational-records.edit', [
            'record' => $this->record,
            'degreePrograms' => $degreePrograms,
            'universities' => $universities,
            'user' => $user,
        ]);
    }
}
