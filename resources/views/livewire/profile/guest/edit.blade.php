<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Guest Profile</h1>

        <div class="bg-white shadow-sm rounded-lg p-6">
            @if (session('status'))
                <div class="mb-4 text-green-700">{{ session('status') }}</div>
            @endif

            <form wire:submit.prevent="save" class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Username / Display Name</label>
                    <input id="name" type="text" wire:model.defer="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    @error('name') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" wire:model.defer="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    @error('email') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password (leave blank to keep current)</label>
                    <input id="password" type="password" wire:model.defer="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                    @error('password') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password_confirmation" type="password" wire:model.defer="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                </div>

                @if(!empty($roles))
                <div>
                    <label for="role_id" class="block text-sm font-medium text-gray-700">Role</label>
                    <select id="role_id" wire:model.defer="role_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">-- select role --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->role_id }}">{{ $role->role_title }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
                @endif
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('profile.guest.show', ['user' => $userId]) }}" class="text-sm text-gray-600">Cancel</a>
                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
