@if($record)
    <div class="flex items-center space-x-2">
        <button wire:click="delete" class="text-sm text-red-600 hover:underline">Delete</button>
    </div>
@else
    <div class="text-sm text-gray-500">Record not found.</div>
@endif
