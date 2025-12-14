<?php

namespace App\Http\Livewire\StudentExcuseLetters;

use Livewire\Component;
use App\Models\StudentExcuseLetter;
use App\Models\User;

class Index extends Component
{
    public $user;

    public function mount($user_id)
    {
        $this->user = User::findOrFail($user_id);
    }

    public function render()
    {
        $excuseLetters = StudentExcuseLetter::where('user_id', $this->user->id)->get();
        return view('livewire.student-excuse-letters.index', compact('excuseLetters'));
    }
}