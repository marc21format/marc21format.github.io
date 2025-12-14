<div class="mb-4">
    <form wire:submit.prevent="save" class="flex gap-2">
        <input type="text" wire:model.defer="province_name" class="form-input rounded-lg px-4 py-2" placeholder="New province name">
        <button class="btn btn-primary" type="submit">Add province</button>
    </form>
</div>
