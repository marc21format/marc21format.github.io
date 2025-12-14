<div class="p-2">
    <form wire:submit.prevent="update" class="flex items-center space-x-2">
        <input wire:model.defer="name" type="text" class="rounded border p-1" />
        <input wire:model.defer="email" type="email" class="rounded border p-1" />
        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded">Save</button>
    </form>
</div>
