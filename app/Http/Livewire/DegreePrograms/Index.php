<?php
namespace App\Http\Livewire\DegreePrograms;

use Livewire\Component;
use App\Models\DegreeProgram;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $tab = 'all';
    public $search = '';

    public function render()
    {
        $query = DegreeProgram::with([
            'educationalRecords.user.userProfile',
            'educationalRecords.user.professionalCredentials.prefix',
            'educationalRecords.user.professionalCredentials.suffix',
            'educationalRecords.user.fceerProfile',
            'educationalRecords.user.committeeMemberships.committee',
            'educationalRecords.user.committeeMemberships.position'
        ])->orderBy('full_degree_program_name');

        if ($this->tab === 'used') {
            $query = $query->whereHas('educationalRecords');
        } elseif ($this->tab === 'unused') {
            $query = $query->whereDoesntHave('educationalRecords');
        }

        if ($this->search) {
            $query = $query->where('full_degree_program_name', 'like', '%' . $this->search . '%');
        }

        $programs = $query->paginate(10);

        return view('livewire.degree-programs.index', compact('programs'));
    }

    public function delete($id)
    {
        $program = DegreeProgram::find($id);
        if (!$program) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Degree program not found.']);
            return;
        }
        $program->delete();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Degree program deleted.']);
    }
}
