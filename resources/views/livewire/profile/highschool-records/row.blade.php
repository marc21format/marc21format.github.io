<div>
    @if($record)
        <div>
            <button wire:click="delete" class="btn btn-danger" onclick="return confirm('Delete this record?')">Delete</button>
        </div>
    @else
        <div class="text-sm text-gray-500">Record not found.</div>
    @endif
</div>
