<div class="flex items-center space-x-4">
    <div>
        <div class="font-medium">{{ $student->name }}</div>
        <div class="text-sm text-gray-500">{{ $student->email }}</div>
    </div>
    <div class="ml-auto flex items-center space-x-2">
        @livewire('roster.students.edit', ['id' => $student->id], key('edit-'.$student->id))
        <button wire:click="$dispatch('delete', {{ $student->id }})" class="text-red-600">Delete</button>
    </div>
</div>
