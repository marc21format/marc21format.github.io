<?php

namespace App\Http\Livewire\StudentExcuseLetters;

use Livewire\Component;
use App\Models\StudentExcuseLetter;
use App\Models\User;

class Show extends Component
{
    public $user;
    public $letter;

    public function mount($user_id, $letter_id)
    {
        $this->user = User::findOrFail($user_id);
        $this->letter = StudentExcuseLetter::where('user_id', $user_id)->findOrFail($letter_id);
    }

    public function render()
    {
        return view('livewire.student-excuse-letters.show');
    }
}