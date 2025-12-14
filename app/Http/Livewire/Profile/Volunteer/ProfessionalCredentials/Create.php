<?php
namespace App\Http\Livewire\Profile\Volunteer\ProfessionalCredentials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ProfessionalCredential;

class Create extends Component
{
    public $userId;
    public $user;
    public $fieldofwork_id;
    public $prefix_id;
    public $suffix_id;
    public $issued_on;
    public $notes;

    public $fields = [];
    public $prefixes = [];
    public $suffixes = [];

    protected $rules = [
        'fieldofwork_id' => 'nullable|integer|exists:fields_of_work,fieldofwork_id',
        'prefix_id' => 'nullable|integer|exists:prefix_titles,prefix_id',
        'suffix_id' => 'nullable|integer|exists:suffix_titles,suffix_id',
        'issued_on' => 'nullable|string',
        'notes' => 'nullable|string|max:255',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?? Auth::id();
        $this->user = \App\Models\User::find($this->userId);
        $this->fields = \App\Models\FieldOfWork::orderBy('name')->get();
        $this->prefixes = \App\Models\PrefixTitle::orderBy('title')->get();
        $this->suffixes = \App\Models\SuffixTitle::orderBy('title')->get();
    }

    public function store()
    {
        $this->validate();

        $data = [
            'user_id' => $this->userId,
            'fieldofwork_id' => $this->fieldofwork_id ?: null,
            'prefix_id' => $this->prefix_id ?: null,
            'suffix_id' => $this->suffix_id ?: null,
            'issued_on' => $this->issued_on ?: null,
            'notes' => $this->notes ?: null,
        ];

        if (! empty($data['issued_on']) && ! preg_match('/^\d{4}$/', $data['issued_on'])) {
            $ts = strtotime($data['issued_on']);
            if ($ts !== false) {
                $data['issued_on'] = date('Y', $ts);
            } else {
                $data['issued_on'] = null;
            }
        }

        ProfessionalCredential::create($data);

        $this->reset(['fieldofwork_id','prefix_id','suffix_id','issued_on','notes']);

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('professionalCredentialCreated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('professionalCredentialCreated');
        }
    }

    public function render()
    {
        return view('livewire.profile.volunteer.professional-credentials.create', [
            'user' => $this->user,
            'fields' => $this->fields,
            'prefixes' => $this->prefixes,
            'suffixes' => $this->suffixes,
        ]);
    }
}
