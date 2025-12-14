<?php
namespace App\Http\Livewire\Profile\Volunteer\CommitteeMembers;

use Livewire\Component;
use App\Models\CommitteeMember;

class Index extends Component
{
    public $userId;

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }

    public function render()
    {
        $members = $this->userId ? CommitteeMember::where('user_id', $this->userId)->get() : CommitteeMember::orderBy('member_id')->get();
        return view('livewire.profile.volunteer.committee_members..index', [
            'members' => $members,
        ]);
    }

    public function delete($id)
    {
        $m = CommitteeMember::find($id);
        if ($m) {
            $m->delete();
            // Emit a client-side notification: prefer Livewire v3 dispatch(), fall back to dispatchBrowserEvent if available, otherwise emit a Livewire event
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('committeeMemberDeleted', ['id' => $id]);
            } elseif (method_exists($this, 'dispatchBrowserEvent')) {
                $this->dispatchBrowserEvent('committeeMemberDeleted', ['id' => $id]);
            } else {
                $this->emit('committeeMemberDeleted', ['id' => $id]);
            }
            // Trigger a refresh in a way that's compatible across Livewire versions.
            // Livewire v3 provides dispatch(), older versions provide emit/emitSelf.
            if (method_exists($this, 'dispatch')) {
                // v3 style
                $this->dispatch('$refresh');
            } elseif (method_exists($this, 'emitSelf')) {
                $this->emitSelf('$refresh');
            } elseif (method_exists($this, 'emit')) {
                $this->emit('$refresh');
            } else {
                // last-resort: nothing we can call reliably; the page will still reload if needed
            }
        }
    }
}
